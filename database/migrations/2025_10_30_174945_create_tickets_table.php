<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            
            // Requerimiento: Relación con User (user_id) 
            // Asumiendo que usas la tabla 'users' predeterminada de Laravel
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Requerimiento: title (validar que sea requerido) 
            $table->string('title');
            
            // Requerimiento: status (por defecto 'open') 
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};