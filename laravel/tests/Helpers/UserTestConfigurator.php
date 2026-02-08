<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Models\Address;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Tests\Helpers\Base\BaseTestConfigurator;

class UserTestConfigurator extends BaseTestConfigurator
{
    /**
     * Get the model class name
     */
    public function getModelClass(): string
    {
        return User::class;
    }

    /**
     * Get the model instance
     */
    public function getModelInstance(): Model
    {
        // Get the model class
        $modelClass = $this->getModelClass();

        // Get the model
        $model = new $modelClass;

        return $model;
    }

    /**
     * Get the belongsTo relations
     */
    public function getBelongsToRelations(): array
    {
        return [];
    }

    /**
     * Get belongsToMany relations
     */
    public function getBelongsToManyRelations(): array
    {
        return [
            'role_ids' => Role::class,
        ];
    }

    /**
     * Get hasMany relations
     */
    public function getHasManyRelations(): array
    {
        return [
            'addresses' => Address::class,
        ];
    }

    /**
     * Get the base API endpoint URL
     */
    public function getEndpoint(): string
    {
        return '/api/users';
    }
}
