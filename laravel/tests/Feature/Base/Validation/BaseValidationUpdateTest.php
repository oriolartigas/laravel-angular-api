<?php

declare(strict_types=1);

namespace Tests\Feature\Base\Validation;

use PHPUnit\Framework\Attributes\DataProvider;

abstract class BaseValidationUpdateTest extends BaseValidationTest
{
    /**
     * Defines the invalid data required to fail validation for update method
     *
     * This method should return an array of test cases containing invalid data
     * that will cause validation failures when testing the update endpoint.
     * Each test case should include the invalid request data and the expected
     * validation error fields that should be returned when updating existing models.
     *
     * @return array Array of test cases with invalid data for update validation
     */
    abstract public static function updateValidationDataSets(): array;

    /**
     * Update test that returns validation errors for invalid data
     *
     * This test validates that the update endpoint properly rejects invalid
     * request data and returns appropriate validation errors. It tests
     * various scenarios including missing required fields, invalid formats,
     * and constraint violations to ensure robust input validation during
     * model update operations.
     *
     * @param  array  $requestData  The request data containing invalid fields.
     * @param  array  $expectedFails  Array of fields expected to fail validation.
     */
    #[DataProvider('updateValidationDataSets')]
    public function test_update_validation_returns_errors(array $requestData, array $expectedFails): void
    {
        $this->runValidationAssertion('PUT', $requestData, $expectedFails);
    }
}
