<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class AppStartCommand extends Command
{
    /**
     * El nombre y la firma del comando de la consola.
     * El nombre debe ser 'start' para cumplir con el requisito 'php artisan start'.
     *
     * @var string
     */
    protected $signature = 'start';

    /**
     * La descripción de la consola del comando.
     *
     * @var string
     */
    protected $description = 'Instala la aplicación automáticamente y arranca el servidor de desarrollo.';

    /**
     * Ejecuta el comando de la consola.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("------------------------------------------------");
        $this->info("| INICIANDO INSTALACIÓN AUTOMÁTICA DE LARAVEL |");
        $this->info("------------------------------------------------");

        // 1. Crear .env y configurar SQLite
        if (!File::exists(base_path('.env'))) {
            $this->info("Creando archivo .env...");
            File::copy(base_path('.env.example'), base_path('.env'));
            
            // Configurar para usar SQLite
            $envPath = base_path('.env');
            $content = File::get($envPath);
            $content = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=sqlite', $content);
            $content = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=database/database.sqlite', $content);
            $content = preg_replace('/DB_HOST=.*/', 'DB_HOST=', $content);
            $content = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=', $content);
            $content = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=', $content);
            File::put($envPath, $content);
            $this->info("Configuración de base de datos SQLite lista.");
        }

        // 2. Generar archivo database.sqlite
        $sqlitePath = database_path('database.sqlite');
        if (!File::exists($sqlitePath)) {
            $this->info("Creando archivo database/database.sqlite...");
            File::put($sqlitePath, '');
        }

        // 3. Generar llave, migraciones y seeders
        $this->info("Ejecutando php artisan key:generate...");
        Artisan::call('key:generate', [], $this->output);
        
        // CORRECCIÓN CLAVE: Usar migrate:fresh para borrar y recrear la base de datos
        // y asegurar que el schema esté siempre sincronizado con sus migraciones.
        $this->info("Ejecutando php artisan migrate:fresh --seed...");
        Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true], $this->output);

        // 4. Compilar los assets de Vue (npm run build) antes de iniciar el servidor
        $this->info("Compilando assets de Vue (npm run build)...");
        // Ejecutamos 'npm run build' de forma síncrona y mostramos la salida.
        exec('npm run build', $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error("La compilación de assets con 'npm run build' falló. Por favor, ejecute 'npm run build' manualmente para ver el error.");
            return self::FAILURE;
        }
        $this->info("Compilación de assets completada con éxito. Manifiesto creado.");


        // 5. Iniciar el servidor de desarrollo
        $this->info("----------------------------------------------------");
        $this->info("APLICACIÓN LISTA: Iniciando servidor en el puerto 8000...");
        $this->warn("NOTA IMPORTANTE: El navegador puede mostrar un error inicial. Espere 5 segundos y RECARGUE MANUALMENTE la página.");
        $this->info("----------------------------------------------------");

        // Usamos 'serve' para iniciar el servidor de desarrollo.
        Artisan::call('serve', ['--host' => '127.0.0.1', '--port' => 8000], $this->output);

        return Command::SUCCESS;
    }
}