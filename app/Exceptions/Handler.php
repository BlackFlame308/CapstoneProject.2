<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        AuthenticationException::class,
        ValidationException::class,
        ModelNotFoundException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
            // Custom reporting can be defined here.
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->renderJsonException($request, $e);
        }

        return parent::render($request, $e);
    }

    private function renderJsonException(Request $request, Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'status' => 'validation_error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'status' => 'unauthenticated',
                'message' => 'Authentication required',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Resource not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
        $message = $e->getMessage() ?: 'Server error';

        if ($status < 400 || $status >= 600) {
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $status);
    }
}
