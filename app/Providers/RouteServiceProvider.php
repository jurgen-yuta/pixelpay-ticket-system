<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

// Importamos el middleware de Inertia
use App\Http\Middleware\HandleInertiaRequests; 

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define tu mapeo de rutas aquí.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        // Bloque que define las rutas web y API
        $this->routes(function () {
            
            // 1. RUTAS API (Sin middleware de Inertia)
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // 2. RUTAS WEB (CON middleware de Inertia)
            // Aquí aplicamos el middleware 'HandleInertiaRequests' al grupo 'web'
            Route::middleware(['web', HandleInertiaRequests::class]) // <<< CONFIGURACIÓN CLAVE
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configura los limitadores de tasa para la aplicación.
     *
     * @return void
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}