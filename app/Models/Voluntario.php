<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voluntario extends Model
{
    protected $table = 'voluntarios';

    protected $fillable = [
        'compania_id', 'nombre', 'rut', 'telefono', 'email', 'activo'
    ];

    public function compania()
    {
        return $this->belongsTo(Compania::class);
    }

    public function roles()
    {
        return $this->hasMany(VoluntarioRol::class);
    }

    public function unidadesAutorizadas()
    {
        return $this->belongsToMany(Unidad::class, 'voluntario_unidad')
                    ->withPivot('autorizado_por', 'fecha_autorizacion')
                    ->withTimestamps();
    }

    public function turnos()
    {
        return $this->hasMany(RegistroTurno::class);
    }

    public function turnoActivo()
    {
        return $this->hasOne(RegistroTurno::class)->whereNull('salida_at');
    }

    public function esMaquinista(): bool
    {
        return $this->roles()->where('rol', 'maquinista')->where('activo', true)->exists();
    }

    public function esOficial(): bool
    {
        return $this->roles()->where('rol', 'oficial')->where('activo', true)->exists();
    }

    public function getRolesListaAttribute(): string
    {
        return $this->roles->where('activo', true)->pluck('rol')->join(', ');
    }
}