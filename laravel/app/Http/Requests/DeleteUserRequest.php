<?php

namespace App\Http\Requests;

use App\Contracts\Services\UserServiceInterface;
use App\Http\Requests\Base\BaseDeleteRequest;

class DeleteUserRequest extends BaseDeleteRequest
{
    /**
     * The service class associated with the request.
     */
    public function getServiceClass(): string
    {
        return UserServiceInterface::class;
    }
}
