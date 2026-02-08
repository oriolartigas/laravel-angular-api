<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Tests\Helpers\Base\BaseTestConfigurator;

class RoleTestConfigurator extends BaseTestConfigurator
{
    /**
     * Get the model class name
     */
    public function getModelClass(): string
    {
        return Role::class;
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
     * Get hasMany relations
     */
    public function getHasManyRelations(): array
    {
        return [];
    }

    /**
     * Get belongsToMany relations
     */
    public function getBelongsToManyRelations(): array
    {
        return [
            'user_ids' => User::class,
        ];
    }

    /**
     * Get the base API endpoint URL
     */
    public function getEndpoint(): string
    {
        return 'api/roles';
    }
}
