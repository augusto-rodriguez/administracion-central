<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuardiaNocturnaVoluntario extends Model
{
    protected $table = 'guardia_nocturna_voluntario';

    protected $fillable = [
        'guardia_nocturna_compania_id',
        'voluntario_id',
        'hora_ingreso',
    ];

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class);
    }

    public function guardiaCompania()
    {
        return $this->belongsTo(GuardiaNocturnaCompania::class, 'guardia_nocturna_compania_id');
    }
}