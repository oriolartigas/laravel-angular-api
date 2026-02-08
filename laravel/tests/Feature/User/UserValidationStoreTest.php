<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Tests\Feature\Base\Validation\BaseValidationStoreTest;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\UserTestConfigurator;

/**
 * @property UserTestConfigurator $configurator
 */
class UserValidationStoreTest extends BaseValidationStoreTest
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
     * Gets the configurator class FQCN to be instantiated statically.
     */
    protected static function getConfiguratorClass(): string
    {
        return UserTestConfigurator::class;
    }

    /**
     * Defines the invalid data required to fail validation for store method
     */
    public static function storeValidationDataSets(): array
    {
        return static::validationDataSets();
    }

    /**
     * Returns an instance of the model to update
     *
     * @return User
     */
    protected function getModelToUpdate(): Model
    {
        return $this->configurator->getModelClass()::factory()->create();
    }

    /**
     * Override the parent's function to include 'password_confirmation'
     */
    protected function getValidBaseData(): array
    {
        $data = parent::getValidBaseData();

        $data['password_confirmation'] = $data['password'] ?? 'secret12345';

        return $data;
    }

    /**
     * Provides fixed data sets for validation failure scenarios.
     */
    public static function validationDataSets(): array
    {
        return [
            'missing_name' => [['name' => null, 'email' => 'test@example.com', 'password' => 'secret123', 'password_confirmation' => 'secret123'], ['name']],
            'missing_email' => [['name' => 'Test User', 'email' => null, 'password' => 'secret123', 'password_confirmation' => 'secret123'], ['email']],
            'missing_password' => [['name' => 'Test User', 'email' => 'test@example.com', 'password' => null, 'password_confirmation' => 'secret123'], ['password']],
            'unconfirmed_password' => [['name' => 'Test User', 'email' => 'test@example.com', 'password' => 'secret123', 'password_confirmation' => 'mismatch'], ['password']],
            'invalid_email_format' => [['name' => 'Test User', 'email' => 'not-an-email', 'password' => 'secret123', 'password_confirmation' => 'secret123'], ['email']],
        ];
    }

    /**
     * Test that creating a user with duplicate name fails validation
     */
    public function test_store_fails_with_duplicate_name(): void
    {
        $existingUser = User::factory()->create(['name' => 'Existing User']);

        $requestData = [
            'name' => $existingUser->name,
            'email' => 'new@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $this->runValidationAssertion('POST', $requestData, ['name']);
    }

    /**
     * Test that creating a user with duplicate email fails validation
     */
    public function test_store_fails_with_duplicate_email(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $requestData = [
            'name' => 'New User',
            'email' => $existingUser->email,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $this->runValidationAssertion('POST', $requestData, ['email']);
    }
}
