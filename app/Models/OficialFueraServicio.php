<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OficialFueraServicio extends Model
{
    protected $table = 'oficiales_fuera_servicio';

    protected $fillable = ['voluntario_id', 'fecha_inicio', 'fecha_fin', 'motivo'];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class);
    }
}