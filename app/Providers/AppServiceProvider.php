<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// Importa el middleware de Inertia
use App\Http\Middleware\HandleInertiaRequests; 
// Importa la clase Router para registrar el middleware
use Illuminate\Routing\Router;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Router $router): void
    {
        // 1. REGISTRAR EL MIDDLEWARE DE INERTIA AQUÍ
        // Esto asegura que Inertia maneje las peticiones web, reemplazando la función del Kernel
        $router->middlewareGroup('web', [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // 2. Ejecutar la generación del Middleware si no lo hiciste
        // Nota: Debes ejecutar esto en la terminal si no lo has hecho: php artisan inertia:middleware
    }
}
