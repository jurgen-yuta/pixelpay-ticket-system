<?php

use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('tickets', [TicketController::class, 'store']);
Route::get('tickets', [TicketController::class, 'index']); // <<< LISTADO
Route::put('tickets/{ticket}/status', [TicketController::class, 'updateStatus']); // <<< ACTUALIZAR ESTADO
Route::get('tickets/{ticket}', [TicketController::class, 'show']);
