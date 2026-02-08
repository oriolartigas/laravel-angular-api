<?php

namespace App\Http\Requests;

use App\Contracts\Services\AddressServiceInterface;
use App\Http\Requests\Base\BaseIndexRequest;
use App\Models\Address;

class IndexAddressRequest extends BaseIndexRequest
{
    /**
     * The service class associated with the request.
     */
    public function getServiceClass(): string
    {
        return AddressServiceInterface::class;
    }

    /**
     * The model class associated with the request.
     */
    public function getModelClass(): string
    {
        return Address::class;
    }
}
