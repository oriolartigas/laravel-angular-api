<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\RoleServiceInterface;
use App\Http\Controllers\Base\BaseCrudController;

/**
 * @property RoleServiceInterface $service
 */
class RoleController extends BaseCrudController
{
    public function __construct(RoleServiceInterface $service)
    {
        parent::__construct($service);
    }

    /**
     * Get the base name for request classes
     */
    protected function getRequestBaseName(): string
    {
        return 'Role';
    }
}
