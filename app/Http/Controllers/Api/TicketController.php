<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validaciones requeridas
        $request->validate([
            'title' => 'required|string|max:255', 
            'user_id' => 'required|exists:users,id', 
        ]);

        // 2. Creación del Ticket
        $ticket = Ticket::create([
            'title' => $request->title,
            'user_id' => $request->user_id,
            'status' => 'open', // Asignar 'status' por defecto como 'open'.
        ]);

        // 3. Respuesta JSON (201 Created)
        return response()->json([
            'message' => 'Ticket creado exitosamente.',
            'ticket' => $ticket
        ], 201);
    }

    public function show($id)
    {
        // Retorna el ticket con los datos del usuario (Eager Loading: with('user'))
        $ticket = Ticket::with('user')->find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket no encontrado'], 404);
        }

        return response()->json($ticket);
    }
}
