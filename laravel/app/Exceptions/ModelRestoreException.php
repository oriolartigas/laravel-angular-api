<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

class ModelRestoreException extends ModelOperationException
{
    protected string $operation = 'restore';

    protected string $defaultMessage = 'Error restoring model';

    public function __construct(string $modelName, Throwable $previous, int $code = 500)
    {
        parent::__construct(
            modelName: $modelName,
            code: $code,
            previous: $previous,
        );
    }
}
