<?php

declare(strict_types=1);

namespace Tests\Feature\Base\Validation;

use PHPUnit\Framework\Attributes\DataProvider;

abstract class BaseValidationShowTest extends BaseValidationTest
{
    /**
     * Defines the invalid data required to fail validation for show method
     *
     * By default, show endpoints use the same query parameter validation as index.
     * Override this method only if show has different validation requirements.
     *
     * @return array Array of test cases with invalid data for show validation
     */
    public static function showValidationDataSets(): array
    {
        return static::indexValidationDataSets();
    }

    /**
     * Show test that returns validation errors for invalid parameters
     *
     * This test validates that the show endpoint properly handles validation
     * scenarios and returns appropriate validation errors when invalid data
     * is provided. It ensures that show-specific validation rules are
     * properly enforced and error responses are correctly formatted.
     *
     * @param  array  $requestData  The request data containing invalid fields.
     * @param  array  $expectedFails  Array of fields expected to fail validation.
     */
    #[DataProvider('showValidationDataSets')]
    public function test_show_validation_returns_errors(array $requestData, array $expectedFails): void
    {
        $this->runValidationAssertion('GET', $requestData, $expectedFails);
    }
}
