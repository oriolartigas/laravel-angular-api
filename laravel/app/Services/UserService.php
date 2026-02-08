<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Base\BaseRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Repositories\UserRepository;
use App\Services\Base\BaseCrudService;

/**
 * @property UserRepository $repository
 */
class UserService extends BaseCrudService implements UserServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
}
