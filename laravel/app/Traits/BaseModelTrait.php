<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BaseModelTrait
{
    /**
     * Return the One-to-Many relations that can be created
     *
     * @return array An array of relations
     */
    public function getCreatableRelations(): array
    {
        return $this->creatableRelations ?? [];
    }

    /**
     * Return the columns that can be sorted
     *
     * @return array An array of sortable fields
     */
    public function getSortable(): array
    {
        return $this->sortable ?? [];
    }

    /**
     * Return the relations that can be eager loaded
     *
     * @return array An array of relations
     */
    public function getWithable(): array
    {
        return $this->withable ?? [];
    }

    /**
     * Return the relations that are countable
     *
     * @return array An array of relations that are countable
     */
    public function getWithCountable(): array
    {
        return $this->withCountable ?? [];
    }

    /**
     * Return the columns that can be filtered
     *
     * @return array An array of whereable fields
     */
    public function getWhereable(): array
    {
        return $this->whereable ?? [];
    }

    /**
     * Return the columns that are mandatory for filtering
     *
     * @return array An array of mandatory fields
     */
    public function getMandatoryWhereable(): array
    {
        return $this->mandatoryWhereable ?? [];
    }

    /**
     * Get the relations configured to be synced (Many-to-Many).
     */
    public function getSyncableRelations(): array
    {
        return $this->syncableRelations ?? [];
    }

    /**
     * Filters the input array of column filters to return only those fields
     * that are explicitly allowed for querying (whereable or mandatory whereable).
     *
     * @param  array  $where  The array of columns with values
     * @return array An array of columns that can be filtered
     */
    public function extractWhereableFilters(array $where): array
    {
        $whereable = array_unique(array: array_merge($this->getWhereable(), $this->getMandatoryWhereable()));

        // Use helper to filter by KEY (where[column]=value)
        return $this->filterAllowed($where, $whereable, true);
    }

    /**
     * Filters the array of requested relations to return only those that
     * are explicitly allowed for eager loading (withable).
     *
     * @param  array  $with  The array of relations to eager load
     * @return array An array of relations that can be eager loaded
     */
    public function extractWithable(array $with): array
    {
        $withable = $this->getWithable();

        // Use helper to filter by VALUE
        return $this->filterAllowed($with, $withable, false);
    }

    /**
     * Filters the array of requested relations to return only those that
     * are explicitly allowed to be counted (withCountable).
     *
     * @param  array  $withCount  The array of relations to count
     * @return array An array of relations that can be counted
     */
    public function extractWithCountable(array $withCount): array
    {
        $countable = $this->getWithCountable();

        // Use helper to filter by VALUE
        return $this->filterAllowed($withCount, $countable, false);
    }

    /**
     * Filters the array of requested sort fields to return only those that
     * are explicitly allowed in the sortable list, while keeping the direction sign (+/-).
     *
     * @param  array  $sort  The array of sort fields (e.g., ['-name', 'email'])
     * @return array An array of allowed sort fields
     */
    public function extractSortable(array $sort): array
    {
        $sortable = $this->getSortable();

        $filteredSort = collect(value: $sort)
            ->filter(callback: function (string $field) use ($sortable): bool {
                if (empty($field)) {
                    return false;
                }

                // Extract the clean column name (without the sign)
                $sign = $field[0];
                $column = $field;

                if ($sign === '-' || $sign === '+') {
                    $column = substr(string: $field, offset: 1);
                }

                return in_array(needle: $column, haystack: $sortable, strict: true);
            })
            ->toArray();

        return $filteredSort;
    }

    /**
     * Scope to apply sorting to the query builder based on provided fields.
     *
     * @param  array  $fields  Array of sort fields (e.g., ['-name', '+id', 'email']).
     */
    public function scopeSort(Builder $query, $fields): Builder
    {
        $defaultDirection = $this->sort_direction ?? 'asc';
        $defaultSortField = null;

        if (! empty($this->sortable)) {
            $defaultSortField = $this->sortable[0];
        } elseif (! empty($this->primaryKey)) {
            $defaultSortField = $this->primaryKey;
        }

        $fieldsCollection = collect(value: $fields)->filter();

        if ($fieldsCollection->isEmpty()) {
            if ($defaultSortField) {
                $key = $this->getQualifiedColumnName($defaultSortField);

                return $query->orderBy(column: $key, direction: $defaultDirection);
            }

            return $query;
        }

        foreach ($fieldsCollection as $field) {

            $direction = 'asc';
            $column = (string) $field;

            $sign = ! empty($field) ? $field[0] : null;

            if ($sign === '-') {
                $direction = 'desc';
                $column = substr(string: $column, offset: 1);
            } elseif ($sign === '+') {
                $column = substr(string: $column, offset: 1);
            }

            // Final fail-safe check: only add order if the clean column name is whitelisted
            if (in_array(needle: $column, haystack: $this->sortable, strict: true)) {
                $key = $this->getQualifiedColumnName($column);
                $query->orderBy(column: $key, direction: $direction);
            }
        }

        return $query;
    }

    /**
     * Generic helper to filter a request array against an allowed list (whitelist).
     *
     * @param  array  $requested  The array of keys/values requested by the user.
     * @param  array  $allowed  The whitelist of allowed keys/values.
     * @param  bool  $byKey  If true, filters by array key (for 'where'). If false, filters by value (for 'with'/'withCount').
     * @return array The filtered array.
     */
    protected function filterAllowed(array $requested, array $allowed, bool $byKey = false): array
    {
        if ($byKey) {
            // Used for associative arrays (like 'where'), filtering by key
            return array_intersect_key($requested, array_flip($allowed));
        }

        // Used for flat arrays (like 'with' or 'withCount'), filtering by value
        return array_intersect($requested, $allowed);
    }

    /**
     * Returns the fully qualified name for a database field, by appending
     * the table name to the column name. If the column is found on the
     * model 'aggregates' array, the table name is not appended and the
     * column name is returned as is.
     *
     * @param  string  $column  - The column name
     * @return string - Qualified column name (e.g., 'table.column' or just 'column')
     */
    protected function getQualifiedColumnName(string $column): string
    {
        // Check if the 'aggregates' property exists on the current model instance
        if (property_exists($this, 'aggregates')) {
            // If the column is an aggregate (e.g., a count field), return it as is
            if (in_array(needle: $column, haystack: $this->aggregates, strict: true)) {
                return $column;
            }
        }

        // Otherwise, qualify the column name with the table name
        return $this->getTable().'.'.$column;
    }
}
