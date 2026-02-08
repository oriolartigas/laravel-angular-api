<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\User;

use App\Repositories\UserRepository;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\UserTestConfigurator;
use Tests\Unit\Base\BaseRepositoryUpdateTest;

class UserRepositoryUpdateTest extends BaseRepositoryUpdateTest
{
    protected function getConfigurator(): BaseTestConfigurator
    {
        return new UserTestConfigurator;
    }

    protected function getRepositoryClass(): string
    {
        return UserRepository::class;
    }

    protected function getTestData(): array
    {
        return $this->configurator->getModelClass()::factory()->raw();
    }
}
