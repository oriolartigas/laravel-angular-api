<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Tests\Helpers\Base\BaseTestConfigurator;

class AddressTestConfigurator extends BaseTestConfigurator
{
    /**
     * Get the model class name
     */
    public function getModelClass(): string
    {
        return Address::class;
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
        return [
            'user_id' => User::class,
        ];
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
        return [];
    }

    /**
     * Get the base API endpoint URL
     */
    public function getEndpoint(): string
    {
        return '/api/addresses';
    }
}
