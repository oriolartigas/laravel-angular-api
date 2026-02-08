<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Role;

use App\Repositories\RoleRepository;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\RoleTestConfigurator;
use Tests\Unit\Base\BaseRepositoryDeleteTest;

class RoleRepositoryDeleteTest extends BaseRepositoryDeleteTest
{
    protected function getConfigurator(): BaseTestConfigurator
    {
        return new RoleTestConfigurator;
    }

    protected function getRepositoryClass(): string
    {
        return RoleRepository::class;
    }
}
