<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistSeccion extends Model
{
    protected $table = 'checklist_secciones';

    protected $fillable = [
        'plantilla_id',
        'nombre',
        'descripcion',
        'tipo_respuesta',
        'orden',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'orden'  => 'integer',
    ];

    // 🔗 Relaciones

    public function plantilla()
    {
        return $this->belongsTo(ChecklistPlantilla::class, 'plantilla_id');
    }

    public function items()
    {
        return $this->hasMany(ChecklistItem::class, 'seccion_id')->orderBy('orden');
    }

    public function itemsActivos()
    {
        return $this->hasMany(ChecklistItem::class, 'seccion_id')
                    ->where('activo', true)
                    ->orderBy('orden');
    }

    // 🔍 Scopes

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    // 🛠 Helpers

    /**
     * Opciones de respuesta según el tipo de la sección.
     */
    public function getOpcionesRespuesta(): array
    {
        return match ($this->tipo_respuesta) {
            'nivel'     => ['1/4', '1/2', '3/4', 'full', 'no_aplica'],
            'estado'    => ['bueno', 'regular', 'malo', 'no_aplica'],
            'documento' => ['ok', 'vencido'],
            default     => [],
        };
    }
}