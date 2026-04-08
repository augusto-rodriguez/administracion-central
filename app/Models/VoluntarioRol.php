<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoluntarioRol extends Model
{
    protected $table = 'voluntario_roles';

    protected $fillable = ['voluntario_id', 'rol', 'rango', 'activo', 'puede_autorizar_salidas'];

    protected $casts = [
        'activo'                  => 'boolean',
        'puede_autorizar_salidas' => 'boolean',
    ];

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class);
    }
}