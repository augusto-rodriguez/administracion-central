<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoletinMaquinista extends Model
{
    protected $table = 'boletin_maquinistas';

    protected $fillable = [
        'boletin_id',
        'voluntario_id',
        'unidad_id',
        'unidades_texto',
        'estado',
    ];

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class);
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }

    public function boletin()
    {
        return $this->belongsTo(Boletin::class);
    }
}