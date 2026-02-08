<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Contracts\Services\UserServiceInterface;
use App\Http\Requests\Base\BaseUpdateRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseUpdateRequest
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
        $userId = $this->route(param: 'user');

        return [
            // Append the parent rules
            ...parent::rules(),

            // Custom rules
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique(table: 'users', column: 'name')->ignore(id: $userId),
            ],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique(table: 'users', column: 'email')->ignore(id: $userId),
            ],
            'password' => [
                'sometimes',
                'confirmed',
                'min:8',
            ],

            // Role rules
            'role_ids' => [
                'sometimes',
                'array',
            ],
            'role_ids.*' => [
                'integer',
                'exists:roles,id',
            ],
        ];
    }
}
