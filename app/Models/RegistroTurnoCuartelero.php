<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroTurnoCuartelero extends Model
{
    protected $table = 'registros_turno_cuartelero';

    protected $fillable = [
        'cuartelero_id', 'entrada_at', 'salida_at', 'total_minutos', 'observaciones'
    ];

    protected $casts = [
        'entrada_at' => 'datetime',
        'salida_at'  => 'datetime',
    ];

    public function cuartelero()
    {
        return $this->belongsTo(Cuartelero::class);
    }

    public function unidades()
    {
        return $this->belongsToMany(Unidad::class, 'turno_cuartelero_unidad',
                    'turno_id', 'unidad_id')
                    ->withTimestamps();
    }

    public function getTiempoFormateadoAttribute(): string
    {
        $minutos = $this->total_minutos ?? 0;
        $horas   = intdiv($minutos, 60);
        $mins    = $minutos % 60;
        return "{$horas}h {$mins}min";
    }
}