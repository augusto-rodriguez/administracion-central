<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OficialFueraServicio extends Model
{
    protected $table = 'oficiales_fuera_servicio';

    protected $fillable = ['voluntario_id', 'fecha_inicio', 'fecha_fin', 'motivo'];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class);
    }
    
    public function scopeVigentes($query, $fecha = null)
    {
        $dia = Carbon::parse($fecha ?? now('America/Santiago'))->toDateString();

        return $query->whereDate('fecha_inicio', '<=', $dia)
            ->where(fn($q) => $q->whereNull('fecha_fin')
                                ->orWhereDate('fecha_fin', '>', $dia));
    }
}