<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Role;

use App\Repositories\RoleRepository;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\RoleTestConfigurator;
use Tests\Unit\Base\BaseRepositoryIndexTest;

class RoleRepositoryIndexTest extends BaseRepositoryIndexTest
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
