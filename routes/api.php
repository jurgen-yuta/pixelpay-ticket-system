<?php

use App\Http\Controllers\Api\TicketController; // ⬅️ DEBE ESTAR ESTA IMPORTACIÓN
use Illuminate\Support\Facades\Route;

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


// ENDPOINT PARA TICKETS:
// 1. POST /api/tickets (Creación)
Route::post('tickets', [TicketController::class, 'store']); 

// 2. GET /api/tickets/{id} (Detalle)
Route::get('tickets/{id}', [TicketController::class, 'show']);
