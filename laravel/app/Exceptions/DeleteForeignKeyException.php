<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * This class DO NOT extend ModelOperationException because
 * it is NOT an error
 */
class DeleteForeignKeyException extends Exception
{
    public function __construct(string $modelName, ?Throwable $previous = null)
    {
        parent::__construct(
            message: 'This record cannot be deleted because it has related data.',
            code: 409,
            previous: $previous,
        );
    }
}
