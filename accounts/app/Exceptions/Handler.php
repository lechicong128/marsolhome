<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        $status = 500;
        $message = config('app.debug') ? $exception->getMessage() : 'Internal Server Error';
        if (config('app.debug')) {
            $message = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'exception' => get_class($exception),
            ];
        }
        if ($exception instanceof ValidationException) {
            $status = 422;
            $message = $exception->errors();
        } elseif ($exception instanceof ModelNotFoundException) {
            $status = 404;
            $message = 'Resource not found';
        } elseif ($exception instanceof HttpException) {
            $status = $exception->getStatusCode();
            $message = $exception->getMessage() ?: (Response::$statusTexts[$status] ?? 'Http Error');
        } elseif ($exception instanceof ErrorException) {
            $message = $exception->errors();
            $status = $exception->getStatusCode();
        }

        return response()->json([
            'error' => [
                'message' => $message,
                'status' => $status,
            ],
            'status' => $status,
        ], $status);
    }
}
