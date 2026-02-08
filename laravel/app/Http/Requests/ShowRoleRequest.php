<?php

namespace App\Http\Requests;

use App\Contracts\Services\RoleServiceInterface;
use App\Http\Requests\Base\BaseShowRequest;

class ShowRoleRequest extends BaseShowRequest
{
    /**
     * The service class associated with the request.
     */
    public function getServiceClass(): string
    {
        return RoleServiceInterface::class;
    }
}
