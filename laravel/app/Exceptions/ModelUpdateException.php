<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

class ModelUpdateException extends ModelOperationException
{
    protected string $operation = 'update';

    protected string $defaultMessage = 'Error updating model';

    public function __construct(string $modelName, Throwable $previous, int $code = 405)
    {
        parent::__construct(
            modelName: $modelName,
            code: $code,
            previous: $previous,
        );
    }
}
