<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Base\BaseRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Services\RoleServiceInterface;
use App\Repositories\RoleRepository;
use App\Services\Base\BaseCrudService;

/**
 * @property RoleRepository $repository
 */
class RoleService extends BaseCrudService implements RoleServiceInterface
{
    protected BaseRepositoryInterface $repository;

    /**
     * Create a new class instance.
     */
    public function __construct(RoleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
}
