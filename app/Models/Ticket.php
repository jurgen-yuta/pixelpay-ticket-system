<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TicketStatus; // Importar el Enum

class Ticket extends Model
{
    use HasFactory;
    
    // Permitir la asignación masiva de los campos requeridos
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
    ];
    
    // Castear el campo status a un enum para una mejor tipificación
    protected $casts = [
        'status' => TicketStatus::class,
    ];
    
    // Define la relación: Un Ticket pertenece a un Usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
