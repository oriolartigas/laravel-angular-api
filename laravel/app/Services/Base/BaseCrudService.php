<?php

declare(strict_types=1);

namespace App\Services\Base;

use App\Contracts\Base\BaseCrudServiceInterface;
use App\Contracts\Base\BaseRepositoryInterface;
use App\Exceptions\ModelNotModifiedException;
use App\Repositories\Base\BaseRepository;
use App\Traits\BaseModelTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @property BaseRepository $repository
 */
class BaseCrudService extends BaseService implements BaseCrudServiceInterface
{
    /**
     * BaseRepository class
     */
    protected BaseRepositoryInterface $repository;

    /**
     * Cached model instance
     */
    private ?Model $cachedModel = null;

    public function __construct(BaseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get the repository class
     */
    public function getRepository(): BaseRepositoryInterface
    {
        return $this->repository;
    }

    /**
     * Get multiple models applying filters, eager loads, and sorting
     *
     * @param  Collection  $request  The validated request
     * @return Collection The models searched
     */
    public function index(Collection $request): Collection
    {
        $options = $this->parseRequestOptions(request: $request, keys: ['where', 'with', 'withCount', 'sort']);
        $cleanedOptions = $this->cleanOptions(options: $options);

        return $this->repository->index(options: $cleanedOptions);
    }

    /**
     * Create a new model and sync relations
     *
     * @param  Collection  $request  The validated request
     * @return Model The created model
     */
    public function create(Collection $request): Model
    {
        return DB::transaction(callback: function () use ($request): Model {

            $model = $this->repository->create(
                data: $this->extractFillableData(request: $request)
            );

            $this->processModelRelations(model: $model, request: $request, skipCreate: false);

            return $model;
        });
    }

    /**
     * Check if model exists or create it and sync relations
     *
     * @param  Collection  $exists  The values to check if exists
     * @param  Collection  $request  The validated request
     */
    public function firstOrCreate(Collection $exists, Collection $request): Model
    {
        return DB::transaction(callback: function () use ($exists, $request): Model {

            $model = $this->repository->firstOrCreate(
                attributes: $this->extractFillableData(request: $exists),
                values: $this->extractFillableData(request: $request),
            );

            $this->processModelRelations(model: $model, request: $request, skipCreate: true);

            return $model;
        });
    }

    /**
     * Create multiple models at once
     *
     * @param  Collection  $values  The values to insert
     * @return Collection<Model> The created models
     */
    public function insert(Collection $values): Collection
    {
        return DB::transaction(callback: function () use ($values): Collection {

            $data = $values->map(callback: function ($r): array {
                return $this->extractFillableData(request: collect(value: $r));
            })->toArray();

            return $this->repository->insert($data);
        });
    }

    /**
     * Update model and sync relations.
     * Capture if the update method throws a ModelNotModifiedException.
     * If the model and any relation changed then throw the exception.
     *
     * @param  Collection  $request  The validated request
     * @param  int  $id  The ID to update
     * @return Model The updated model
     */
    public function update(Collection $request, int $id): Model
    {
        return DB::transaction(callback: function () use ($request, $id): Model {
            $modelModified = false;
            $notModifiedException = null;
            $model = null;

            try {
                $model = $this->repository->update(
                    data: $this->extractFillableData(request: $request),
                    id: $id
                );
            } catch (ModelNotModifiedException $e) {
                $model = $this->repository->find($id);
                $notModifiedException = $e;
                $modelModified = false;
            } catch (Exception $e) {
                $modelModified = false;
                throw $e;
            }

            $relationModified = $this->processModelRelations(model: $model, request: $request, skipCreate: false);

            if (! $modelModified && ! $relationModified) {
                if ($notModifiedException) {
                    throw $notModifiedException;
                }
            }

            return $model;
        });
    }

    /**
     * Check if a model exists and update it or create it
     * and sync relations
     *
     * @param  Collection  $check  The values to check if exists
     * @param  Collection  $request  The validated request
     * @return Model The created/updated model
     */
    public function updateOrCreate(Collection $check, Collection $request): Model
    {
        return DB::transaction(callback: function () use ($check, $request): Model {

            $model = $this->repository->updateOrCreate(
                attributes: $this->extractFillableData(request: $check),
                values: $this->extractFillableData(request: $request),
            );

            $this->processModelRelations(model: $model, request: $request, skipCreate: false);

            return $model;
        });
    }

    /**
     * Find model by ID
     *
     * @param  int  $id  The ID of the record to return
     * @param  Collection  $request  The validated request
     * @return Collection|Model The model searched
     */
    public function find(int $id, Collection $request): Collection|Model
    {
        $options = $this->parseRequestOptions(request: $request, keys: ['with', 'withCount']);
        $cleanedOptions = $this->cleanOptions(options: $options);

        return $this->repository->findWithOptions(id: $id, options: $cleanedOptions);
    }

    /**
     * Restore a record
     *
     * @param  int  $id  The ID to restore
     */
    public function restore(int $id): bool
    {
        return $this->repository->restore(id: $id);
    }

    /**
     * Delete a record
     *
     * @param  int  $id  The ID to delete
     */
    public function delete(int $id): bool
    {
        return (bool) $this->repository->delete(id: $id);
    }

    /**
     * Delete multiple records
     */
    public function deleteMultiple(array $ids): void
    {
        DB::transaction(callback: function () use ($ids): void {
            $this->repository->deleteMultiple(ids: $ids);
        });
    }

    /**
     * Inspects the model for syncable relations and executes the sync operation.
     * Returns true if at least one relation was modified.
     *
     * @param  Model  $model  The model instance (newly or updated)
     * @param  Collection  $request  The validated request
     */
    protected function syncModelRelations(Model $model, Collection $request): bool
    {
        $atLeastOneRelationModified = false;
        $syncableRelations = $model->getSyncableRelations();

        foreach ($syncableRelations as $inputKey => $relationMethod) {

            // We only process the relation if the key is PRESENT in the request.
            // This allows for partial updates where relations are not changed.
            if ($request->has(key: $inputKey)) {

                // The sync payload is always an array (empty array means detach all)
                $relatedIds = (array) $request->get(key: $inputKey);

                $syncResult = $this->repository->sync(
                    id: $model->id,
                    relationName: $relationMethod,
                    relatedIds: $relatedIds
                );

                $modifiedKeys = array_filter($syncResult, fn ($changes) => ! empty($changes));

                if (! empty($modifiedKeys)) {
                    $atLeastOneRelationModified = true;
                }
            }
        }

        return $atLeastOneRelationModified;
    }

    /**
     * Creates One-to-Many related models using createMany() based on input data.
     * Returns true if at least one relation was created.
     *
     * @param  Model  $model  The newly created model instance.
     * @param  Collection  $request  The full request data.
     */
    protected function createOneToManyRelations(Model $model, Collection $request): bool
    {
        $atLeastOneCreated = false;
        $creatableRelations = $model->getCreatableRelations() ?? [];

        foreach ($creatableRelations as $inputKey => $relationMethod) {

            if ($request->has(key: $inputKey) && is_array(value: $request->get(key: $inputKey))) {
                $relatedData = $request->get($inputKey);

                if (! empty($relatedData)) {
                    try {
                        $model->{$relationMethod}()->createMany($relatedData);
                        $atLeastOneCreated = true;
                    } catch (Exception $e) {
                        throw new \RuntimeException("QueryException during createMany for {$relationMethod}: ".$e->getMessage());
                    }
                }
            }
        }

        return $atLeastOneCreated;
    }

    /**
     * Loads eager relations requested by the user, plus any relations that were just synced.
     *
     * @param  Model  $model  The model instance (created or updated).
     * @param  Collection  $request  The full request data, including query parameters ('with', 'withSum', etc.).
     */
    protected function loadRequestedRelations(Model $model, Collection $request): void
    {
        $options = $this->parseRequestOptions(request: $request, keys: ['with', 'withCount']);
        $syncable = array_values(array: $model->getSyncableRelations() ?? []);

        $relationsToLoad = collect(value: $syncable)
            ->merge(items: $model->extractWithable($options['with']))
            ->unique()
            ->toArray();

        $relationsToCount = $model->extractWithCountable($options['withCount']);

        if (! empty($relationsToLoad)) {
            $model->load(relations: $relationsToLoad);
        }

        if (! empty($relationsToCount)) {
            $model->loadCount(relations: $relationsToCount);
        }
    }

    /**
     * Parse comma-separated string into filtered array
     *
     * @param  mixed  $value  The value to parse
     * @return array The filtered array
     */
    private function parseCommaSeparatedString(mixed $value): array
    {
        return array_filter(array: explode(separator: ',', string: (string) $value));
    }

    /**
     * Extract fillable data from request
     *
     * @param  Collection  $request  The request data
     */
    private function extractFillableData(Collection $request): array
    {
        return $request->only(keys: $this->repository->getModel()->getFillable())->toArray();
    }

    /**
     * Get cached model instance
     */
    private function getModel(): Model
    {
        return $this->cachedModel ??= $this->repository->getModel();
    }

    /**
     * Parse request options for given keys
     *
     * Extracts specified query parameters from request and processes them based on type.
     * 'where' parameters are returned as-is, other parameters are parsed as comma-separated strings.
     *
     * Request: ?where[status]=active&with=roles,addresses&sort=name
     * Result: ['where' => ['status' => 'active'], 'with' => ['roles', 'addresses'], 'sort' => 'name']
     *
     * @param  Collection  $request  The validated request data
     * @param  array  $keys  The parameter keys to extract (['where', 'with', 'sort', etc.])
     * @return array Associative array of processed options
     */
    private function parseRequestOptions(Collection $request, array $keys): array
    {
        $options = [];

        foreach ($keys as $key) {
            $options[$key] = match ($key) {
                'where' => $request->get(key: $key, default: []),
                default => $this->parseCommaSeparatedString(value: $request->get(key: $key))
            };
        }

        return $options;
    }

    /**
     * Clean options using model extractors
     *
     * Validates and filters raw options through model-specific extractor methods.
     * Each option type uses its corresponding whitelist validation method.
     *
     * @param  array  $options  The raw parsed options from request
     * @return array Validated options containing only allowed values
     */
    private function cleanOptions(array $options): array
    {
        $model = $this->getModel();
        $cleaned = [];

        foreach ($options as $key => $value) {
            $cleaned[$key] = match ($key) {
                'where' => $model->extractWhereableFilters($value),
                'with' => $model->extractWithable($value),
                'withCount' => $model->extractWithCountable($value),
                'sort' => $model->extractSortable($value),
                default => $value
            };
        }

        return $cleaned;
    }

    /**
     * Process all model relations (sync, create, load)
     * and return true if at least one relation was modified.
     *
     * @param  Model  $model  The model instance
     * @param  Collection  $request  The validated request
     * @param  bool  $skipCreate  Skip one-to-many creation
     */
    private function processModelRelations(Model $model, Collection $request, bool $skipCreate = false): bool
    {
        if (! $this->hasBaseModelTrait(model: $model)) {
            return false;
        }

        $syncModified = $this->syncModelRelations(model: $model, request: $request);
        $creationModified = false;

        if (! $skipCreate) {
            $creationModified = $this->createOneToManyRelations(model: $model, request: $request);
        }

        $this->loadRequestedRelations(model: $model, request: $request);

        return $syncModified || $creationModified;
    }

    /**
     * Check if model uses BaseModelTrait
     *
     * @param  Model  $model  The model to check
     */
    private function hasBaseModelTrait(Model $model): bool
    {
        return in_array(needle: BaseModelTrait::class, haystack: class_uses($model), strict: true);
    }
}
