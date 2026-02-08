<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\User;

use App\Repositories\UserRepository;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\UserTestConfigurator;
use Tests\Unit\Base\BaseRepositoryDeleteTest;

class UserRepositoryDeleteTest extends BaseRepositoryDeleteTest
{
    protected function getConfigurator(): BaseTestConfigurator
    {
        return new UserTestConfigurator;
    }

    protected function getRepositoryClass(): string
    {
        return UserRepository::class;
    }
}
