<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @return void
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Summary of render
     *
     * @param  mixed  $request
     */
    public function render($request, Throwable $exception): JsonResponse|Response
    {
        if ($request->expectsJson()) {
            if ($exception instanceof ModelNotFoundException) {
                return $this->createJsonErrorResponse(message: 'Resource not found.', status: 404);
            }

            if ($exception instanceof NotFoundHttpException) {
                return $this->createJsonErrorResponse(message: 'Endpoint not found.', status: 404);
            }

            if ($exception instanceof ModelNotModifiedException
                || $exception instanceof ModelCreateException
                || $exception instanceof ModelUpdateException
                || $exception instanceof ModelDeleteException
                || $exception instanceof ModelRestoreException
                || $exception instanceof DeleteForeignKeyException) {

                return $this->createJsonErrorResponse(
                    message: $exception->getMessage(),
                    status: $exception->getCode()
                );
            }
        }

        return parent::render(request: $request, e: $exception);
    }

    /**
     * Create JSON error response
     *
     * @param  string  $message  The error message
     * @param  int  $status  The HTTP status code
     */
    private function createJsonErrorResponse(string $message, int $status): JsonResponse
    {
        return response()->json(data: ['message' => $message], status: $status);
    }
}
