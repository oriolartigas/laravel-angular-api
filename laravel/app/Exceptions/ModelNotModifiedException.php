<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Exception thrown when a model update operation results in no actual changes.
 *
 * This class does NOT extend ModelOperationException because
 * it represents a successful operation with no modifications rather than an error.
 */
class ModelNotModifiedException extends HttpException
{
    public function __construct(string $modelName, array $attemptedData = [], ?int $recordId = null)
    {
        $message = "Model {$modelName} was not modified.";

        if ($recordId !== null) {
            $message .= " Record ID: {$recordId}.";
        }

        if (! empty($attemptedData)) {
            $fields = implode(', ', array_keys($attemptedData));
            $message .= " Attempted fields: {$fields}.";
        }

        parent::__construct(
            statusCode: 400,
            message: $message,
            code: 400,
        );
    }
}
