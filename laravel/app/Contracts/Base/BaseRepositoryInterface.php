<?php

declare(strict_types=1);

namespace App\Contracts\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface BaseRepositoryInterface
{
    /**
     * Get the model of the repository
     */
    public function getModel(): Model;

    /**
     * Get the class of the model
     */
    public function getModelClass(): string;

    /**
     * Get the base class of the model
     */
    public function getBaseModelClass(): string;

    /**
     * Get one record
     *
     * @param  int  $id  The ID of the model
     * @return Collection<Model>|Model
     */
    public function find(int $id): Collection|Model;

    /**
     * Create new record
     */
    public function create(array $data): Model;

    public function firstOrCreate(array $attributes, array $values): Model;

    /**
     * @return Collection<Model>
     */
    public function insert(array $data): Collection;

    /**
     * Update record
     */
    public function update(int $id, array $data): Model;

    public function updateOrCreate(array $attributes, array $values): Model;

    /**
     * @param  array<int>  $relatedIds
     */
    public function sync(int $id, string $relationName, array $relatedIds): array;

    public function fill(array $data, int $id): Model;

    public function save(Model $data): void;

    public function restore(int $id): bool;

    public function delete(int $id): bool;

    public function deleteMultiple(array $ids): void;

    public function forceDelete(): void;
}
