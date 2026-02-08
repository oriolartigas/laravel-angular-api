<?php

namespace App\Http\Requests;

use App\Contracts\Services\AddressServiceInterface;
use App\Http\Requests\Base\BaseShowRequest;

class ShowAddressRequest extends BaseShowRequest
{
    /**
     * The service class associated with the request.
     */
    public function getServiceClass(): string
    {
        return AddressServiceInterface::class;
    }
}
