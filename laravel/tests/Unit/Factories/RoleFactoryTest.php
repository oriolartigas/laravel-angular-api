<?php

declare(strict_types=1);

namespace Tests\Unit\Factories;

use App\Models\User;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\RoleTestConfigurator;
use Tests\Unit\Base\BaseFactoryTest;

class RoleFactoryTest extends BaseFactoryTest
{
    /**
     * Get the configurator class
     *
     * @return RoleTestConfigurator
     */
    protected function getConfigurator(): BaseTestConfigurator
    {
        return new RoleTestConfigurator;
    }

    /**
     * Get the allowed fields but not fillable
     */
    protected function getAllowedFieldsButNotFillable(): array
    {
        return [];
    }

    /**
     * Test that the factory definition creates a valid user
     * with two addresses
     *
     * @return void
     */
    public function test_factory_creates_role_with_two_users()
    {
        $modelInstance = $this->configurator->getModelInstance();

        $role = $modelInstance::factory()
            ->has(User::factory()->count(count: 2), 'users')
            ->create();

        $this->assertDatabaseCount(table: 'users', count: 2);
        $this->assertCount(expectedCount: 2, haystack: $role->users);

        foreach ($role->users as $user) {
            $this->assertDatabaseHas(table: 'role_user', data: [
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]);
        }
    }
}
