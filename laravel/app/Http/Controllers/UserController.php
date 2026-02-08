<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\UserServiceInterface;
use App\Http\Controllers\Base\BaseCrudController;

/**
 * @property UserServiceInterface $service
 */
class UserController extends BaseCrudController
{
    public function __construct(UserServiceInterface $service)
    {
        parent::__construct($service);
    }

    /**
     * Get the base name for request classes
     */
    protected function getRequestBaseName(): string
    {
        return 'User';
    }
}
