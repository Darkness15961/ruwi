<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/profile', [AuthController::class, 'profile']);
        
        Route::middleware('tenant.db')->group(function () {
            Route::resource('/cuentas', \App\Http\Controllers\Api\CuentaController::class);
            Route::resource('/categorias', \App\Http\Controllers\Api\CategoriaController::class);
            Route::resource('/insumos', \App\Http\Controllers\Api\InsumoController::class);
            Route::resource('/ingresos', \App\Http\Controllers\Api\IngresoController::class);
            Route::resource('/detalleingresos', \App\Http\Controllers\Api\DetalleIngresoController::class);
            Route::resource('/empresas', \App\Http\Controllers\Api\EmpresaController::class);
            Route::resource('/usuarios', \App\Http\Controllers\Api\UserController::class);
            Route::resource('/empresausuarios', \App\Http\Controllers\Api\EmpresaUsuarioController::class);
        });
    });
});
