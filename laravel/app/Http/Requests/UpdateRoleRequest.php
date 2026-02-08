<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Contracts\Services\RoleServiceInterface;
use App\Http\Requests\Base\BaseUpdateRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends BaseUpdateRequest
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
        $roleId = $this->route(param: 'role');

        return [
            // Append the parent rules
            ...parent::rules(),

            // Custom rules
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique(table: 'roles', column: 'name')->ignore(id: $roleId),
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            // Role rules
            'user_ids' => [
                'sometimes',
                'array',
            ],
            'user_ids.*' => [
                'integer',
                'exists:users,id',
            ],
        ];
    }
}
