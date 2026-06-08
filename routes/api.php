<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas de autenticación (con prefijo api/auth/)
Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/profile', [AuthController::class, 'profile']);
    });
});

// Rutas para la base de datos principal (sin prefijo auth, protegidas por auth:api)
Route::middleware('auth:api')->group(function () {
    Route::resource('/empresas', \App\Http\Controllers\Api\EmpresaController::class);
    Route::resource('/usuarios', \App\Http\Controllers\Api\UserController::class);
    Route::resource('/empresausuarios', \App\Http\Controllers\Api\EmpresaUsuarioController::class);
});

// Rutas multi-tenant para empresas (sin prefijo auth, protegidas por auth:api y tenant.db)
Route::middleware(['auth:api', 'tenant.db'])->group(function () {
    Route::resource('/cuentas', \App\Http\Controllers\Api\CuentaController::class);
    Route::resource('/categorias', \App\Http\Controllers\Api\CategoriaController::class);
    Route::resource('/insumos', \App\Http\Controllers\Api\InsumoController::class);
    Route::resource('/ingresos', \App\Http\Controllers\Api\IngresoController::class);
    Route::resource('/detalleingresos', \App\Http\Controllers\Api\DetalleIngresoController::class);
    Route::resource('/cotizaciones', \App\Http\Controllers\Api\CotizacionController::class);
    Route::resource('/productos', \App\Http\Controllers\Api\ProductoController::class);
    Route::resource('/productoinsumos', \App\Http\Controllers\Api\ProductoInsumoController::class);

    Route::get('/saldo-real-detalle-ingreso', [\App\Http\Controllers\Api\DetalleIngresoController::class, 'saldoRealDetalleIngreso']);
    Route::get('/obtener-cotizacion-por-id/{id}', [\App\Http\Controllers\Api\CotizacionController::class, 'obtenerCotizacionPorId']);

    Route::prefix('apis')->group(function () {
        Route::get('/resumen', [\App\Http\Controllers\Api\ApisController::class, 'resumen']);
        Route::get('/inventario', [\App\Http\Controllers\Api\ApisController::class, 'inventario']);
        Route::get('/compras', [\App\Http\Controllers\Api\ApisController::class, 'compras']);
        Route::get('/cotizaciones', [\App\Http\Controllers\Api\ApisController::class, 'cotizaciones']);
        Route::get('/produccion', [\App\Http\Controllers\Api\ApisController::class, 'produccion']);
        Route::get('/graficos', [\App\Http\Controllers\Api\ApisController::class, 'graficos']);
        Route::get('/alertas', [\App\Http\Controllers\Api\ApisController::class, 'alertas']);
    });
});
