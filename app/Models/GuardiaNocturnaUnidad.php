<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuardiaNocturnaUnidad extends Model
{
    protected $table = 'guardia_nocturna_unidad';

    protected $fillable = [
        'guardia_nocturna_compania_id',
        'unidad_id',
        'maquinista_id',
        'cuartelero_id',
    ];

    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }

    public function maquinista()
    {
        return $this->belongsTo(Voluntario::class, 'maquinista_id');
    }

    public function cuartelero()
    {
        return $this->belongsTo(Cuartelero::class);
    }

    public function guardiaCompania()
    {
        return $this->belongsTo(GuardiaNocturnaCompania::class, 'guardia_nocturna_compania_id');
    }
}