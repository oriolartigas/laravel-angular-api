<?php

declare(strict_types=1);

namespace Tests\Feature\Address;

use Tests\Feature\Base\Validation\BaseValidationIndexTest;
use Tests\Helpers\AddressTestConfigurator;
use Tests\Helpers\Base\BaseTestConfigurator;

/**
 * @property AddressTestConfigurator $configurator
 */
class AddressValidationIndexTest extends BaseValidationIndexTest
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
     * Returns an instance of the model to update
     *
     * @return \App\Models\Address
     */
    protected function getModelToUpdate(): \Illuminate\Database\Eloquent\Model
    {
        $user = \App\Models\User::factory()->create();

        return $this->configurator->getModelClass()::factory()->create([
            'user_id' => $user->id,
        ]);
    }
}
