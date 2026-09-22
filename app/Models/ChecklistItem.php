<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $table = 'checklist_items';

    protected $fillable = [
        'seccion_id',
        'nombre',
        'es_critico',
        'orden',
        'activo',
    ];

    protected $casts = [
        'es_critico' => 'boolean',
        'activo'     => 'boolean',
        'orden'      => 'integer',
    ];

    // 🔗 Relaciones

    public function seccion()
    {
        return $this->belongsTo(ChecklistSeccion::class, 'seccion_id');
    }

    public function respuestas()
    {
        return $this->hasMany(ChecklistRespuesta::class, 'item_id');
    }

    public function hallazgos()
    {
        return $this->hasMany(ChecklistHallazgo::class, 'item_id');
    }

    // 🔍 Scopes

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeCriticos($query)
    {
        return $query->where('es_critico', true);
    }
}