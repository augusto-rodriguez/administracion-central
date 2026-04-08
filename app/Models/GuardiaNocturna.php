<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class GuardiaNocturna extends Model
{
    protected $table = 'guardia_nocturna';

    protected $fillable = ['fecha', 'estado', 'cerrado_por', 'cerrado_at'];

    protected $casts = [
        'fecha'      => 'date',
        'cerrado_at' => 'datetime',
    ];

    public function companias()
    {
        return $this->hasMany(GuardiaNocturnaCompania::class);
    }

    public function cerradoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'cerrado_por');
    }

    public static function activa()
    {
        return static::where('estado', 'abierta')
            ->whereDate('fecha', today())
            ->first();
    }

    public function esCerrada(): bool
    {
        return $this->estado === 'cerrada';
    }
}