<?php

declare(strict_types=1);

namespace Tests\Unit\Factories;

use App\Models\Address;
use App\Models\Role;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\UserTestConfigurator;
use Tests\Unit\Base\BaseFactoryTest;

class UserFactoryTest extends BaseFactoryTest
{
    /**
     * Get the configurator class
     *
     * @return UserTestConfigurator
     */
    protected function getConfigurator(): BaseTestConfigurator
    {
        return new UserTestConfigurator;
    }

    /**
     * Get the allowed fields but not fillable
     */
    protected function getAllowedFieldsButNotFillable(): array
    {
        return [
            'password',
            'email_verified_at',
            'remember_token',
        ];
    }

    /**
     * Test that the factory definition creates a valid user
     * with two addresses
     *
     * @return void
     */
    public function test_factory_creates_user_with_two_addresses()
    {
        $modelInstance = $this->configurator->getModelInstance();

        $user = $modelInstance::factory()
            ->has(Address::factory()->count(count: 2), 'addresses')
            ->create();

        $this->assertDatabaseCount(table: 'addresses', count: 2);

        $this->assertDatabaseHas(table: 'addresses', data: [
            'user_id' => $user->id,
        ]);

        $this->assertCount(expectedCount: 2, haystack: $user->addresses);
        $this->assertInstanceOf(expected: Address::class, actual: $user->addresses->first());
    }

    /**
     * Test that the factory definition creates a valid user
     * with two addresses
     *
     * @return void
     */
    public function test_factory_creates_user_with_two_roles()
    {
        $modelInstance = $this->configurator->getModelInstance();

        $user = $modelInstance::factory()
            ->has(Role::factory()->count(count: 2), 'roles')
            ->create();

        $this->assertDatabaseCount(table: 'roles', count: 2);
        $this->assertCount(expectedCount: 2, haystack: $user->roles);

        foreach ($user->roles as $role) {
            $this->assertDatabaseHas(table: 'role_user', data: [
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]);
        }
    }
}
