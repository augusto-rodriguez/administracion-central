<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistHallazgo extends Model
{
    protected $table = 'checklist_hallazgos';

    protected $fillable = [
        'inspeccion_id',
        'item_id',
        'severidad',
        'descripcion',
        'estado',
        'asignado_a',
        'resuelto_at',
        'resuelto_por',
        'notificado',
        'notificado_at',
    ];

    protected $casts = [
        'notificado'    => 'boolean',
        'notificado_at' => 'datetime',
        'resuelto_at'   => 'datetime',
    ];

    // 🔗 Relaciones

    public function inspeccion()
    {
        return $this->belongsTo(ChecklistInspeccion::class, 'inspeccion_id');
    }

    public function item()
    {
        return $this->belongsTo(ChecklistItem::class, 'item_id');
    }

    public function asignado()
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    public function resolutorPor()
    {
        return $this->belongsTo(User::class, 'resuelto_por');
    }

    public function fotos()
    {
        return $this->hasMany(ChecklistHallazgoFoto::class, 'hallazgo_id');
    }

    public function fotosProblema()
    {
        return $this->hasMany(ChecklistHallazgoFoto::class, 'hallazgo_id')
                    ->where('tipo', 'problema');
    }

    public function fotosResolucion()
    {
        return $this->hasMany(ChecklistHallazgoFoto::class, 'hallazgo_id')
                    ->where('tipo', 'resolucion');
    }

    public function comentarios()
    {
        return $this->hasMany(ChecklistHallazgoComentario::class, 'hallazgo_id')
                    ->orderBy('created_at');
    }

    // 🔍 Scopes

    public function scopeAbiertos($query)
    {
        return $query->whereNotIn('estado', ['resuelto', 'verificado']);
    }

    public function scopeCriticos($query)
    {
        return $query->where('severidad', 'critico');
    }

    public function scopePendientesNotificacion($query)
    {
        return $query->where('notificado', false);
    }

    public function scopeDeUnidad($query, int $unidadId)
    {
        return $query->whereHas('inspeccion', fn ($q) => $q->where('unidad_id', $unidadId));
    }

    // 🛠 Helpers

    /**
     * Cambia el estado del hallazgo y registra el cambio como comentario.
     */
    public function cambiarEstado(string $nuevoEstado, User $usuario, ?string $comentario = null): void
    {
        $estadoAnterior = $this->estado;

        $this->update(['estado' => $nuevoEstado]);

        // Si se resuelve, registrar quién y cuándo
        if ($nuevoEstado === 'resuelto') {
            $this->update([
                'resuelto_at'  => now(),
                'resuelto_por' => $usuario->id,
            ]);
        }

        // Registrar el cambio en el historial de comentarios
        $this->comentarios()->create([
            'user_id'         => $usuario->id,
            'comentario'      => $comentario ?? "Estado cambiado de {$estadoAnterior} a {$nuevoEstado}.",
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $nuevoEstado,
        ]);
    }

    public function esCritico(): bool
    {
        return $this->severidad === 'critico';
    }

    public function estaAbierto(): bool
    {
        return !in_array($this->estado, ['resuelto', 'verificado']);
    }

    /**
     * Días transcurridos desde que se abrió el hallazgo.
     */
    public function diasAbierto(): int
    {
        $fin = $this->resuelto_at ?? now();
        return (int) $this->created_at->diffInDays($fin);
    }
}