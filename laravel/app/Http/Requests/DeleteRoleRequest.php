<?php

namespace App\Http\Requests;

use App\Contracts\Services\RoleServiceInterface;
use App\Http\Requests\Base\BaseDeleteRequest;

class DeleteRoleRequest extends BaseDeleteRequest
{
    /**
     * The service class associated with the request.
     */
    public function getServiceClass(): string
    {
        return RoleServiceInterface::class;
    }
}
