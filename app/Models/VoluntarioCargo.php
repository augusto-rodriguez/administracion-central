<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoluntarioCargo extends Model
{
    protected $table = 'voluntario_cargos';

    protected $fillable = [
        'voluntario_id',
        'cargo_id',
        'compania_id',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'designado_por',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'activo'       => 'boolean',
    ];

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class);
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    public function compania()
    {
        return $this->belongsTo(Compania::class);
    }
}