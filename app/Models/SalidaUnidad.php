<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalidaUnidad extends Model
{
    protected $table = 'salidas_unidad';

    protected $fillable = [
        'unidad_id', 'clave_salida_id', 'voluntario_id', 'oficial_id', 'al_mando_id',
        'conductor_libre', 'direccion', 'cantidad_personal',
        'km_salida', 'km_llegada', 'km_recorrido',
        'salida_at', 'llegada_at', 'observaciones',
    ];

    protected $casts = [
        'salida_at'  => 'datetime',
        'llegada_at' => 'datetime',
    ];

    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }

    public function claveSalida()
    {
        return $this->belongsTo(ClaveSalida::class);
    }

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class);
    }

    public function oficial()
    {
        return $this->belongsTo(Voluntario::class, 'oficial_id');
    }

    public function alMando(): BelongsTo
    {
        return $this->belongsTo(Voluntario::class, 'al_mando_id');
    }

    public function getConductorNombreAttribute(): string
    {
        if ($this->voluntario_id && $this->voluntario) {
            return $this->voluntario->nombre;
        }
        return $this->conductor_libre ?? '—';
    }

    public function getTiempoFormateadoAttribute(): string
    {
        if (!$this->salida_at || !$this->llegada_at) return 'En curso';
        $minutos = $this->salida_at->diffInMinutes($this->llegada_at);
        $horas   = intdiv($minutos, 60);
        $mins    = $minutos % 60;
        return "{$horas}h {$mins}min";
    }
}