<?php

namespace App\Mail;

use App\Models\ChecklistInspeccion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ChecklistHallazgosMail extends Mailable
{
    use Queueable, SerializesModels;

    public ChecklistInspeccion $inspeccion;
    public Collection $criticos;
    public Collection $atencion;
    public Collection $info;
    public int $totalHallazgos;

    public function __construct(
        ChecklistInspeccion $inspeccion,
        Collection $criticos,
        Collection $atencion,
        Collection $info
    ) {
        $this->inspeccion = $inspeccion;
        $this->criticos = $criticos;
        $this->atencion = $atencion;
        $this->info = $info;
        $this->totalHallazgos = $criticos->count() + $atencion->count() + $info->count();
    }

    public function envelope(): Envelope
    {
        $unidad = $this->inspeccion->unidad->nombre;
        $critCount = $this->criticos->count();

        $subject = $critCount > 0
            ? "⚠️ ALERTA: {$critCount} hallazgo(s) crítico(s) en {$unidad}"
            : "Checklist {$unidad} — {$this->totalHallazgos} hallazgo(s) detectado(s)";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.checklist-hallazgos',
        );
    }
}