<?php

namespace App\Http\Controllers; // ¡Asegúrate de que no está en el namespace 'Api'!

use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia; // ¡Necesitas Inertia para renderizar la vista!
use App\Models\User;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        

        $tickets = Ticket::with('user') // Inicia la consulta aquí
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // 4. Devolver la respuesta Inertia con los tickets paginados
        return Inertia::render('Dashboard', [
            'tickets' => $tickets,
            'users' => User::all(),
        ]);
    }
}