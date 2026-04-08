<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibroNovedades extends Model
{
    protected $table = 'libro_novedades';

    protected $fillable = [
        'fecha',
        'turno',
        'hora_inicio',
        'hora_fin',
        'operador_id',
        'operador_turno_anterior',
        'maquinistas_al_recibir',
        'cuarteleros_al_recibir',
        'unidades_fuera_servicio_al_recibir',
        'maquinistas_al_entregar',
        'cuarteleros_al_entregar',
        'unidades_fuera_servicio_al_entregar',
        'puestas_en_servicio',
        'salidas_administrativas',
        'salidas_emergencia',
        'novedades_cronologicas',
        'observaciones_telecomunicaciones',
        'novedades_viper',
        'estado',
        'cerrado_por',
        'cerrado_at',
    ];

    protected $casts = [
        'fecha'                                => 'date',
        'cerrado_at'                           => 'datetime',
        'maquinistas_al_recibir'               => 'array',
        'cuarteleros_al_recibir'               => 'array',
        'unidades_fuera_servicio_al_recibir'   => 'array',
        'maquinistas_al_entregar'              => 'array',
        'cuarteleros_al_entregar'              => 'array',
        'unidades_fuera_servicio_al_entregar'  => 'array',
        'puestas_en_servicio'                  => 'array',
        'salidas_administrativas'              => 'array',
        'salidas_emergencia'                   => 'array',
    ];

    public function operador()
    {
        return $this->belongsTo(User::class, 'operador_id');
    }

    public function cerradoPor()
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    // Etiqueta legible del turno
    public function getTurnoLabelAttribute(): string
    {
        return $this->turno === 'dia' ? 'Turno Día' : 'Turno Noche';
    }

    // Horario formateado
    public function getHorarioAttribute(): string
    {
        return substr($this->hora_inicio, 0, 5) . ' a ' . substr($this->hora_fin, 0, 5) . ' hrs';
    }
}