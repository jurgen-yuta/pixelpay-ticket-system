<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
        // Asegúrate de que no haya ninguna línea de User::factory()->create() aquí
        // o cualquier llamada a NivelSeeder::class.

        $this->call([
            // Solo llamamos al seeder del proyecto de Tickets
            \Database\Seeders\TicketSeeder::class,
        ]);
    }
}
