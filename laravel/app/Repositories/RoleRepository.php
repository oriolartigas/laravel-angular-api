<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Models\Role;
use App\Repositories\Base\BaseRepository;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(Role $model)
    {
        parent::__construct(modelInstance: $model);
    }
}
