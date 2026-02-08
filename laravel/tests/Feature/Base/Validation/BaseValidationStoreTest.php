<?php

declare(strict_types=1);

namespace Tests\Feature\Base\Validation;

use PHPUnit\Framework\Attributes\DataProvider;

abstract class BaseValidationStoreTest extends BaseValidationTest
{
    /**
     * Defines the invalid data required to fail validation for store method
     *
     * This method should return an array of test cases containing invalid data
     * that will cause validation failures when testing the store endpoint.
     * Each test case should include the invalid request data and the expected
     * validation error fields that should be returned when creating new models.
     *
     * @return array Array of test cases with invalid data for store validation
     */
    abstract public static function storeValidationDataSets(): array;

    /**
     * Store test that returns validation errors for invalid data
     *
     * This test validates that the store endpoint properly rejects invalid
     * request data and returns appropriate validation errors. It tests
     * various scenarios including missing required fields, invalid formats,
     * and constraint violations to ensure robust input validation during
     * model creation operations.
     *
     * @param  array  $requestData  The request data containing invalid fields.
     * @param  array  $expectedFails  Array of fields expected to fail validation.
     */
    #[DataProvider('storeValidationDataSets')]
    public function test_store_validation_returns_errors(array $requestData, array $expectedFails): void
    {
        $this->runValidationAssertion('POST', $requestData, $expectedFails);
    }
}
