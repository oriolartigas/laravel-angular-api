<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Address;

use App\Repositories\AddressRepository;
use Tests\Helpers\AddressTestConfigurator;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Unit\Base\BaseRepositoryUpdateTest;

class AddressRepositoryUpdateTest extends BaseRepositoryUpdateTest
{
    protected function getConfigurator(): BaseTestConfigurator
    {
        return new AddressTestConfigurator;
    }

    protected function getRepositoryClass(): string
    {
        return AddressRepository::class;
    }

    protected function getTestData(): array
    {
        return [
            'user_id' => 999,
            'street' => 'Carrer Fals 1',
            'city' => 'Barcelona',
            'postal_code' => '08001',
        ];
    }
}
