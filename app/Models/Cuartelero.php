<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuartelero extends Model
{
    protected $table = 'cuarteleros';

    protected $fillable = [
        'compania_id', 'nombre', 'rut', 'telefono', 'activo'
    ];

    public function compania()
    {
        return $this->belongsTo(Compania::class);
    }

    public function unidadesAutorizadas()
    {
        return $this->belongsToMany(Unidad::class, 'cuartelero_unidad')
                    ->withTimestamps();
    }

    public function turnos()
    {
        return $this->hasMany(RegistroTurnoCuartelero::class);
    }

    public function turnoActivo()
    {
        return $this->hasOne(RegistroTurnoCuartelero::class)->whereNull('salida_at');
    }
}