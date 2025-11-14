<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class AppStartCommand extends Command
{
    protected $signature = 'start';
    protected $description = 'Instala la aplicación automáticamente y arranca el servidor de desarrollo.';

    public function handle()
    {
        $this->info("------------------------------------------------");
        $this->info("| INICIANDO INSTALACIÓN AUTOMÁTICA DE LARAVEL |");
        $this->info("------------------------------------------------");
        
        // Limpieza de caché inicial para asegurar que la nueva ruta sea leída.
        $this->info("Limpiando configuración antigua...");
        Artisan::call('config:clear', [], $this->output);
        
        // 1. Crear .env y configurar SQLite
        if (!File::exists(base_path('.env'))) {
            $this->info("Creando archivo .env...");
            File::copy(base_path('.env.example'), base_path('.env'));
            
            // Configurar para usar SQLite y sistema de archivos (file/sync)
            $envPath = base_path('.env');
            $content = File::get($envPath);
            
            // Configuración de BD principal a SQLite
            $content = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=sqlite', $content);
            $content = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=database/database.sqlite', $content);
            $content = preg_replace('/DB_HOST=.*/', 'DB_HOST=', $content);
            $content = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=', $content);
            $content = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=', $content);
            
            // 🔑 CORRECCIÓN CLAVE: EVITAR CONEXIÓN FALLIDA A MYSQL EN SERVICIOS
            // Forzar servicios secundarios a usar el sistema de archivos (file/sync).
            $content = preg_replace('/SESSION_DRIVER=.*/', 'SESSION_DRIVER=file', $content);
            $content = preg_replace('/QUEUE_CONNECTION=.*/', 'QUEUE_CONNECTION=sync', $content);
            $content = preg_replace('/CACHE_STORE=.*/', 'CACHE_STORE=file', $content);
            
            File::put($envPath, $content);
            $this->info("Configuración de base de datos SQLite y servicios secundarios lista.");
            
            // Limpieza de caché después de escribir el nuevo .env.
            $this->info("Recargando la nueva configuración de SQLite y servicios...");
            Artisan::call('config:clear', [], $this->output);
        }

        // 2. Generar archivo database.sqlite
        $sqlitePath = database_path('database.sqlite');
        if (!File::exists($sqlitePath)) {
            $this->info("Creando archivo database/database.sqlite...");
            File::put($sqlitePath, '');
        }

        // CORRECCIÓN DEFINITIVA: FORZAR RUTA EN TIEMPO DE EJECUCIÓN
        $this->info("Forzando la ruta de la base de datos a la ubicación actual...");
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);

        // 3. Generar llave, migraciones y seeders
        $this->info("Ejecutando php artisan key:generate...");
        Artisan::call('key:generate', [], $this->output);
        
        // Eliminé la llamada duplicada a migrate:fresh
        $this->info("Ejecutando php artisan migrate:fresh --seed...");
        Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true], $this->output);
        
        // 4. Compilar los assets de Vue (npm run build)
        $this->info("Compilando assets de Vue (npm run build)...");
        exec('npm run build', $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error("La compilación de assets con 'npm run build' falló. Por favor, ejecute 'npm run build' manualmente para ver el error.");
            return self::FAILURE;
        }
        $this->info("Compilación de assets completada con éxito. Manifiesto creado.");


        // 5. Iniciar el servidor de desarrollo
        
        $url = 'http://127.0.0.1:8000/dashboard';

        $this->info("----------------------------------------------------");
        $this->info("APLICACIÓN LISTA: Abriendo {$url} e iniciando servidor...");
        $this->info("----------------------------------------------------");

        // Llamar a la función para abrir el navegador
        $this->openBrowser($url);

        // Pausa breve antes de que la consola sea bloqueada por 'php artisan serve'.
        usleep(500000); // 0.5 segundos

        $this->warn('Iniciando servidor de desarrollo. Presione Ctrl+C para detener.');
        passthru('php artisan serve');
        
        return Command::SUCCESS;
    }

    /**
     * Abre la URL especificada en el navegador predeterminado del sistema.
     * @param string $url La URL a abrir.
     */
    private function openBrowser(string $url): void
    {
        $os = strtoupper(substr(PHP_OS, 0, 3));

        if ($os === 'WIN') {
            exec('start "" "' . $url . '"');
        } elseif ($os === 'DAR') {
            exec('open "' . $url . '"');
        } else {
            exec('xdg-open "' . $url . '" || google-chrome "' . $url . '" || firefox "' . $url . '"');
        }
    }
}