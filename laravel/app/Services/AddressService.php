<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Base\BaseRepositoryInterface;
use App\Contracts\Repositories\AddressRepositoryInterface;
use App\Contracts\Services\AddressServiceInterface;
use App\Services\Base\BaseCrudService;

class AddressService extends BaseCrudService implements AddressServiceInterface
{
    protected BaseRepositoryInterface $repository;

    /**
     * Create a new class instance.
     */
    public function __construct(AddressRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
}
