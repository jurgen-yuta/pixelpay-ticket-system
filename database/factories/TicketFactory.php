<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // El status debe ser uno de los permitidos en tu migración
        $statuses = ['open', 'in_progress', 'closed'];

        return [
            // Asigna un user_id existente al azar
            'user_id' => User::factory(), 
            
            'title' => fake()->sentence(5), // Título de 5 palabras
            
            // Asigna un status al azar
            'status' => fake()->randomElement($statuses), 
        ];
    }
}
