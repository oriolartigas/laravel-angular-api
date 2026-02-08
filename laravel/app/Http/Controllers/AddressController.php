<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\AddressServiceInterface;
use App\Http\Controllers\Base\BaseCrudController;

/**
 * @property AddressServiceInterface $service
 */
class AddressController extends BaseCrudController
{
    public function __construct(AddressServiceInterface $service)
    {
        parent::__construct($service);
    }

    /**
     * Get the base name for request classes
     */
    protected function getRequestBaseName(): string
    {
        return 'Address';
    }
}
