<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Role;

use App\Repositories\RoleRepository;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\RoleTestConfigurator;
use Tests\Unit\Base\BaseRepositoryUpdateTest;

class RoleRepositoryUpdateTest extends BaseRepositoryUpdateTest
{
    protected function getConfigurator(): BaseTestConfigurator
    {
        return new RoleTestConfigurator;
    }

    protected function getRepositoryClass(): string
    {
        return RoleRepository::class;
    }

    protected function getTestData(): array
    {
        return $this->configurator->getModelClass()::factory()->raw();
    }
}
