<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistPlantilla extends Model
{
    protected $table = 'checklist_plantillas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo_unidad',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    // 🔗 Relaciones

    public function secciones()
    {
        return $this->hasMany(ChecklistSeccion::class, 'plantilla_id')->orderBy('orden');
    }

    public function inspecciones()
    {
        return $this->hasMany(ChecklistInspeccion::class, 'plantilla_id');
    }

    // 🔍 Scopes

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopePorTipoUnidad($query, ?string $tipo)
    {
        return $query->where(function ($q) use ($tipo) {
            $q->whereNull('tipo_unidad')
              ->orWhere('tipo_unidad', $tipo);
        });
    }

    public function unidades()
    {
        return $this->hasMany(Unidad::class, 'checklist_plantilla_id');
    }
}