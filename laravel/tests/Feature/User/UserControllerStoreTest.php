<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Contracts\Services\UserServiceInterface;
use Faker\Factory;
use Tests\Feature\Base\Controller\BaseControllerStoreTest;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\UserTestConfigurator;

/**
 * @property UserTestConfigurator $configurator
 */
class UserControllerStoreTest extends BaseControllerStoreTest
{
    /**
     * Keys from the RequestData that must be excluded
     */
    protected array $excludedServiceKeys = [
        'password_confirmation',
    ];

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
     * Get the service contract class name
     */
    protected function getServiceContractClass(): string
    {
        return UserServiceInterface::class;
    }

    /**
     * Generate valid data for the test
     */
    protected function getRequestData(): array
    {
        $faker = Factory::create();

        return [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
        ];
    }

    /**
     * Returns the expected data array for the JSON response
     *
     * @param  array  $requestData  The original data sent in the request
     */
    protected function getExpectedResponseData(array $requestData): array
    {
        return collect($requestData)
            ->except([
                'password',
                'password_confirmation',
                'remember_token',
                'email_verified_at',
                'role_ids',
            ])
            ->toArray();
    }
}
