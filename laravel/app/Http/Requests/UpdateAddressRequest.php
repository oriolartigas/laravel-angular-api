<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Contracts\Services\AddressServiceInterface;
use App\Http\Requests\Base\BaseUpdateRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateAddressRequest extends BaseUpdateRequest
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
                'sometimes',
                'string',
                'max:255',
            ],
            'street' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'city' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'postal_code' => [
                'sometimes',
                'string',
                'max:20',
            ],
            'country' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'state' => [
                'sometimes',
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
