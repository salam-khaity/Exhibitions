<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
->withRouting(
web: __DIR__.'/../routes/web.php',
api: __DIR__.'/../routes/api.php',
commands: __DIR__.'/../routes/console.php',
health: '/up',)

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    $middleware->alias([
    'role' => RoleMiddleware::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

    $exceptions->render(function (AuthenticationException $e, $request) {
    return response()->json([
    'status' => 'error',
    'message' => 'Unauthenticated',
    ], 401);
    });

    $exceptions->render(function (ValidationException $e, $request) {
    return response()->json([
    'status' => 'error',
    'message' => $e->errors(),
    ], 422);
    });

    $exceptions->render(function (QueryException $e, $request) {
    return response()->json([
    'status' => 'error',
    'message' => 'Database error',
    'error' => $e->getMessage()
    ], 500);
    });

    $exceptions->render(function (ModelNotFoundException $e, $request) {
    return response()->json([
    'status' => 'error',
    'message' => 'Resource not found'
    ], 404);
    });

    $exceptions->render(function (NotFoundHttpException $e, $request) {
    return response()->json([
    'status' => 'error',
    'message' => 'Route not found'
    ], 404);
    });

    $exceptions->render(function (Throwable $e, $request) {
    return response()->json([
    'status' => 'error',
    'message' => 'Server Error',
    'error' => $e->getMessage()
    ], 500);
    });
    })->create();
