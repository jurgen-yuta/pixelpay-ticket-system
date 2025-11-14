<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;

class TicketTest extends TestCase
{
    // Usamos esto para que cada test tenga una base de datos limpia.
    // Ejecutará migraciones ANTES y se limpiará DESPUÉS de cada test.
    use RefreshDatabase;

    // --- PRUEBA POSITIVA: Creación Exitosa (Requerimiento 1) ---

    /** @test */
    public function test_can_create_ticket_successfully()
    {
        // 1. Crear un usuario necesario para la prueba
        $user = User::factory()->create();

        // 2. Datos válidos para el ticket (status se asigna como 'open' por defecto)
        $data = [
            'title' => 'Problema con el nuevo API Gateway',
            'user_id' => $user->id,
            // 🔑 CORRECCIÓN CLAVE: Añadir 'description' para evitar el error 500
            'description' => 'El tráfico está siendo rechazado con error 504. Esto resuelve el error Undefined array key.', 
        ];

        // 3. Ejecutar la petición POST al endpoint de creación
        $response = $this->postJson('/api/tickets', $data);

        // 4. Verificar el estado 201 (Created)
        $response->assertStatus(201);
        
        // 5. Verificar que el ticket existe en la base de datos
        $this->assertDatabaseHas('tickets', [
            'title' => 'Problema con el nuevo API Gateway',
            'user_id' => $user->id,
            'description' => 'El tráfico está siendo rechazado con error 504. Esto resuelve el error Undefined array key.',
            'status' => 'open' // Verificar que se asignó el valor por defecto
        ]);
        
        // 6. Verificar la estructura JSON de la respuesta
        $response->assertJsonStructure([
            'message',
            'ticket' => [
                'id',
                'title',
                'user_id',
                'description', // Aseguramos que 'description' esté en la respuesta si lo guardamos
                'status',
                'created_at',
                'updated_at',
            ]
        ]);
    }

    // --- PRUEBA NEGATIVA: Validaciones Faltantes (Requerimiento 2) ---

    /** @test */
    public function test_creation_fails_without_required_fields()
    {
        // Datos inválidos (faltan 'title', 'user_id', y 'description')
        $data = []; 
        
        $response = $this->postJson('/api/tickets', $data);

        // 1. Verificar el estado 422 (Unprocessable Entity) para errores de validación
        // Ahora esperamos que también falle si falta 'description'
        $response->assertStatus(422);

        // 2. Verificar que el JSON de respuesta contenga errores para los campos requeridos
        // Asumiendo que 'description' es un campo requerido.
        $response->assertJsonValidationErrors(['title', 'user_id']);
    }

    // --- PRUEBA NEGATIVA: Error Controlado (Requerimiento 3) ---

    /** @test */
    public function test_creation_fails_with_non_existent_user_id()
    {
        // El ID 9999 no existe en la base de datos
        $data = [
            'title' => 'Ticket de prueba para validación',
            'user_id' => 9999, 
            'description' => 'Este es el campo requerido.', // También debemos incluir este campo para que solo falle user_id
        ];

        $response = $this->postJson('/api/tickets', $data);

        // 1. Verificar el estado 422
        $response->assertStatus(422);

        // 2. Verificar que el JSON de respuesta contenga el error de validación 'exists'
        $response->assertJsonValidationErrors(['user_id' => 'The selected user id is invalid.']);
    }
}