<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistInspeccion extends Model
{
    protected $table = 'checklist_inspecciones';

    protected $fillable = [
        'unidad_id',
        'cuartelero_id',
        'plantilla_id',
        'fecha',
        'kilometraje',
        'hora_motor',
        'hora_bomba',
        'hora_ultimo_cambio_aceite',
        'proxima_mantencion',
        'observaciones',
        'estado',
        'completado_at',
    ];
    protected $casts = [
        'fecha'         => 'date',
        'completado_at' => 'datetime',
    ];

    // 🔗 Relaciones

    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }

    public function cuartelero()
    {
        return $this->belongsTo(Cuartelero::class);
    }

    public function plantilla()
    {
        return $this->belongsTo(ChecklistPlantilla::class, 'plantilla_id');
    }

    public function respuestas()
    {
        return $this->hasMany(ChecklistRespuesta::class, 'inspeccion_id');
    }

    public function hallazgos()
    {
        return $this->hasMany(ChecklistHallazgo::class, 'inspeccion_id');
    }

    // 🔍 Scopes

    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    public function scopeBorradores($query)
    {
        return $query->where('estado', 'borrador');
    }

    public function scopeDeUnidad($query, int $unidadId)
    {
        return $query->where('unidad_id', $unidadId);
    }

    public function scopeDeCuartelero($query, int $cuarteleroId)
    {
        return $query->where('cuartelero_id', $cuarteleroId);
    }

    // 🛠 Helpers

    public function estaCompletado(): bool
    {
        return $this->estado === 'completado';
    }

    public function esBorrador(): bool
    {
        return $this->estado === 'borrador';
    }

    /**
     * Marca la inspección como completada y registra la fecha.
     */
    public function completar(): void
    {
        $this->update([
            'estado'        => 'completado',
            'completado_at' => now(),
        ]);
    }

    /**
     * Cantidad de ítems con problemas (regular, malo, vencido, niveles bajos).
     */
    public function cantidadProblemas(): int
    {
        return $this->respuestas()
            ->whereIn('valor', ['regular', 'malo', 'vencido', '1/4'])
            ->count();
    }
}