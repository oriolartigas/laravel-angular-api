<?php

declare(strict_types=1);

namespace Tests\Feature\Base\Validation;

use PHPUnit\Framework\Attributes\DataProvider;

abstract class BaseValidationIndexTest extends BaseValidationTest
{
    /**
     * Index test that returns validation errors for invalid query parameters
     *
     * This test validates that the index endpoint properly rejects invalid
     * query parameters and returns appropriate validation errors. It tests
     * various scenarios including invalid where clauses, with relations,
     * withCount relations, and sort parameters to ensure robust input validation.
     *
     * @param  array  $requestData  The request data containing invalid fields.
     * @param  array  $expectedFails  Array of fields expected to fail validation.
     */
    #[DataProvider('indexValidationDataSets')]
    public function test_index_validation_returns_errors(array $requestData, array $expectedFails): void
    {
        $this->runValidationAssertion('GET', $requestData, $expectedFails);
    }

    /**
     * Test that returns validation error when mandatory fields are missing
     *
     * This test specifically validates that the index endpoint enforces mandatory
     * whereable fields when they are defined in the model configuration. It ensures
     * that requests without required filtering parameters are properly rejected
     * with appropriate validation errors, maintaining data access control.
     */
    public function test_index_validation_returns_error_for_missing_mandatory_fields(): void
    {
        $configurator = $this->configurator;

        $modelClass = $configurator->getModelClass();
        $modelInstance = $configurator->getModelInstance();

        $mandatoryFields = collect($modelInstance->getMandatoryWhereable());

        if ($mandatoryFields->isEmpty()) {
            $this->markTestSkipped('The model '.class_basename($modelClass).' does not have mandatory fields to test.');
        }

        $requestData = ['where' => ['non_existent_field' => 'non_existent_field_value']];

        $expectedFails = ['mandatory_fields'];

        $this->runValidationAssertion('GET', $requestData, $expectedFails);
    }
}
