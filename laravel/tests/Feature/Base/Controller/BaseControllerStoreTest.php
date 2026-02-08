<?php

declare(strict_types=1);

namespace Tests\Feature\Base\Controller;

abstract class BaseControllerStoreTest extends BaseControllerTest
{
    /**
     * Test store controller calls service and returns correct response
     *
     * Validates the creation endpoint's ability to process POST requests and
     * create new model instances through the service layer. This test generates
     * valid request data, mocks the service to return a newly created model,
     * and verifies that the controller properly processes the creation request
     * and returns the expected 201 Created status with the correct response
     * data structure including the new model's ID and formatted data.
     */
    public function test_store_and_returns_correct_response(): void
    {
        $modelClass = new ($this->configurator->getModelClass());
        $requestData = $this->getRequestData();

        $mockedModel = $modelClass::make($requestData);
        $mockedModel->id = 1;

        $action = 'create';

        $this->setupServiceMocking($mockedModel, $action, $requestData);

        $response = $this->postJson($this->configurator->getEndpoint(), $requestData);

        $this->assertJsonResponse($response, $requestData, $mockedModel->id, 201);
    }

    /**
     * Test that the store endpoint returns a 500 status when the Service throws an exception.
     *
     * Tests error handling during model creation when the service layer encounters
     * failures such as database constraints, validation errors, or other runtime
     * exceptions. This test configures the service mock to throw an exception
     * during the create operation and verifies that the controller handles the
     * failure gracefully by returning a 500 Internal Server Error status,
     * ensuring robust error handling for creation operations.
     */
    public function test_store_returns_500_on_service_failure(): void
    {
        $requestData = $this->getRequestData();

        $this->setupServiceMockingFailure('create');

        $response = $this->postJson($this->configurator->getEndpoint(), $requestData);

        $response->assertStatus(500);
    }
}
