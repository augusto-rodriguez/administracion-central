<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedioRecepcionCitacion extends Model
{
    protected $table = 'medios_recepcion_citaciones';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    // 🔗 Relaciones

    public function citaciones()
    {
        return $this->hasMany(Citacion::class, 'medio_recepcion_id');
    }
}