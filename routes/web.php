<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/

// Mantenemos solo la vista de bienvenida por defecto.
Route::get('/', function () {
    return view('welcome');
});

// Todas las rutas del API están en routes/api.php