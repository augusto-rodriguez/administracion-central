<?php

namespace App\Mail;

use App\Models\ChecklistHallazgo;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HallazgoAsignadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public ChecklistHallazgo $hallazgo;
    public User $asignadoPor;

    public function __construct(ChecklistHallazgo $hallazgo, User $asignadoPor)
    {
        $this->hallazgo = $hallazgo;
        $this->asignadoPor = $asignadoPor;
    }

    public function envelope(): Envelope
    {
        $unidad = $this->hallazgo->inspeccion->unidad->nombre;
        $item = $this->hallazgo->item->nombre;
        $severidad = strtoupper($this->hallazgo->severidad);

        return new Envelope(
            subject: "📋 Hallazgo asignado: {$item} — {$unidad} [{$severidad}]"
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.hallazgo-asignado',
        );
    }
}