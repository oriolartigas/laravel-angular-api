<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Contracts\Services\UserServiceInterface;
use App\Http\Requests\Base\BaseStoreRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreUserRequest extends BaseStoreRequest
{
    /**
     * The service class associated with the request.
     */
    public function getServiceClass(): string
    {
        return UserServiceInterface::class;
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
                'unique:users,name',
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'confirmed',
                'min:8',
            ],

            // Roles rules
            'role_ids' => [
                'nullable',
                'array',
            ],
            'role_ids.*' => [
                'integer',
                'exists:roles,id',
            ],

            // Addresses rules
            'addresses' => [
                'nullable', 'array',
            ],
            'addresses.*.name' => [
                'required_with:addresses',
                'string',
                'max:255',
            ],
            'addresses.*.street' => [
                'required_with:addresses',
                'string',
                'max:255',
            ],
            'addresses.*.city' => [
                'required_with:addresses',
                'string',
                'max:255',
            ],
            'addresses.*.postal_code' => [
                'required_with:addresses',
                'string',
                'max:20',
            ],
            'addresses.*.state' => [
                'required_with:addresses',
                'string',
                'max:255',
            ],
            'addresses.*.country' => [
                'required_with:addresses',
                'string',
                'max:255',
            ],
        ];
    }
}
