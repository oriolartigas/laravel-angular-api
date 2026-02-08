<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use Tests\Feature\Base\Validation\BaseValidationIndexTest;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\UserTestConfigurator;

/**
 * @property UserTestConfigurator $configurator
 */
class UserValidationIndexTest extends BaseValidationIndexTest
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
     * Gets the configurator class FQCN to be instantiated statically.
     */
    protected static function getConfiguratorClass(): string
    {
        return UserTestConfigurator::class;
    }

    /**
     * Returns an instance of the model to update
     *
     * @return \App\Models\User
     */
    protected function getModelToUpdate(): \Illuminate\Database\Eloquent\Model
    {
        return $this->configurator->getModelClass()::factory()->create();
    }

    /**
     * Override the parent's function to include 'password_confirmation'
     */
    protected function getValidBaseData(): array
    {
        $data = parent::getValidBaseData();

        $data['password_confirmation'] = $data['password'] ?? 'secret12345';

        return $data;
    }
}
