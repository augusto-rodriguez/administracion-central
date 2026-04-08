<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compania extends Model
{
    protected $table = 'companias';

    protected $fillable = ['nombre', 'numero', 'direccion', 'telefono', 'activa'];

    public function voluntarios()
    {
        return $this->hasMany(Voluntario::class);
    }

    public function unidades()
    {
        return $this->hasMany(Unidad::class);
    }

    public function citaciones()
    {
        return $this->hasMany(Citacion::class);
    }
    
    public function cuarteleros()
    {
        return $this->hasMany(Cuartelero::class);
    }
}