<?php

declare(strict_types=1);

namespace Tests\Feature\Base\Controller;

abstract class BaseControllerUpdateTest extends BaseControllerTest
{
    /**
     * Test update controller calls service and returns correct response
     *
     * Validates the update endpoint's functionality by testing PUT requests that
     * modify existing model instances through the service layer. This test creates
     * an existing model, generates update data, mocks the service to return the
     * updated model, and verifies that the controller processes the update request
     * correctly, returning a 200 OK status with the properly formatted updated
     * model data including the correct ID and modified attributes.
     */
    public function test_update_and_returns_correct_response(): void
    {
        $modelToUpdate = $this->configurator->getModelClass()::factory()->create();
        $requestData = $this->getRequestData();
        $modelToUpdateId = $modelToUpdate->id;
        $action = 'update';

        $this->setupServiceMocking($modelToUpdate, $action, $requestData);

        $response = $this->putJson("{$this->configurator->getEndpoint()}/{$modelToUpdateId}", $requestData);

        $this->assertJsonResponse($response, $modelToUpdate->toArray(), $modelToUpdateId, 200);
    }

    /**
     * Test that the update endpoint returns a 500 status when the Service throws an exception.
     *
     * Tests error handling for update operations when the service layer encounters
     * failures during model modification. This test creates an existing model,
     * configures the service mock to throw an exception during the update operation,
     * and verifies that the controller properly handles the service failure by
     * returning a 500 Internal Server Error status. This ensures that update
     * operation failures are handled gracefully without exposing internal errors.
     */
    public function test_update_returns_500_on_service_failure(): void
    {
        $modelToUpdate = $this->configurator->getModelClass()::factory()->create();
        $requestData = $this->getRequestData();

        $this->setupServiceMockingFailure('update');

        $response = $this->putJson("{$this->configurator->getEndpoint()}/{$modelToUpdate->id}", $requestData);

        $response->assertStatus(500);
    }

    /**
     * Test that attempting to update a non-existent model returns a 404 Not Found
     *
     * Validates proper RESTful behavior when attempting to update a resource that
     * doesn't exist in the system. This test sends a PUT request with a non-existent
     * ID (using PHP_INT_MAX as a guaranteed non-existent identifier) and verifies
     * that the controller returns the appropriate 404 Not Found status code.
     * This ensures that clients can distinguish between missing resources and
     * other types of errors during update operations.
     */
    public function test_update_returns_404_for_non_existent_model(): void
    {
        $requestData = $this->getRequestData();
        $nonExistentId = PHP_INT_MAX;

        $response = $this->putJson("{$this->configurator->getEndpoint()}/{$nonExistentId}", $requestData);

        $response->assertStatus(404);
    }
}
