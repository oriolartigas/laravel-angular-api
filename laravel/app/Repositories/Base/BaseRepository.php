<?php

declare(strict_types=1);

namespace App\Repositories\Base;

use App\Contracts\Base\BaseRepositoryInterface;
use App\Exceptions\DeleteForeignKeyException;
use App\Exceptions\ModelCreateException;
use App\Exceptions\ModelDeleteException;
use App\Exceptions\ModelNotModifiedException;
use App\Exceptions\ModelOperationException;
use App\Exceptions\ModelRestoreException;
use App\Exceptions\ModelUpdateException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Throwable;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * The Eloquent model instance used by this repository
     */
    protected Model $model;

    /**
     * Cached model class name for performance optimization
     */
    private ?string $modelClass = null;

    /**
     * Constructor to bind model to repository
     */
    public function __construct(Model $modelInstance)
    {
        $this->model = $modelInstance;
    }

    /**
     * Get the associated model instance
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Get the model class
     */
    public function getModelClass(): string
    {
        if ($this->modelClass === null) {
            $this->modelClass = get_class($this->model);
        }

        return $this->modelClass;
    }

    /**
     * Get the model basename class
     */
    public function getBaseModelClass(): string
    {
        return class_basename($this->getModelClass());
    }

    /**
     * Retrieves a collection of models based on the query options,
     * applying eager loads, filters, and sorting.
     *
     * @param  array  $options  The options (where, with, withCount, sort, limit...)
     * @return Collection<Model> The models searched
     */
    public function index(array $options): Collection
    {
        $with = $options['with'] ?? [];
        $withCount = $options['withCount'] ?? [];
        $where = $options['where'] ?? [];
        $sort = $options['sort'] ?? [];

        $query = $this->model
            ->with($with)
            ->withCount($withCount)
            ->where($where)
            ->sort($sort);

        return $query->get();
    }

    /**
     * Find the record with the given id
     *
     * @param  int  $id  The ID of the model
     */
    public function find(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Find the record with the given id and options
     *
     * @param  int  $id  The ID of the model
     * @param  array  $options  The options (with, withCount)
     */
    public function findWithOptions(int $id, array $options): Model
    {
        $with = $options['with'] ?? [];
        $withCount = $options['withCount'] ?? [];

        // $with and $withCount are pre-validated at service layer via model's withable/withCountable properties
        return $this->model
            ->with($with)
            ->withCount($withCount)
            ->findOrFail($id);
    }

    /**
     * Create one record
     *
     * @param  array  $data  The values to insert
     * @return Model The model created
     *
     * @throws ModelCreateException If model creation fails
     */
    public function create(array $data): Model
    {
        try {
            return $this->model->create(attributes: $data);
        } catch (Throwable $e) {
            $this->throwModelOperationException(
                exceptionClass: ModelCreateException::class,
                previous: $e,
            );
        }
    }

    /**
     * Check if a model exists and update it or create it
     *
     * @param  array  $attributes  The values to check if exists
     * @param  array  $values  The values to be inserted
     * @return Model The model created
     *
     * @throws ModelCreateException If model creation fails
     */
    public function firstOrCreate(array $attributes, array $values): Model
    {
        try {
            return $this->model->firstOrCreate(
                attributes: $attributes,
                values: $values,
            );
        } catch (Throwable $e) {
            $this->throwModelOperationException(
                exceptionClass: ModelCreateException::class,
                previous: $e,
            );
        }
    }

    /**
     * Create multiple records at once
     *
     * Create rows one by one instead of
     * Laravel "insert" function because it
     * doesn't return created rows.
     *
     * @param  array  $data  The values to insert
     * @return Collection<Model> Collection of created models
     *
     * @throws ModelCreateException If model creation fails
     */
    public function insert(array $data): Collection
    {
        return collect(value: $data)
            ->map(callback: function (array $recordData): Model {
                try {
                    $insert = $this->model->create(
                        attributes: $recordData,
                    );

                    return $insert;
                } catch (Throwable $e) {
                    $this->throwModelOperationException(
                        exceptionClass: ModelCreateException::class,
                        previous: $e
                    );
                }
            });
    }

    /**
     * Update one record
     *
     * @param  int  $id  The ID to update
     * @param  array  $data  The values to update
     * @return Model The updated model
     *
     * @throws ModelUpdateException If model update fails
     */
    public function update(int $id, array $data): Model
    {
        try {
            $record = $this->model->findOrFail($id);
            $record->fill(attributes: $data);

            // Throw exception if nothing changed
            if (! $record->isDirty()) {
                throw new ModelNotModifiedException(
                    $this->getBaseModelClass(),
                    $data,
                    $id
                );
            }

            // Save the record to the database
            $record->save();

            return $record;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (ModelNotModifiedException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->throwModelOperationException(
                exceptionClass: ModelUpdateException::class,
                previous: $e,
            );

            throw new \RuntimeException('Unreachable code'); // For static analysis
        }
    }

    /**
     * Check if a model exists and update it or create it
     *
     * @param  array  $attributes  The values to check if exists
     * @param  array  $values  The values to be inserted or updated
     * @return Model The model inserted/updated
     *
     * @throws ModelCreateException If model create fails
     * @throws ModelUpdateException If model update fails
     */
    public function updateOrCreate(array $attributes, array $values): Model
    {
        $isUpdating = false;
        $existingModel = $this->model->where($attributes)->first();

        if ($existingModel) {
            $isUpdating = true;
        }

        try {
            return $this->model->updateOrCreate($attributes, $values);
        } catch (Throwable $e) {
            $this->throwModelOperationException(
                exceptionClass: $isUpdating ? ModelUpdateException::class : ModelCreateException::class,
                previous: $e,
            );
        }
    }

    /**
     * Sync many-to-many relations
     *
     * @throws \BadMethodCallException
     * @throws ModelUpdateException If sync fails
     * @throws ModelNotModifiedException If nothing changed
     */
    public function sync(int $id, string $relationName, array $relatedIds): array
    {
        try {
            $record = $this->model->findOrFail($id);

            // Check if relation exists
            if (! method_exists($record, $relationName)) {
                throw new \BadMethodCallException(
                    "The relation method '{$relationName}' doesn\'t exist on model '{$this->getBaseModelClass()}'.",
                );
            }

            $syncResult = $record->{$relationName}()->sync($relatedIds);

            return $syncResult;

        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->throwModelOperationException(
                exceptionClass: ModelUpdateException::class,
                previous: $e,
            );

            throw new \RuntimeException('Unreachable code'); // For static analysis
        }
    }

    /**
     * Update model but don't save it to database
     *
     * @param  array  $data  The data to be saved to the model
     * @param  int  $id  The ID of the model
     * @return Model The filled model
     */
    public function fill(array $data, int $id): Model
    {
        $record = $this->model->findOrFail($id);
        $record->fill(attributes: $data);

        return $record;
    }

    /**
     * Save record to the database
     *
     * @param  Model  $model  The model to be saved
     *
     * @throws ModelUpdateException If model update fails
     */
    public function save(Model $model): void
    {
        if ($model->isDirty()) {
            try {
                $model->save();
            } catch (Throwable $e) {
                $this->throwModelOperationException(
                    exceptionClass: ModelUpdateException::class,
                    previous: $e,
                );
            }
        }
    }

    /**
     * Remove record from the database
     *
     * @param  int  $id  The ID of the model
     *
     * @throws ModelRestoreException If model restore fails
     */
    public function restore(int $id): bool
    {
        try {
            return $this->model->withTrashed()->findOrFail($id)->restore();
        } catch (Throwable $e) {
            $this->throwModelOperationException(
                exceptionClass: ModelRestoreException::class,
                previous: $e,
            );
        }
    }

    /**
     * Delete record from the database
     *
     * @param  int  $id  The ID of the model
     *
     * @throws ModelDeleteException If model delete fails
     */
    public function delete(int $id): bool
    {
        try {
            $model = $this->model->findOrFail($id);

            if ($model->delete() === false) {
                throw new \RuntimeException(message: 'Eloquent delete operation was cancelled.');
            }

            return true;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            $queryException = $this->findQueryException($e);

            // Check if it's a foreign key constraint exception
            if ($queryException instanceof QueryException && $queryException->errorInfo[1] === 1451) {
                throw new DeleteForeignKeyException(
                    modelName: $this->getBaseModelClass(),
                    previous: $queryException,
                );
            }

            $this->throwModelOperationException(
                exceptionClass: ModelDeleteException::class,
                previous: $e,
            );

            throw new \RuntimeException('Unreachable code'); // For static analysis
        }
    }

    /**
     * Delete multiple records
     *
     * Sanitizes the IDs to prevent SQL injection and deletes the models
     *
     * @param  array<int>  $ids  The IDs of the models to delete
     *
     * @throws ModelDeleteException
     */
    public function deleteMultiple(array $ids): void
    {
        try {
            // Validate and sanitize IDs to prevent SQL injection
            $sanitizedIds = array_filter(array_map('intval', $ids), fn ($id) => $id > 0);

            if (empty($sanitizedIds)) {
                return;
            }

            $this->model->whereIn('id', $sanitizedIds)->delete();
        } catch (Throwable $e) {
            $this->throwModelOperationException(
                exceptionClass: ModelDeleteException::class,
                previous: $e,
            );
        }
    }

    /**
     * Force remove record from the database when softdelete is enabled
     *
     * @throws ModelDeleteException If model delete fails
     */
    public function forceDelete(): void
    {
        try {
            $this->model->forceDelete();
        } catch (Throwable $e) {
            $this->throwModelOperationException(
                exceptionClass: ModelDeleteException::class,
                previous: $e,
            );
        }
    }

    /**
     * Determine the HTTP status code based on the exception
     */
    private function determineHttpStatusCode(Throwable $previous): int
    {
        $queryException = $this->findQueryException($previous);

        if (! $queryException) {
            if ($previous->getCode() !== 0) {
                return $previous->getCode();
            }

            return 500;
        }

        $errorInfo = $queryException->errorInfo ?? [];
        $sqlState = $errorInfo[0] ?? ''; // Ex: '42S22'
        $errorCode = $errorInfo[1] ?? 0;  // Ex: 1054 (MySQL: Unknown column)

        // MySQL Error 1054: Unknown column.
        // 500: Schema error (SQLSTATE 42XXX)
        if ($errorCode === 1054
            || str_starts_with($sqlState, '42')) {
            return 500;
        }

        // MySQL Error 1062: Duplicate entry for key '...'
        // 409: Integrity violation (SQLSTATE 23XXX)
        if ($errorCode === 1062
            || str_starts_with($sqlState, '23')) {
            return 409;
        }

        return 500;
    }

    /**
     * Find the original QueryException in the Throwable chain
     */
    private function findQueryException(Throwable $e): ?Throwable
    {
        $current = $e;
        while ($current !== null) {
            if ($current instanceof QueryException) {
                return $current;
            }
            $current = $current->getPrevious();
        }

        return null;
    }

    /**
     * Throws a model operation exception
     *
     * @param  class-string<ModelOperationException>  $exceptionClass  Exception type
     * @param  Throwable  $e  Previous exception
     *
     * @phpstan-return never
     *
     * @throws ModelOperationException
     */
    private function throwModelOperationException(string $exceptionClass, Throwable $previous): never
    {
        if (! class_exists($exceptionClass)) {
            throw new \RuntimeException("Class '{$exceptionClass}' doesn't exist.");
        }

        if (! is_subclass_of($exceptionClass, ModelOperationException::class)) {
            throw new \RuntimeException("Class '{$exceptionClass}' doesn't implement ModelOperationException.");
        }

        $modelName = $this->getBaseModelClass();
        $code = $this->determineHttpStatusCode(previous: $previous);

        throw new $exceptionClass(
            modelName: $modelName,
            code: $code,
            previous: $previous,
        );
    }
}
