<?php

declare(strict_types=1);

namespace Tests\Feature\Role;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Tests\Feature\Base\Validation\BaseValidationStoreTest;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\RoleTestConfigurator;

/**
 * @property RoleTestConfigurator $configurator
 */
class RoleValidationStoreTest extends BaseValidationStoreTest
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
     * Gets the configurator class FQCN to be instantiated statically.
     */
    protected static function getConfiguratorClass(): string
    {
        return RoleTestConfigurator::class;
    }

    /**
     * Defines the invalid data required to fail validation for store method
     */
    public static function storeValidationDataSets(): array
    {
        return static::validationDataSets();
    }

    /**
     * Returns an instance of the model to update
     *
     * @return Role
     */
    protected function getModelToUpdate(): Model
    {
        return $this->configurator->getModelClass()::factory()->create();
    }

    /**
     * Defines the invalid data sets required to fail validation.
     *
     * @return array Array of data sets: [caseName => [requestData, expectedFails]]
     */
    protected static function validationDataSets(): array
    {
        return [
            'missing_name' => [['name' => null], ['name']],
        ];
    }

    /**
     * Test that creating a role with duplicate name fails validation
     */
    public function test_store_fails_with_duplicate_name(): void
    {
        $existingRole = Role::factory()->create(['name' => 'Existing Role']);

        $requestData = [
            'name' => $existingRole->name,
            'description' => 'New role description',
        ];

        $this->runValidationAssertion('POST', $requestData, ['name']);
    }
}
