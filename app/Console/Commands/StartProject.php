<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class StartProject extends Command
{
    protected $signature = 'start';
    protected $description = 'Configura el proyecto (SQLite, migraciones) e inicia el servidor de desarrollo.';

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
        Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true], $this->output); // <<< CORRECCIÓN APLICADA AQUÍ LA LOGICAla PARA CREAE EL ARCHIVO SQLite SI NO EXISTE
        $this->info('Configuración de base de datos finalizada.');

        // 4. Inicio del Servidor
        $this->warn('Iniciando servidor de desarrollo en http://127.0.0.1:8000...');
        passthru('php artisan serve');
        
        return Command::SUCCESS;
    }
}