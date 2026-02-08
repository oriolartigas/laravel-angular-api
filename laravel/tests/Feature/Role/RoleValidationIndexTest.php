<?php

declare(strict_types=1);

namespace Tests\Feature\Role;

use Tests\Feature\Base\Validation\BaseValidationIndexTest;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\RoleTestConfigurator;

/**
 * @property RoleTestConfigurator $configurator
 */
class RoleValidationIndexTest extends BaseValidationIndexTest
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
     * Returns an instance of the model to update
     *
     * @return \App\Models\Role
     */
    protected function getModelToUpdate(): \Illuminate\Database\Eloquent\Model
    {
        return $this->configurator->getModelClass()::factory()->create();
    }
}
