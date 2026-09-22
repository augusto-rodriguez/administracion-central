<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    protected $table = 'unidades';

    protected $fillable = [
        'compania_id', 'nombre', 'patente', 'tipo', 'descripcion', 'activa', 'checklist_plantilla_id'
    ];

    public function compania()
    {
        return $this->belongsTo(Compania::class);
    }

    public function voluntariosAutorizados()
    {
        return $this->belongsToMany(Voluntario::class, 'voluntario_unidad')
                    ->withPivot('autorizado_por', 'fecha_autorizacion')
                    ->withTimestamps();
    }

    public function turnos()
    {
        return $this->belongsToMany(RegistroTurno::class, 'turno_unidad',
                    'unidad_id', 'turno_id')
                    ->withTimestamps();
    }

    public function salidas()
    {
        return $this->hasMany(SalidaUnidad::class);
    }

    public function cuarteleros()
    {
        return $this->belongsToMany(Cuartelero::class, 'cuartelero_unidad');
    }

    public function checklistPlantilla()
    {
        return $this->belongsTo(\App\Models\ChecklistPlantilla::class, 'checklist_plantilla_id');
    }
}