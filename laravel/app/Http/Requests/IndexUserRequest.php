<?php

namespace App\Http\Requests;

use App\Contracts\Services\UserServiceInterface;
use App\Http\Requests\Base\BaseIndexRequest;
use App\Models\User;

class IndexUserRequest extends BaseIndexRequest
{
    /**
     * The service class associated with the request.
     */
    public function getServiceClass(): string
    {
        return UserServiceInterface::class;
    }

    /**
     * The model class associated with the request.
     */
    public function getModelClass(): string
    {
        return User::class;
    }
}
