<?php

declare(strict_types=1);

namespace Tests\Feature\Address;

use App\Models\User;
use Tests\Feature\Base\Validation\BaseValidationStoreTest;
use Tests\Helpers\AddressTestConfigurator;
use Tests\Helpers\Base\BaseTestConfigurator;

/**
 * @property AddressTestConfigurator $configurator
 */
class AddressValidationStoreTest extends BaseValidationStoreTest
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

    /**
     * Gets the configurator class FQCN to be instantiated statically.
     */
    protected static function getConfiguratorClass(): string
    {
        return AddressTestConfigurator::class;
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
     * @return \App\Models\Address
     */
    protected function getModelToUpdate(): \Illuminate\Database\Eloquent\Model
    {
        $user = User::factory()->create();

        return $this->configurator->getModelClass()::factory()->create([
            'user_id' => $user->id,
        ]);
    }

    /**
     * Defines the invalid data required to fail validation for create/update methods
     *
     * @return array Array of data sets: [caseName => [requestData, expectedFails]]
     */
    protected static function validationDataSets(): array
    {
        return [
            'missing_user_id' => [['city' => 'Test City', 'street' => 'Test Street', 'postal_code' => '12345', 'state' => 'Test State', 'country' => 'Test Country'], ['user_id']],
            'missing_name' => [['user_id' => 1, 'name' => null, 'city' => 'Test City', 'street' => 'Test Street', 'postal_code' => '12345', 'state' => 'Test State', 'country' => 'Test Country'], ['name']],
            'missing_city' => [['user_id' => 1, 'name' => 'Test Name', 'city' => null, 'street' => 'Test Street', 'postal_code' => '12345', 'state' => 'Test State', 'country' => 'Test Country'], ['city']],
            'missing_street' => [['user_id' => 1, 'name' => 'Test Name', 'city' => 'Test City', 'street' => null, 'postal_code' => '12345', 'state' => 'Test State', 'country' => 'Test Country'], ['street']],
            'missing_postal_code' => [['user_id' => 1, 'name' => 'Test Name', 'city' => 'Test City', 'street' => 'Test Street', 'postal_code' => null, 'state' => 'Test State', 'country' => 'Test Country'], ['postal_code']],
            'missing_state' => [['user_id' => 1, 'name' => 'Test Name', 'city' => 'Test City', 'street' => 'Test Street', 'postal_code' => '12345', 'state' => null, 'country' => 'Test Country'], ['state']],
            'missing_country' => [['user_id' => 1, 'name' => 'Test Name', 'city' => 'Test City', 'street' => 'Test Street', 'postal_code' => '12345', 'state' => 'Test State', 'country' => null], ['country']],
            'invalid_user_id' => [['user_id' => 0, 'name' => 'Test Name', 'city' => 'Test City', 'street' => 'Test Street', 'postal_code' => '12345', 'state' => 'Test State', 'country' => 'Test Country'], ['user_id']],
        ];
    }
}
