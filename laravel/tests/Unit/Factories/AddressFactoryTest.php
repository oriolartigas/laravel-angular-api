<?php

declare(strict_types=1);

namespace Tests\Unit\Factories;

use App\Models\User;
use Tests\Helpers\AddressTestConfigurator;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Unit\Base\BaseFactoryTest;

class AddressFactoryTest extends BaseFactoryTest
{
    /**
     * Get the configurator class
     *
     * @return AddressTestConfigurator
     */
    protected function getConfigurator(): BaseTestConfigurator
    {
        return new AddressTestConfigurator;
    }

    protected function getAllowedFieldsButNotFillable(): array
    {
        return [];
    }

    /**
     * Test that the factory definition creates a valid user
     */
    public function test_factory_creates_address_with_user(): void
    {
        $address = $this->configurator->getModelInstance()::factory()->create();
        $tableName = $this->configurator->getModelInstance()->getTable();

        $this->assertDatabaseHas(table: $tableName, data: ['user_id' => $address->user_id]);
        $this->assertInstanceOf(expected: User::class, actual: $address->user);
    }

    /**
     * Test that the factory correctly use the ->for() method,
     * overriding its internal definition and successfully setting the
     * correct foreign key (user_id) to the ID of the provided User instance.
     */
    public function test_factory_creates_address_with_user_using_for(): void
    {
        $user = User::factory()->create();

        $this->configurator->getModelInstance()::factory()->for($user, 'user')->create();

        $this->assertDatabaseHas(table: 'addresses', data: [
            'user_id' => $user->id,
        ]);
    }
}
