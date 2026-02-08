<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

class ModelDeleteException extends ModelOperationException
{
    protected string $operation = 'delete';

    protected string $defaultMessage = 'Error deleting model';

    public function __construct(string $modelName, Throwable $previous, int $code = 500)
    {
        parent::__construct(
            modelName: $modelName,
            code: $code,
            previous: $previous,
        );
    }
}
