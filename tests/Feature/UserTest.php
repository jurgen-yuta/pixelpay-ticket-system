<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Alumno;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    //use RefreshDatabase;

    public function test_get_alumnos_list(): void
    {
        // Llama a la nueva ruta API que usa apiIndex()
        $response = $this->get('/api/alumnos');
        // Comprobacion de ruta correcta
        $response->assertStatus(200);
        // Comprobacion de estructura de datos correcta
        $response->assertJsonStructure([
            '*' => [
                'id',
                'matricula',
                'nombre',
                'fecha_nacimiento',
                'telefono',
                'email',
                // El objeto de relación "nivel" anidado
                'nivel' => [ 'id', 'nombre', 'created_at', 'updated_at' ]
            ]
        ]);
        // Devolucion de datos correctos dentro de la DB
        $response->assertJsonFragment(['nombre' => 'juan perez']);
        // Comprobar que traiga todos los Usuarios o contenido de la DB
        $response->assertJsonCount(4);
    }

    // Comprobacion de detalles en base de datos
    public function test_get_alumnos_detail()
    {

        // Obtener el primer alumno disponible en la BD de prueba
        // NO usamos factory. Buscamos el primer registro existente.
        $alumno = Alumno::first();

        // Opcional: Si sabes que siempre habrá un ID=1
        // $alumno = Alumno::find(1); 

        // Si la BD está vacía, $alumno será null y fallará (es necesario que ingreses datos)
        if (is_null($alumno)) {
            $this->markTestSkipped('No hay datos de alumnos ingresados manualmente para probar el detalle.');
        }


        // Comprobacion de ruta o estado es correcta
        //$response->assertStatus(200);

        // Llamado de ruta
        $response = $this->get('/api/alumnos/' . $alumno->id);
        // Comprobacion de ruta o estado es correcta
        $response->assertStatus(200);
        // Comprobacion de estructura de datos correcta de un usuario en especifico
        $response->assertJsonStructure([
            'id',
            'matricula',
            'nombre',
            'fecha_nacimiento',
            'telefono',
            'email',
            'nivel_id',           // <- El campo de la clave foránea del alumno
            'created_at',
            'updated_at',
            
            // El objeto de relación "nivel" que se carga con 'with()'
            'nivel' => [ 
                'id', 
                'nombre', 
                'created_at', 
                'updated_at' 
            ]
            ]);
        // Devolucion de datos correctos dentro de la DB
        $response->assertJsonFragment(['nombre' => 'juan perez']);
    }

    // Comprobacion de datos erroneos o inexistentes
    public function test_get_non_existing_alumnos_detail()
    {
        // Llamado de ruta
        $response = $this->get('/api/alumnos/559');
        // Comprobacion de la ruta o estado sea correcta
        $response->assertStatus(404);
    }
}
