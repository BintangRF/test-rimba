<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

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
    public function register()
    {
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated',
                ], 401);
            }
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Route not found',
                ], 404);
            }
        });

        $this->renderable(function (HttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'HTTP Error',
                ], $e->getStatusCode());
            }
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Something went wrong.',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }
        });
    }

    public function render($request, Throwable $exception)
    {
        // Paksa semua request ke route /api/* dianggap JSON
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
        }

        return parent::render($request, $exception);
    }
}
