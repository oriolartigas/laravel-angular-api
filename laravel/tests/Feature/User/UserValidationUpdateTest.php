<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Tests\Feature\Base\Validation\BaseValidationUpdateTest;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\Helpers\UserTestConfigurator;

/**
 * @property UserTestConfigurator $configurator
 */
class UserValidationUpdateTest extends BaseValidationUpdateTest
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
     * Defines the invalid data required to fail validation for update method
     */
    public static function updateValidationDataSets(): array
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
     * Test that updating a user with duplicate name fails validation
     */
    public function test_update_fails_with_duplicate_name(): void
    {
        $existingUser = User::factory()->create(['name' => 'Existing User']);
        $userToUpdate = User::factory()->create(['name' => 'User To Update']);

        $requestData = ['name' => $existingUser->name];
        $this->runValidationAssertion('PUT', $requestData, ['name'], $userToUpdate->id);
    }

    /**
     * Test that updating a user with duplicate email fails validation
     */
    public function test_update_fails_with_duplicate_email(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $userToUpdate = User::factory()->create(['email' => 'update@example.com']);

        $requestData = ['email' => $existingUser->email];
        $this->runValidationAssertion('PUT', $requestData, ['email'], $userToUpdate->id);
    }

    /**
     * Test that the unique name validation rule allows the model being updated
     * while the name is the same.
     */
    public function test_update_allows_existing_name_for_same_user(): void
    {
        $userToUpdate = User::factory()->create(['name' => 'Same User', 'email' => 'original@example.com']);

        $requestData = ['name' => $userToUpdate->name, 'email' => 'updated@example.com'];
        $url = $this->configurator->getEndpoint().'/'.$userToUpdate->id;
        $response = $this->putJson($url, $requestData);
        $response->assertStatus(200);
    }

    /**
     * Test that the unique email validation rule allows the model being updated
     * while the email is the same.
     */
    public function test_update_allows_existing_email_for_same_user(): void
    {
        $userToUpdate = User::factory()->create(['name' => 'Original Name', 'email' => 'same@example.com']);

        $requestData = ['name' => 'Updated Name', 'email' => $userToUpdate->email];
        $url = $this->configurator->getEndpoint().'/'.$userToUpdate->id;
        $response = $this->putJson($url, $requestData);
        $response->assertStatus(200);
    }
}
