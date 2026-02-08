<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Contracts\ModelOperationInterface;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

abstract class ModelOperationException extends HttpException implements ModelOperationInterface
{
    protected string $operation;

    protected string $defaultMessage;

    /**
     * @param  string  $message
     */
    public function __construct(protected string $modelName, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(
            statusCode: $code,
            message: $this->getMessageAndModel(),
            code: $code,
            previous: $previous,
        );
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getDefaultMessage(): string
    {
        return $this->defaultMessage;
    }

    public function getModelName(): string
    {
        return $this->modelName;
    }

    public function getMessageAndModel(): string
    {
        return "{$this->getDefaultMessage()} {$this->getModelName()}";
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        Log::error(
            message: $this->getMessage(),
            context: [
                'previous' => $this->getPrevious(),
            ],
        );
    }
}
