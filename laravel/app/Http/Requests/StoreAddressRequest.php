<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Contracts\Services\AddressServiceInterface;
use App\Http\Requests\Base\BaseStoreRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreAddressRequest extends BaseStoreRequest
{
    /**
     * The service class associated with the request.
     */
    public function getServiceClass(): string
    {
        return AddressServiceInterface::class;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Append the parent rules
            ...parent::rules(),

            // Custom rules
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'street' => [
                'required',
                'string',
                'max:255',
            ],
            'city' => [
                'required',
                'string',
                'max:255',
            ],
            'postal_code' => [
                'required',
                'string',
                'max:20',
            ],
            'country' => [
                'required',
                'string',
                'max:255',
            ],
            'state' => [
                'required',
                'string',
                'max:255',
            ],

            // User rules
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ];
    }
}
