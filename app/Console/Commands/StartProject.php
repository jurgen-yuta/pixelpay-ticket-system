<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class StartProject extends Command
{
    protected $signature = 'start';
    protected $description = 'Configura el proyecto (SQLite, migraciones) e inicia el servidor de desarrollo, abriendo la vista frontend.';

    public function handle()
    {
        $this->info('Iniciando configuración y despliegue del proyecto...');

        $envPath = base_path('.env');
        
        // 1. Manejo y Configuración de .env
        if (!File::exists($envPath)) {
            $this->warn('Creando archivo .env con configuracion SQLite...');
            File::copy(base_path('.env.example'), $envPath);
            
            // Generar clave antes de configurar DB (ya que se requiere)
            Artisan::call('key:generate', [], $this->output);
            
            // Reemplazar la configuración de MySQL por SQLite
            $envContent = File::get($envPath);
            $envContent = preg_replace('/DB_CONNECTION=mysql/', 'DB_CONNECTION=sqlite', $envContent);
            // Usamos la ruta absoluta del archivo SQLite
            $envContent = preg_replace('/^DB_DATABASE=.*$/m', 'DB_DATABASE=' . database_path('database.sqlite'), $envContent);
            
            // Comentar las líneas de MySQL que ya no se usan
            $envContent = preg_replace('/^DB_HOST=.*$/m', '# DB_HOST=127.0.0.1', $envContent);
            $envContent = preg_replace('/^DB_PORT=.*$/m', '# DB_PORT=3306', $envContent);
            $envContent = preg_replace('/^DB_USERNAME=.*$/m', '# DB_USERNAME=root', $envContent);
            $envContent = preg_replace('/^DB_PASSWORD=.*$/m', '# DB_PASSWORD=', $envContent);
            
            File::put($envPath, $envContent);
            $this->info('.env configurado para SQLite.');
        } else {
            $this->warn('.env ya existe. ¡Asegúrate de que DB_CONNECTION=sqlite!');
        }

        // --- CORRECCIÓN CRUCIAL: LIMPIAR CACHÉ ---
        // Esto obliga a Laravel a leer el .env modificado y usar la conexión SQLite.
        $this->info('Limpiando caché de configuración...');
        Artisan::call('config:clear', [], $this->output);
        
        // 2. Creación del archivo de base de datos SQLite
        $dbPath = database_path('database.sqlite');
        if (!File::exists($dbPath)) {
            $this->info('Creando archivo database.sqlite...');
            File::put($dbPath, '');
        }

        // 3. Ejecución de migraciones/seeders (Ahora con SQLite)
        $this->info('Ejecutando migraciones y seeders...');
        Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true], $this->output);
        $this->info('Configuración de base de datos finalizada.');

        // 4. Inicio del Servidor
        
        // ***********************************************
        // ABRIR EL NAVEGADOR EN /dashboard
        // ***********************************************
        $url = 'http://127.0.0.1:8000/dashboard'; // <--- LA NUEVA RUTA
        
        $this->info("Abriendo el frontend en: {$url}");
        $this->openBrowser($url);
        
        // Pausa breve para asegurar que el navegador tenga tiempo de lanzar la URL
        // antes de que la consola sea bloqueada por 'php artisan serve'.
        usleep(500000); // 0.5 segundos
        // ***********************************************

        $this->warn('Iniciando servidor de desarrollo en http://127.0.0.1:8000...');
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
            // Windows: usa 'start' (el 'start ""' es para manejar espacios en la URL/comando)
            exec('start "" "' . $url . '"');
        } elseif ($os === 'DAR') {
            // macOS: usa 'open'
            exec('open "' . $url . '"');
        } else {
            // Linux: usa 'xdg-open' y fallbacks comunes
            exec('xdg-open "' . $url . '" || google-chrome "' . $url . '" || firefox "' . $url . '"');
        }
    }
}