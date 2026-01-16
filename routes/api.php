<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LecturaController;
use App\Http\Controllers\MetaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\TareaController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    //* resource para los endpoints de metas
    Route::apiResource('metas', MetaController::class);

    //* resource para los endpoints de tareas dentro de una meta
    Route::get('tareas', [TareaController::class, 'allTareas']);
    Route::apiResource('metas.tareas', TareaController::class);

    //* funciones de perfil
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('/name', [ProfileController::class, 'updateName']);
        Route::put('/email', [ProfileController::class, 'updateEmail']);
        Route::put('/password', [ProfileController::class, 'updatePassword']);
        Route::post('/avatar', [ProfileController::class, 'updateAvatar']);
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar']);
    });

    //* lecturas para usuarios autenticados
    Route::get('lecturas', [LecturaController::class, 'index']);
    Route::get('lecturas/{lectura}', [LecturaController::class, 'show']);
    Route::post('lecturas/{lectura}/marcar-leida', [LecturaController::class, 'marcarLeida']);
    Route::get('mis-lecturas', [LecturaController::class, 'misLecturas']);

    //* funciones de rol (requiere permisos de administrador)
    Route::middleware('role:administrador')->group(function () {
        Route::get('roles', [RolController::class, 'index']);
        Route::post('roles', [RolController::class, 'store']);
        Route::post('users/{user}/roles/asignar', [RolController::class, 'asignarRol']);
        Route::post('users/{user}/roles/remover', [RolController::class, 'removerRol']);
        
        // CRUD completo de lecturas (solo administradores)
        Route::post('lecturas', [LecturaController::class, 'store']);
        Route::put('lecturas/{lectura}', [LecturaController::class, 'update']);
        Route::delete('lecturas/{lectura}', [LecturaController::class, 'destroy']);
    });

});

?>