<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\ModelCreateException;
use App\Exceptions\ModelDeleteException;
use App\Exceptions\ModelUpdateException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class BaseRepositoryTest
 *
 * This test suite verifies that the BaseRepository properly throws
 * custom exceptions (ModelCreateException, ModelUpdateException, ModelDeleteException)
 * when database errors occur during CRUD operations.
 */
class ModelExceptionTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(abstract: UserRepository::class);
    }

    /**
     * Test that a ModelCreateException is thrown when a model cannot be created.
     */
    public function test_model_create_exception_is_thrown_on_error(): void
    {
        $this->expectException(exception: ModelCreateException::class);
        $this->expectExceptionMessage(message: 'Error creating model');
        $this->repository->create(data: ['non_existing_column' => 'invalid']);
    }

    /**
     * Test that a ModelUpdateException is thrown when a model cannot be updated
     * In this case we try to update a user with an email that already exists
     */
    public function test_model_update_exception_is_thrown_on_error(): void
    {
        $this->expectException(exception: ModelUpdateException::class);

        $userToUpdate = User::factory()->create(attributes: ['email' => 'user_to_update@test.com']);
        User::factory()->create(attributes: ['email' => 'unique_violation@test.com']);

        // Try to update the user with an email that already exists
        $this->repository->update(id: $userToUpdate->id, data: [
            'email' => 'unique_violation@test.com',
        ]);
    }

    /**
     * Test that a ModelDeleteException is thrown when a model cannot be deleted.
     */
    public function test_model_delete_exception_is_thrown_on_error(): void
    {
        // Create a user to delete
        $userToDelete = User::factory()->create();

        // Listen to the 'deleting' event and return false and force the repository to throw an exception
        User::deleting(callback: function ($model): bool {
            return false;
        });

        // Assert that the exception is thrown
        $this->expectException(exception: ModelDeleteException::class);

        // Try to delete the user
        $this->repository->delete(id: $userToDelete->id);

    }

    /**
     * Test that a ModelNotFoundException is thrown when a model is not found.
     */
    public function test_throws_model_not_found_exception_on_delete_of_nonexistent_id(): void
    {
        $this->expectException(exception: ModelNotFoundException::class);

        $this->repository->delete(id: 999999);
    }
}
