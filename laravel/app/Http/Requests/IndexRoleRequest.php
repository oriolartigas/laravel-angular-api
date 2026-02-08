<?php

namespace App\Http\Requests;

use App\Contracts\Services\RoleServiceInterface;
use App\Http\Requests\Base\BaseIndexRequest;
use App\Models\Role;

class IndexRoleRequest extends BaseIndexRequest
{
    /**
     * The service class associated with the request.
     */
    public function getServiceClass(): string
    {
        return RoleServiceInterface::class;
    }

    /**
     * The model class associated with the request.
     */
    public function getModelClass(): string
    {
        return Role::class;
    }
}
