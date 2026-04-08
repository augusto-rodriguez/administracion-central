<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroTurno extends Model
{
    protected $table = 'registros_turno';

    protected $fillable = [
        'voluntario_id', 'entrada_at', 'salida_at', 'total_minutos', 'observaciones'
    ];

    protected $casts = [
        'entrada_at' => 'datetime',
        'salida_at'  => 'datetime',
    ];

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class);
    }

    public function unidades()
    {
        return $this->belongsToMany(Unidad::class, 'turno_unidad',
                    'turno_id', 'unidad_id')
                    ->withTimestamps();
    }

    public function calcularTotalMinutos(): int
    {
        if (!$this->entrada_at || !$this->salida_at) return 0;
        return $this->entrada_at->diffInMinutes($this->salida_at);
    }

    public function getTiempoFormateadoAttribute(): string
    {
        $minutos = $this->total_minutos ?? 0;
        $horas   = intdiv($minutos, 60);
        $mins    = $minutos % 60;
        return "{$horas}h {$mins}min";
    }
}