<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Address;

use App\Repositories\AddressRepository;
use Tests\Helpers\AddressTestConfigurator;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Unit\Base\BaseRepositoryDeleteTest;

class AddressRepositoryDeleteTest extends BaseRepositoryDeleteTest
{
    protected function getConfigurator(): BaseTestConfigurator
    {
        return new AddressTestConfigurator;
    }

    protected function getRepositoryClass(): string
    {
        return AddressRepository::class;
    }
}
