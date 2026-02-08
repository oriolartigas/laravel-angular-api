<?php

namespace App\Http\Requests;

use App\Contracts\Services\RoleServiceInterface;
use App\Http\Requests\Base\BaseStoreRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class StoreRoleRequest extends BaseStoreRequest
{
    /**
     * The service class associated with the request.
     */
    public function getServiceClass(): string
    {
        return RoleServiceInterface::class;
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
                'unique:roles,name',
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            // User rules
            'user_ids' => [
                'nullable',
                'array',
            ],
            'user_ids.*' => [
                'integer',
                'exists:users,id',
            ],
        ];
    }
}
