<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'voluntario_id', 'nombre', 'email', 'password', 'rol', 'activo'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['password' => 'hashed'];

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class);
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function esComandante(): bool
    {
        return in_array($this->rol, ['admin', 'comandante']);
    }

    public function esOperador(): bool
    {
        return in_array($this->rol, ['admin', 'comandante', 'operador']);
    }
}