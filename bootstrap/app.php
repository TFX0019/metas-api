<?php

use App\Http\Controllers\Mileddware\CheckRole;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            // Solo convertir a JSON si es una petición a la API
            if ($request->is('api/*')) {
                $response = [
                    'ok' => false,
                    'message' => 'Ha ocurrido un error',
                    'error' => null,
                    'data' => null,
                ];

                // Validación de errores
                if ($e instanceof ValidationException) {
                    $response['message'] = 'Error de validación';
                    $response['error'] = $e->errors();
                    return response()->json($response, 422);
                }

                // Error de autenticación
                if ($e instanceof AuthenticationException) {
                    $response['message'] = 'No autenticado';
                    $response['error'] = 'Token inválido o no proporcionado';
                    return response()->json($response, 401);
                }

                // Modelo no encontrado
                if ($e instanceof ModelNotFoundException) {
                    $response['message'] = 'Recurso no encontrado';
                    $response['error'] = 'El recurso solicitado no existe';
                    return response()->json($response, 404);
                }

                // Ruta no encontrada
                if ($e instanceof NotFoundHttpException) {
                    $response['message'] = 'Endpoint no encontrado';
                    $response['error'] = 'La ruta solicitada no existe';
                    return response()->json($response, 404);
                }

                // Método no permitido
                if ($e instanceof MethodNotAllowedHttpException) {
                    $response['message'] = 'Método no permitido';
                    $response['error'] = 'El método HTTP utilizado no está permitido para esta ruta';
                    return response()->json($response, 405);
                }

                // Error genérico
                $response['error'] = $e->getMessage();
                return response()->json($response, 500);
            }

            // Para peticiones web normales, usar el renderizado por defecto
            return parent::render($request, $e);
        });
    })->create();
