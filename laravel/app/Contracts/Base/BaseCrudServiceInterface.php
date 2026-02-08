<?php

declare(strict_types=1);

namespace App\Contracts\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface BaseCrudServiceInterface
{
    public function getRepository(): ?BaseRepositoryInterface;

    /**
     * Get multiple rows
     *
     * @param  Collection  $request  The validated request
     * @return Collection The models searched
     */
    public function index(Collection $request): Collection;

    /**
     * Create record
     *
     * @param  Collection  $request  The values to insert
     * @return Model The created model
     */
    public function create(Collection $request): Model;

    /**
     * Check if model exists or create it
     *
     * @param  Collection  $exists  The values to check
     * @param  Collection  $insert  The values to insert
     * @return Model The created model
     */
    public function firstOrCreate(Collection $exists, Collection $insert): Model;

    /**
     * Create multiple records at once
     *
     * @param  Collection  $values  The values to insert
     * @return Collection<Model> The created models
     */
    public function insert(Collection $values): Collection;

    /**
     * Update record
     *
     * @param  Collection  $request  The values to update
     * @param  int  $id  The ID to update
     * @return Model The updated model
     */
    public function update(Collection $request, int $id): Model;

    /**
     * Check if a model exists and update it or create it
     *
     * @param  Collection  $check  The values to check if exists
     * @param  Collection  $request  The values to be inserted or updated
     * @return Model The created/updated model
     */
    public function updateOrCreate(Collection $check, Collection $request): Model;

    /**
     * Find model by ID
     *
     * @param  int  $id  The ID of the record to return
     * @param  Collection  $request  The validated request
     * @return Collection<Model>|Model The models searched
     */
    public function find(int $id, Collection $request): Collection|Model;

    /**
     * Delete a record
     *
     * @param  int  $id  The ID to delete
     */
    public function delete(int $id): bool;

    /**
     * Delete multiple records
     */
    public function deleteMultiple(array $ids): void;

    /**
     * Restore a record
     *
     * @param  int  $id  The ID to restore
     * @return bool True if the record was restored, false otherwise
     */
    public function restore(int $id): bool;
}
