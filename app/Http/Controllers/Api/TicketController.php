<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Enums\TicketStatus; // Importar el Enum
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validaciones
        $validatedData = $request->validate([
            'title' => 'required|string|max:255', 
            'description' => 'nullable|string',
            // FIX: Forzamos la regla 'exists' a usar la conexión 'sqlite'
            // Sintaxis: exists:nombre_conexion.nombre_tabla,nombre_columna
            'user_id' => 'required|exists:sqlite.users,id', 
        ]);

        // 2. Creación del Ticket (usando el Enum)
        $ticket = Ticket::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'user_id' => $validatedData['user_id'],
            'status' => TicketStatus::Open, 
        ]);

        // 3. Respuesta JSON (201 Created)
        return response()->json([
            'message' => 'Ticket creado exitosamente.',
            'ticket' => $ticket
        ], 201);
    }

    public function updateStatus(Ticket $ticket)
    {
        // 1. Usa el método next() del Enum
        $ticket->status = $ticket->status->next(); 
        $ticket->save();

        return response()->json(['message' => 'Estado actualizado', 'ticket' => $ticket->fresh()]);
    }

    public function show($id)
    {
        // Retorna el ticket con los datos del usuario
        $ticket = Ticket::with('user')->find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado'], 404);
        }

        return response()->json($ticket);
    }

    /**
     * Devuelve la lista de tickets aplicando filtros, ordenación y paginación.
     */
    public function index(Request $request)
    {
        // --- 1. Obtener Parámetros de Ordenación y Paginación ---
        
        // Define la columna de ordenación (por defecto: created_at)
        $sortColumn = $request->sort_column ?? 'created_at';
        // Define la dirección de ordenación (por defecto: descendente)
        $sortDirection = $request->sort_direction ?? 'desc';
        // Define cuántos tickets por página (por defecto: 10)
        $perPage = $request->per_page ?? 10; 
        
        // Columna de seguridad: Asegurar que solo se ordenan columnas válidas
        // Esto evita inyecciones SQL si se usa un parámetro arbitrario
        if (! in_array($sortColumn, ['id', 'title', 'status', 'created_at'])) {
            $sortColumn = 'created_at';
        }
        
        $tickets = Ticket::with('user');

        // --- 2. Aplicar Filtros (Search y Status) ---

        // Aplica filtro de BÚSQUEDA si el parámetro 'search' existe y no está vacío
        if ($request->filled('search')) {
            $search = $request->search;
            $tickets->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Aplica filtro de ESTADO si el parámetro 'status' existe y no está vacío
        if ($request->filled('status')) {
            $tickets->where('status', $request->status);
        }

        // --- 3. Aplicar Ordenación y Paginación ---

        $tickets = $tickets->orderBy($sortColumn, $sortDirection)
                           ->paginate($perPage); // CAMBIO CLAVE: Usamos paginate()

        // Devuelve el objeto de paginación completo (incluye links, data, total, etc.)
        return response()->json($tickets); 
    }
}