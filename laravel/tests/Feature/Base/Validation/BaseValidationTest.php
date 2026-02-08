<?php

declare(strict_types=1);

namespace Tests\Feature\Base\Validation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\Base\BaseTestConfigurator;
use Tests\TestCase;

abstract class BaseValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The configurator of the model
     */
    protected BaseTestConfigurator $configurator;

    /**
     * Get the configurator of the model
     *
     * This method must be implemented by concrete test classes to provide a specific
     * BaseTestConfigurator instance that contains all the necessary configuration
     * for testing validation rules, including model class, endpoint URLs, and
     * field definitions for whereable, withable, and sortable parameters.
     */
    abstract protected function getConfigurator(): BaseTestConfigurator;

    /**
     * Returns an instance of the model to update
     *
     * This method should create and return a valid model instance that can be
     * used as the target for update validation tests. The returned model should
     * have all necessary relationships and attributes properly configured to
     * serve as a baseline for testing update validation scenarios.
     *
     * @return Model A valid model instance ready for update testing
     */
    abstract protected function getModelToUpdate(): Model;

    /**
     * Set up the test
     *
     * Initializes the test environment by calling the parent setUp method
     * and configuring the test-specific configurator instance. This method
     * is called before each test method execution to ensure a clean and
     * consistent testing environment with all necessary dependencies properly
     * initialized for validation testing.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->configurator = $this->getConfigurator();
    }

    /**
     * Gets the configurator class to be instantiated statically
     * This method MUST be overridden in the child class.
     *
     * This static method is required for data providers to work correctly,
     * as data providers are called before the test class is instantiated.
     * It should return the fully qualified class name of the configurator
     * that will be used to generate validation test data sets.
     *
     * @return string Fully qualified class name of the configurator
     */
    protected static function getConfiguratorClass(): string
    {
        // Throw an exception to force implementation in the child class
        throw new \Exception(static::class.' must implement the static method getConfiguratorClass().');
    }

    /**
     * Get valid data using the model factory
     *
     * This method generates a complete set of valid test data using the model's
     * factory. The returned data serves as a baseline for creating valid requests
     * and can be modified in individual tests to create specific validation
     * scenarios by introducing invalid values or missing required fields.
     *
     * @return array Complete set of valid model data from factory
     */
    protected function getValidBaseData(): array
    {
        return $this->configurator->getModelClass()::factory()->raw();
    }

    /**
     * Run validation assertion
     *
     * This helper method executes validation tests by making HTTP requests
     * with invalid data and asserting that the appropriate validation errors
     * are returned. It handles different HTTP methods and constructs the
     * correct URLs for various endpoints, ensuring consistent validation
     * testing across all CRUD operations.
     *
     * @param  string  $httpMethod  HTTP method to use for the request
     * @param  array  $requestData  Invalid data to send in the request
     * @param  array  $expectedFails  Fields expected to fail validation
     * @param  int|null  $modelId  Model ID for update/delete operations
     */
    protected function runValidationAssertion(string $httpMethod, array $requestData, array $expectedFails, ?int $modelId = null): void
    {
        $endpoint = $this->configurator->getEndpoint();
        $url = $endpoint;

        if ($httpMethod === 'PUT' || $httpMethod === 'PATCH') {
            $id = $modelId ?? $this->getModelToUpdate()->id;
            $url = "{$endpoint}/{$id}";
        }

        if ($httpMethod === 'GET') {
            $queryString = http_build_query(data: $requestData);
            $urlWithQuery = "{$url}?{$queryString}";

            $response = $this->json($httpMethod, $urlWithQuery, []);
        } else {
            $response = $this->json($httpMethod, $url, $requestData);
        }

        $response->assertStatus(422);
        $response->assertInvalid($expectedFails);
    }

    // --- DATA PROVIDERS (Must be static and implemented in the child class) ---

    /**
     * Generates ALL generic validation data sets for Index query parameters (where, with, sort).
     * This method is called by the child class.
     *
     * This static method dynamically generates comprehensive test data sets for
     * index endpoint validation by examining the model's configuration and creating
     * various invalid scenarios for where clauses, with relations, withCount relations,
     * and sort parameters. It ensures thorough validation testing coverage for all
     * supported query parameter types.
     *
     * @return array Complete array of validation test cases for index endpoint
     */
    public static function indexValidationDataSets(): array
    {
        $configuratorClass = static::getConfiguratorClass();
        $configurator = new $configuratorClass;

        $whereableFields = $configurator->getWhereable();
        $withableRelations = $configurator->getWithable();
        $withCountableRelations = $configurator->getWithCountable();
        $sortableFields = $configurator->getSortable();

        $datasets = [];

        if (! empty($whereableFields)) {
            $firstWhereableField = $whereableFields[0];

            $datasets['where_invalid_key'] = [
                ['where' => ['invalid_key' => 'invalid_key']],
                ['where'],
            ];

            $datasets['where_invalid_value'] = [
                ['where' => [$firstWhereableField => ['not_a_string']]],
                ['where.'.$firstWhereableField],
            ];
        }

        // Add with validation
        if (! empty($withableRelations)) {
            $firstWithableRelation = $withableRelations[0];

            $datasets['with_invalid_type_array'] = [
                ['with' => ['array_instead_of_string']],
                ['with'],
            ];

            $datasets['with_invalid_type_int'] = [
                ['with' => 1234],
                ['with'],
            ];

            // Check rule AllowedQueryRelations with a valid relation and an invalid one
            $datasets['with_invalid_relation'] = [
                ['with' => $firstWithableRelation.',non_existent_relation'],
                ['with'],
            ];
        }

        // Add withCount validation
        if (! empty($withCountableRelations)) {
            $firstCountableRelation = $withCountableRelations[0];

            $datasets['with_count_invalid_type'] = [
                ['withCount' => ['array_instead_of_string']],
                ['withCount'],
            ];

            // Check rule AllowedQueryRelations with a valid relation and an invalid one
            $datasets['with_count_invalid_relation'] = [
                ['withCount' => $firstCountableRelation.',non_existent_relation'],
                ['withCount'],
            ];
        }

        // Add sort validation
        if (! empty($sortableFields)) {
            $firstSortableField = $sortableFields[0];

            $datasets['sort_invalid_type'] = [
                ['sort' => 12345],
                ['sort'],
            ];

            $datasets['sort_invalid_field'] = [
                ['sort' => $firstSortableField.',+non_existent_field'],
                ['sort'],
            ];
        }

        return $datasets;
    }
}
