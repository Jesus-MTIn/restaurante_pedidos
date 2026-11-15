<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\PedidoController;

Route::prefix('v1')->group(function () {
    Route::post('pedidos', [PedidoController::class, 'store']);
    Route::get('pedidos/{id}', [PedidoController::class, 'show']);
    Route::patch('pedidos/{id}/estado', [PedidoController::class, 'actualizarEstado']);
});
