<?php

declare(strict_types=1);

namespace Tests\Helpers\Base;

use Illuminate\Database\Eloquent\Model;

abstract class BaseTestConfigurator
{
    /**
     * Get the model class name
     *
     * @var string
     */
    abstract public function getModelClass(): string;

    /**
     * Get the model instance
     *
     * @var Model
     */
    abstract public function getModelInstance(): Model;

    /**
     * Get belongsTo relations
     */
    abstract public function getBelongsToRelations(): array;

    /**
     * Get hasMany relations
     */
    abstract public function getHasManyRelations(): array;

    /**
     * Get belongsToMany relations
     */
    abstract public function getBelongsToManyRelations(): array;

    /**
     * Get the base API endpoint URL
     */
    abstract public function getEndpoint(): string;

    /**
     * Returns an array of 'where' parameters for index method.
     * If there are mandatory fields, the first one will be used.
     * If no mandatory fields are found, the first optional field will be used.
     */
    public function getIndexWhereParams(Model $model): array
    {
        $whereMandatoryFields = collect($model->getMandatoryWhereable());
        $whereFields = collect($model->getWhereable());

        // Prepare the request parameters
        $whereClause = [];

        // If there are mandatory fields, we must add a valid filter
        if ($whereMandatoryFields->isNotEmpty()) {

            // Use the value of the model for the mandatory field name/value
            foreach ($whereMandatoryFields as $field) {
                $whereClause[$field] = $model->{$field};
            }
        }
        // If there are optional fields, we must add at least one valid filter
        elseif ($whereFields->isNotEmpty()) {
            $whereClause[$whereFields->first()] = $model->{$whereFields->first()};
        }

        return $whereClause;
    }

    /**
     * Returns a array of 'with' relations for index method
     */
    public function getIndexWithParams(): string
    {
        // Get the names of the belongsTo relations
        $belongsToNames = $this->getRelationNames();

        // Get the names of the belongsToMany relations
        $pivotNames = $this->getPivotRelationNames();

        // Merge the names of the belongsTo and belongsToMany relations
        $allRelations = array_filter(array_merge($belongsToNames, $pivotNames));

        return implode(',', $allRelations);
    }

    /**
     * Returns a array of 'withCount' relations for index method
     */
    public function getIndexWithCountParams(): string
    {
        $allRelations = $this->getPivotRelationNames();

        return implode(',', $allRelations);
    }

    /**
     * Returns a array of 'sort' fields for index method
     */
    public function getIndexSortParams(): string
    {
        // Get the model instance
        $model = $this->getModelInstance();

        // Get the sort fields
        $sortFields = collect($model->getSortable());
        $sortParams = '';

        if ($sortFields->isNotEmpty()) {
            $sortParams = '+'.$sortFields->first();
        }

        return $sortParams;
    }

    public function getWhereable(): array
    {
        // Get the model class
        $model = $this->getModelInstance();

        return $model->getWhereable();
    }

    public function getWithable(): array
    {
        // Get the model class
        $model = $this->getModelInstance();

        return $model->getWithable();
    }

    public function getWithCountable(): array
    {
        // Get the model class
        $model = $this->getModelInstance();

        return $model->getWithCountable();
    }

    public function getSortable(): array
    {
        // Get the model class
        $model = $this->getModelInstance();

        return $model->getSortable();
    }

    /**
     * Converts the array of relations into a comma-separated string
     *
     * @return array La llista de noms de relació (e.g., ['user', 'category']).
     */
    protected function getRelationNames(): array
    {
        return collect($this->getBelongsToRelations())
            ->map(fn ($modelClass) => strtolower(class_basename($modelClass)))
            ->all();
    }

    /**
     * Converts the array of pivot relations into a comma-separated string
     *
     * @return array La llista de noms de relació (e.g., ['roles', 'users']).
     */
    private function getPivotRelationNames(): array
    {
        return collect($this->getBelongsToManyRelations())
            ->map(function ($modelClass) {
                // Extract the base class name
                $singularName = strtolower(class_basename($modelClass));

                // Pluralize the base class name
                return $singularName.'s';
            })
            ->all();
    }
}
