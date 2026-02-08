<?php

declare(strict_types=1);

namespace Tests\Feature\Role;

use Tests\Feature\Base\Crud\BaseCrudUpdateTest;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\RoleTestConfigurator;

/**
 * @property RoleTestConfigurator $configurator
 */
class RoleCrudUpdateTest extends BaseCrudUpdateTest
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
}
