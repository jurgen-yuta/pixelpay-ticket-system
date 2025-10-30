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
        // 1. Crear 5 usuarios de prueba
        User::factory()
            ->count(5)
            // 2. Para cada usuario creado, crea 10 tickets asociados
            ->has(Ticket::factory()->count(10), 'tickets') 
            ->create();

        // 3. Opcional: Crear un usuario conocido para pruebas manuales o Postman
        User::factory()->create([
            'name' => 'QA Tester PixelPay',
            'email' => 'qa@pixelpay.com',
            'password' => bcrypt('password123'),
        ]);
    }
}