<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // ⬅️ DEBE ESTAR IMPORTADO AQUÍ
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory; // ⬅️ DEBE ESTAR USADO AQUÍ
    
    // Permitir la asignación masiva de los campos requeridos
    protected $fillable = [
        'user_id',
        'title',
        'status',
    ];

    // Define la relación: Un Ticket pertenece a un Usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
