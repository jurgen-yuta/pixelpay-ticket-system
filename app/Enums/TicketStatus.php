<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Closed = 'closed';

    // Helper para la secuencia de actualización
    public function next(): self
    {
        return match($this) {
            self::Open => self::InProgress,
            self::InProgress => self::Closed,
            self::Closed => self::Closed, // Permanece cerrado
        };
    }
}