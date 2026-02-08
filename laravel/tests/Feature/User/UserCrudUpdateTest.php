<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use Tests\Feature\Base\Crud\BaseCrudUpdateTest;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\UserTestConfigurator;

/**
 * @property UserTestConfigurator $configurator
 */
class UserCrudUpdateTest extends BaseCrudUpdateTest
{
    /**
     * Get the configurator class
     *
     * @return UserTestConfigurator
     */
    protected function getConfigurator(): BaseTestConfigurator
    {
        return new UserTestConfigurator;
    }

    /**
     * Override createAttributes to include 'password_confirmation'
     *
     * @param  bool  $withBelongsTo  Get the belongsTo relations
     * @param  bool  $withBelongsToMany  Get the belongsToMany relations
     * @param  bool  $withHasMany  Get the hasMany relations
     * @return array Complete set of valid test data
     */
    protected function createAttributes(bool $withBelongsTo = false, bool $withBelongsToMany = false, bool $withHasMany = false): array
    {
        $data = parent::createAttributes(withBelongsTo: $withBelongsTo, withBelongsToMany: $withBelongsToMany, withHasMany: $withHasMany);

        if (isset($data['password'])) {
            $data['password_confirmation'] = $data['password'];
        }

        return $data;
    }

    /**
     * Exclude non-fillable or system-managed attributes
     *
     * @param  array  $data  The data from the Model Factory.
     * @return array The data expected to be stored in the database.
     */
    protected function getExpectedDatabaseData(array $data): array
    {
        return collect(parent::getExpectedDatabaseData($data))
            ->except([
                'password',
                'password_confirmation',
                'remember_token',
                'email_verified_at',
            ])
            ->toArray();
    }

    /**
     * Exclude fields that won't appear in the JSON response.
     *
     * @param  array  $data  The data from the Model Factory.
     * @return array The data expected in the JSON response.
     */
    protected function getExpectedResponseKeys(array $data): array
    {
        return collect(parent::getExpectedResponseKeys($data))
            ->except([
                'password',
                'password_confirmation',
                'remember_token',
                'email_verified_at',
            ])
            ->toArray();
    }
}
