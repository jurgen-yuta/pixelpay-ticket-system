<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    // Usar RefreshDatabase es esencial para Feature Tests. 
    // Asegura que una base de datos limpia (SQLite :memory:) se use para cada test.
    use RefreshDatabase; 

    /**
     * Prueba que el endpoint principal de la aplicación funciona correctamente.
     * Esta es una prueba de sanity check básica.
     *
     * @return void
     */
    public function test_example(): void
    {
        // Cambiamos la URL a una que sabemos que debe devolver una respuesta válida 
        // (Ejemplo: la ruta del índice de tickets, que probablemente existe)
        $response = $this->get('/api/tickets'); 

        // Verifica que el estado de respuesta HTTP es 200 (OK)
        // O si requiere autenticación, podría ser 401/403, pero 404 es incorrecto.
        // Si tu ruta '/api/tickets' no requiere autenticación y devuelve un 200 para la lista, esto funcionará.
        $response->assertStatus(200); 
    }
}