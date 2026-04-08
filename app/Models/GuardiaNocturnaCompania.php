<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuardiaNocturnaCompania extends Model
{
    protected $table = 'guardia_nocturna_compania';

    protected $fillable = [
        'guardia_nocturna_id',
        'compania_id',
        'oficial_a_cargo_id',
        'cuartelero_id',
        'sin_reporte',
        'observaciones',
    ];

    protected $casts = [
        'sin_reporte' => 'boolean',
    ];

    public function guardia()
    {
        return $this->belongsTo(GuardiaNocturna::class, 'guardia_nocturna_id');
    }

    public function compania()
    {
        return $this->belongsTo(Compania::class);
    }

    public function oficialACargo()
    {
        return $this->belongsTo(Voluntario::class, 'oficial_a_cargo_id');
    }

    public function cuartelero()
    {
        return $this->belongsTo(Cuartelero::class);
    }

    public function voluntarios()
    {
        return $this->hasMany(GuardiaNocturnaVoluntario::class);
    }

    public function unidades()
    {
        return $this->hasMany(GuardiaNocturnaUnidad::class);
    }
}