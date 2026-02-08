<?php

declare(strict_types=1);

namespace Tests\Feature\Base\Controller;

use App\Contracts\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Mockery;
use Mockery\MockInterface;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\TestCase;

abstract class BaseControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The configurator of the model
     */
    protected BaseTestConfigurator $configurator;

    /**
     * Keys from the RequestData that must be excluded
     * before sending to the Service
     */
    protected array $excludedServiceKeys = [];

    /**
     * Get the configurator of the model
     */
    abstract protected function getConfigurator(): BaseTestConfigurator;

    /**
     * Get the service contract class name
     */
    abstract protected function getServiceContractClass(): string;

    /**
     * Returns the expected data array for the JSON response
     * excluding fields hidden by the model (e.g., 'password').
     *
     * @param  array  $requestData  The original data sent in the request.
     */
    abstract protected function getExpectedResponseData(array $requestData): array;

    /**
     * Helper to generate valid data for the test.
     */
    abstract protected function getRequestData(): array;

    /**
     * Set up the test
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->configurator = $this->getConfigurator();
    }

    // --- HELPER METHODS ---

    /**
     * Helper to mock the Service contract
     *
     * This method configures Mockery expectations for specific service actions
     * (create, update, find, etc.) and sets up argument validation to ensure
     * the controller passes the correct data to the service.
     *
     * @param  object  $expectedModel  The model returned by the service
     * @param  string  $action  The method to mock (create, update, delete, etc.)
     * @param  array  $serviceParams  The parameter data expected by the service
     */
    protected function setupServiceMocking(object $expectedModel, string $action, array $serviceParams): void
    {
        $modelClass = new ($this->configurator->getModelClass());
        $mockedRepository = Mockery::mock(BaseRepositoryInterface::class);
        $mockedRepository->shouldReceive('getModel')
            ->zeroOrMoreTimes()
            ->andReturn($modelClass);

        $this->mock($this->getServiceContractClass(), function (MockInterface $mock) use ($expectedModel, $serviceParams, $action, $mockedRepository) {

            $mock->shouldReceive('getRepository')
                ->zeroOrMoreTimes()
                ->andReturn($mockedRepository);

            $expectation = $mock->shouldReceive($action)
                ->once()
                ->andReturn($expectedModel);

            if (in_array($action, ['create', 'update'], true)) {

                $expectation->withArgs($this->getServiceArgumentsValidator($serviceParams));

            } elseif (in_array($action, ['index'], true)) {

                $expectation->with(Mockery::type(Collection::class));

            } elseif (in_array($action, ['find', 'delete'], true)) {

                if (! empty($serviceParams) && is_int($serviceParams[0] ?? null)) {
                    $expectation->with($serviceParams[0]);
                }

            }
        });
    }

    /**
     * Helper to mock the Service contract to simulate a database error
     *
     * Configures service mocks to throw exceptions, allowing tests to verify
     * that controllers properly handle service layer failures and return
     * appropriate error responses.
     *
     * @param  string  $action  The method to mock (create, update, delete, etc.)
     */
    protected function setupServiceMockingFailure(string $action): void
    {
        $this->mock($this->getServiceContractClass(), function (MockInterface $mock) use ($action): void {

            $mockedRepository = Mockery::mock(BaseRepositoryInterface::class);
            $mockedRepository->shouldReceive('getModel')
                ->andReturn(new ($this->configurator->getModelClass()));

            $mock->shouldReceive('getRepository')
                ->andReturn($mockedRepository);

            $mock->shouldReceive($action)
                ->once()
                ->andThrow(new \Exception('Simulated exception'));
        });
    }

    /**
     * Executes the final JSON assertion logic.
     *
     * This method ensures that the response contains the expected data format
     * with the correct model ID and properly formatted response data.
     *
     * @param  TestResponse  $response  The response object
     * @param  array  $expectedModel  The expected model to be returned
     * @param  int  $expectedId  The expected model id
     * @param  int  $expectedStatus  The expected response status
     */
    protected function assertJsonResponse(TestResponse $response, array $expectedModel, int $expectedId, int $expectedStatus): void
    {
        $response->assertStatus($expectedStatus);

        $expectedData = array_merge(
            ['id' => $expectedId],
            $this->getExpectedResponseData($expectedModel)
        );

        $response->assertJson(['data' => $expectedData]);
    }

    /**
     * Returns the validation function for Mockery::withArgs.
     *
     * The validator performs field-by-field comparison, handling different data
     * types appropriately (arrays for relationships, scalar values for simple fields)
     * and providing descriptive error messages when validation fails.
     *
     * @param  array  $requestData  The original data sent in the request.
     */
    protected function getServiceArgumentsValidator(array $requestData): callable
    {
        $expectedServiceData = $this->getServiceExpectedData($requestData);

        return function (Collection $data) use ($expectedServiceData) {

            $receivedData = $data->toArray();

            foreach ($expectedServiceData as $key => $value) {

                $this->assertArrayHasKey($key, $receivedData, "The key '{$key}' is missing in the data received by the service.");

                // For arrays (Many-to-Many relations like 'role_ids')
                if (is_array($value)) {
                    $this->assertEqualsCanonicalizing($value, $receivedData[$key], "The relation '{$key}' does not match.");
                }
                // For simple fields ('name', 'email', 'password')
                else {
                    $this->assertEquals($value, $receivedData[$key], "The value of '{$key}' does not match.");
                }
            }

            return true;
        };
    }

    /**
     * Returns the expected data array for the Service contract
     * excluding fields that should not be forwarded to the service
     * (such as confirmation fields, UI-specific data, or
     * security-sensitive information).
     *
     * @param  array  $requestData  The original data sent in the request.
     * @return array The cleaned payload.
     */
    protected function getServiceExpectedData(array $requestData): array
    {
        return collect($requestData)
            ->except($this->excludedServiceKeys)
            ->toArray();
    }
}
