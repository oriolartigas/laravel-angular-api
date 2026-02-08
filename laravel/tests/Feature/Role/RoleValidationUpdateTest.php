<?php

declare(strict_types=1);

namespace Tests\Feature\Role;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Tests\Feature\Base\Validation\BaseValidationUpdateTest;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\RoleTestConfigurator;

/**
 * @property RoleTestConfigurator $configurator
 */
class RoleValidationUpdateTest extends BaseValidationUpdateTest
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
     * Defines the invalid data required to fail validation for update method
     */
    public static function updateValidationDataSets(): array
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
     * Test that updating a role with duplicate name fails validation
     */
    public function test_update_fails_with_duplicate_name(): void
    {
        $existingRole = Role::factory()->create(['name' => 'Existing Role']);
        $roleToUpdate = Role::factory()->create(['name' => 'Role To Update']);

        $requestData = ['name' => $existingRole->name];
        $this->runValidationAssertion('PUT', $requestData, ['name'], $roleToUpdate->id);
    }

    /**
     * Test that updating a role with same name succeeds
     */
    public function test_update_succeeds_with_same_name(): void
    {
        $roleToUpdate = Role::factory()->create(['name' => 'Same Role', 'description' => 'Original description']);

        $requestData = ['name' => $roleToUpdate->name, 'description' => 'Updated description'];
        $url = $this->configurator->getEndpoint().'/'.$roleToUpdate->id;
        $response = $this->putJson($url, $requestData);
        $response->assertStatus(200);
    }
}
