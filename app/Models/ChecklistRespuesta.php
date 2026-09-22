<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistRespuesta extends Model
{
    public $timestamps = false;

    protected $table = 'checklist_respuestas';

    protected $fillable = [
        'inspeccion_id',
        'item_id',
        'valor',
    ];

    // 🔗 Relaciones

    public function inspeccion()
    {
        return $this->belongsTo(ChecklistInspeccion::class, 'inspeccion_id');
    }

    public function item()
    {
        return $this->belongsTo(ChecklistItem::class, 'item_id');
    }

    // 🛠 Helpers

    /**
     * Determina si la respuesta representa un problema.
     */
    public function esProblema(): bool
    {
        return in_array($this->valor, ['malo', 'regular', 'vencido', '1/4']);
    }

    /**
     * Determina la severidad del problema según el valor y si el ítem es crítico.
     */
    public function calcularSeveridad(): ?string
    {
        if (!$this->esProblema()) {
            return null;
        }

        $esCritico = $this->item->es_critico;

        return match (true) {
            $this->valor === 'malo' && $esCritico   => 'critico',
            $this->valor === 'malo'                  => 'atencion',
            $this->valor === 'regular'               => 'atencion',
            $this->valor === 'vencido'               => 'info',
            $this->valor === '1/4' && $esCritico     => 'atencion',
            $this->valor === '1/4'                   => 'info',
            default                                  => null,
        };
    }
}