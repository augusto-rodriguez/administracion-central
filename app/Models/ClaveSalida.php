<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaveSalida extends Model
{
    protected $table = 'claves_salida';

    protected $fillable = ['codigo', 'descripcion', 'tipo', 'activa'];

    public function salidas()
    {
        return $this->hasMany(SalidaUnidad::class);
    }
}