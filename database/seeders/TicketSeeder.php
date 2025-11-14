<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear 5 usuarios de prueba y 10 tickets para cada uno (50 tickets total)
        User::factory()
            ->count(5)
            // 2. Para cada usuario creado, crea 10 tickets asociados
            ->has(Ticket::factory()->count(10), 'tickets') 
            ->create();

        // 3. Opcional: Crear/Obtener un usuario conocido para pruebas manuales o Postman
        // *** CAMBIO CLAVE: Usar firstOrCreate() para evitar duplicados ***
        User::firstOrCreate(
            // Criterio de búsqueda (si existe este email, no lo crea)
            ['email' => 'qa@pixelpay.com'], 
            // Atributos de creación (si no existe)
            [
                'name' => 'QA Tester PixelPay',
                'password' => bcrypt('password123'),
            ]
        );
    }
}