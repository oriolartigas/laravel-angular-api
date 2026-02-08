<?php

declare(strict_types=1);

namespace Tests\Feature\Address;

use Tests\Feature\Base\Crud\BaseCrudUpdateTest;
use Tests\Helpers\AddressTestConfigurator;
use Tests\Helpers\Base\BaseTestConfigurator;

/**
 * @property AddressTestConfigurator $configurator
 */
class AddressCrudUpdateTest extends BaseCrudUpdateTest
{
    /**
     * Get the configurator class
     *
     * @return AddressTestConfigurator
     */
    protected function getConfigurator(): BaseTestConfigurator
    {
        return new AddressTestConfigurator;
    }
}
