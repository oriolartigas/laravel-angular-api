<?php

declare(strict_types=1);

namespace Tests\Feature\Base\Controller;

abstract class BaseControllerShowTest extends BaseControllerTest
{
    /**
     * Test show controller calls service and returns correct response
     *
     * Validates that the show endpoint correctly retrieves a single model instance
     * by ID through the service layer and returns it in the proper JSON format.
     * This test creates a model instance, mocks the service to return that model,
     * and verifies that the controller processes the service response correctly,
     * including proper ID handling and response data formatting.
     */
    public function test_show_and_returns_correct_response(): void
    {
        $model = $this->configurator->getModelClass()::factory()->create();
        $modelId = $model->id;
        $action = 'find';

        $this->setupServiceMocking($model, $action, $model->toArray());

        $response = $this->getJson("{$this->configurator->getEndpoint()}/{$modelId}");

        $this->assertJsonResponse($response, $this->getExpectedResponseData($model->toArray()), $modelId, 200);
    }

    /**
     * Test that the show endpoint returns a 500 status when the Service throws an exception.
     *
     * Tests error handling for the show endpoint when service layer failures occur.
     * This test configures the service mock to throw an exception during the find
     * operation and verifies that the controller handles the exception appropriately
     * by returning a 500 Internal Server Error status.
     */
    public function test_show_returns_500_on_service_failure(): void
    {
        $model = $this->configurator->getModelClass()::factory()->create();

        $this->setupServiceMockingFailure('find');

        $response = $this->getJson("{$this->configurator->getEndpoint()}/{$model->id}");
        $response->assertStatus(500);
    }

    /**
     * Test that attempting to show a non-existent model returns a 404 Not Found
     *
     * Validates proper handling of requests for non-existent resources by testing
     * the show endpoint with an ID that doesn't correspond to any existing model.
     * This test uses PHP_INT_MAX as a guaranteed non-existent ID and verifies
     * that the controller returns the appropriate 404 Not Found status code.
     */
    public function test_show_returns_404_for_non_existent_model(): void
    {
        $nonExistentId = PHP_INT_MAX;

        $response = $this->getJson("{$this->configurator->getEndpoint()}/{$nonExistentId}");

        $response->assertStatus(404);
    }
}
