<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

class ModelCreateException extends ModelOperationException
{
    protected string $operation = 'create';

    protected string $defaultMessage = 'Error creating model';

    public function __construct(string $modelName, Throwable $previous, int $code = 500)
    {
        parent::__construct(
            modelName: $modelName,
            code: $code,
            previous: $previous,
        );
    }
}
