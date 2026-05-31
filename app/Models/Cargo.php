<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $fillable = [
        'nombre',
        'codigo',
        'tipo',
        'orden',
        'descripcion',
        'activo',
        'es_unico', 
    ];

    protected $casts = [
        'activo' => 'boolean',
        'es_unico' => 'boolean',
    ];

    const TIPO_COMPANIA = 'compania';
    const TIPO_GENERAL  = 'general';

    public function asignaciones()
    {
        return $this->hasMany(VoluntarioCargo::class);
    }

    public function asignacionesActivas()
    {
        return $this->hasMany(VoluntarioCargo::class)->where('activo', true);
    }

    public function esDeCompania(): bool
    {
        return $this->tipo === self::TIPO_COMPANIA;
    }

    public function esGeneral(): bool
    {
        return $this->tipo === self::TIPO_GENERAL;
    }
}