<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Citacion extends Model
{
    protected $table = 'citaciones';

    protected $fillable = [
        'compania_id',
        'medio_recepcion_id',
        'mensaje',
        'fecha_citacion',
    ];

    // 🔗 Relaciones

    public function compania()
    {
        return $this->belongsTo(Compania::class);
    }

    public function medioRecepcion()
    {
        return $this->belongsTo(MedioRecepcionCitacion::class, 'medio_recepcion_id');
    }
}