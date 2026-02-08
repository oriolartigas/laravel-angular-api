<?php

declare(strict_types=1);

namespace Tests\Feature\Base\Controller;

abstract class BaseControllerIndexTest extends BaseControllerTest
{
    /**
     * Test index controller calls service and returns correct collection
     *
     * Validates that the index endpoint properly retrieves and returns a collection
     * of models through the service layer. This test creates multiple model instances,
     * configures service mocking to return the expected collection, and verifies
     * that the controller correctly processes the service response and returns
     * the appropriate JSON structure with the correct number of items and proper
     * data formatting. It ensures the basic listing functionality works as expected.
     */
    public function test_index_and_returns_correct_response(): void
    {
        $models = $this->configurator->getModelClass()::factory()->count(3)->create();
        $params = [
            'where' => $this->configurator->getIndexWhereParams($models->first()),
        ];
        $action = 'index';

        $this->setupServiceMocking($models, $action, $params);

        $response = $this->getJson($this->configurator->getEndpoint().'?'.http_build_query($params));
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');

        $expectedData = $this->getExpectedResponseData($models->first()->toArray());

        $response->assertJson([
            'data' => [$expectedData],
        ]);
    }

    /**
     * Test index controller calls service with all allowed options and returns correct collection
     *
     * Comprehensive test that validates the index endpoint's ability to handle
     * complex query parameters including filtering (where), eager loading (with),
     * relationship counting (withCount), and sorting options. This test ensures
     * that the controller properly processes all available query options and
     * passes them correctly to the service layer, while still returning the
     * expected response format and maintaining proper data integrity throughout
     * the request processing pipeline.
     */
    public function test_index_with_all_options_calls_service_correctly(): void
    {
        $models = $this->configurator->getModelClass()::factory()->count(3)->create();
        $action = 'index';

        $queryParams = [
            'where' => $this->configurator->getIndexWhereParams($models->first()),
            'with' => $this->configurator->getIndexWithParams(),
            'withCount' => $this->configurator->getIndexWithCountParams(),
            'sort' => $this->configurator->getIndexSortParams(),
        ];

        $this->setupServiceMocking($models, $action, []);

        $response = $this->getJson($this->configurator->getEndpoint().'?'.http_build_query($queryParams));

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        $expectedData = $this->getExpectedResponseData($models->first()->toArray());
        $response->assertJson([
            'data' => [$expectedData],
        ]);
    }

    /**
     * Test that the index endpoint returns a 500 status when the Service throws an exception.
     *
     * Validates error handling behavior when the service layer encounters failures
     * during index operations. This test configures the service mock to throw an
     * exception and verifies that the controller properly catches the exception
     * and returns an appropriate 500 Internal Server Error response.
     */
    public function test_index_returns_500_on_service_failure(): void
    {
        $model = $this->configurator->getModelClass()::factory()->create();

        $params = [
            'where' => $this->configurator->getIndexWhereParams($model),
        ];

        $this->setupServiceMockingFailure('index');

        $response = $this->getJson($this->configurator->getEndpoint().'?'.http_build_query($params));

        $response->assertStatus(500);
    }
}
