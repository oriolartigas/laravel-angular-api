<?php

declare(strict_types=1);

namespace Tests\Feature\Role;

use App\Contracts\Services\RoleServiceInterface;
use App\Models\User;
use Tests\Feature\Base\Controller\BaseControllerIndexTest;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\RoleTestConfigurator;

/**
 * @property RoleTestConfigurator $configurator
 */
class RoleControllerIndexTest extends BaseControllerIndexTest
{
    /**
     * Keys that must be excluded before sending to the Service
     */
    protected array $excludedServiceKeys = [];

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
     * Get the service contract class name
     */
    protected function getServiceContractClass(): string
    {
        return RoleServiceInterface::class;
    }

    /**
     * Generate valid data for the test
     */
    protected function getRequestData(): array
    {
        $user = User::factory()->count(2)->create();

        return $this->configurator->getModelClass()::factory()->raw([
            'user_ids' => $user->pluck('id')->toArray(),
        ]);
    }

    /**
     * Returns the expected data array for the JSON response
     *
     * @param  array  $requestData  The original data sent in the request.
     */
    protected function getExpectedResponseData(array $requestData): array
    {
        return collect($requestData)->except(['user_ids'])->toArray();
    }
}
