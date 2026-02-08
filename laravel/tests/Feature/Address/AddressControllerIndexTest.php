<?php

declare(strict_types=1);

namespace Tests\Feature\Address;

use App\Contracts\Services\AddressServiceInterface;
use Tests\Feature\Base\Controller\BaseControllerIndexTest;
use Tests\Helpers\AddressTestConfigurator;
use Tests\Helpers\Base\BaseTestConfigurator;

/**
 * @property AddressTestConfigurator $configurator
 */
class AddressControllerIndexTest extends BaseControllerIndexTest
{
    /**
     * Keys that must be excluded before sending to the Service
     */
    protected array $excludedServiceKeys = [];

    /**
     * Get the configurator class
     *
     * @return AddressTestConfigurator
     */
    protected function getConfigurator(): BaseTestConfigurator
    {
        return new AddressTestConfigurator;
    }

    /**
     * Get the service contract class name
     */
    protected function getServiceContractClass(): string
    {
        return AddressServiceInterface::class;
    }

    /**
     * Generate valid data for the test
     */
    protected function getRequestData(): array
    {
        return $this->configurator->getModelClass()::factory()->raw();
    }

    /**
     * Returns the expected data array for the JSON response
     *
     * @param  array  $requestData  The original data sent in the request.
     */
    protected function getExpectedResponseData(array $requestData): array
    {
        return collect($requestData)->toArray();
    }
}
