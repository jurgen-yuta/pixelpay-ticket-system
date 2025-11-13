<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController; // Importar el nuevo controlador

// La ruta principal ahora usa el controlador para manejar filtros y paginación.
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');