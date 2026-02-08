<?php

namespace App\Http\Requests;

use App\Contracts\Services\UserServiceInterface;
use App\Http\Requests\Base\BaseShowRequest;

class ShowUserRequest extends BaseShowRequest
{
    /**
     * The service class associated with the request.
     */
    public function getServiceClass(): string
    {
        return UserServiceInterface::class;
    }
}
