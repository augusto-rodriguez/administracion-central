<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Cuartelero extends Model
{
    protected $table = 'cuarteleros';

    protected $fillable = [
        'compania_id', 'nombre', 'rut', 'telefono', 'activo',
        'fecha_inicio', 'fecha_fin', 'motivo_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'activo'       => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function compania()
    {
        return $this->belongsTo(Compania::class);
    }

    public function unidadesAutorizadas()
    {
        return $this->belongsToMany(Unidad::class, 'cuartelero_unidad')
                    ->withTimestamps();
    }

    public function turnos()
    {
        return $this->hasMany(RegistroTurnoCuartelero::class);
    }

    public function turnoActivo()
    {
        return $this->hasOne(RegistroTurnoCuartelero::class)->whereNull('salida_at');
    }

    // ─── Scopes ───────────────────────────────────────────────────

    /** Cuarteleros actualmente en cargo (sin fecha_fin) */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->whereNull('fecha_fin');
    }

    /** Cuarteleros que ya terminaron su período */
    public function scopeHistorico(Builder $query): Builder
    {
        return $query->whereNotNull('fecha_fin');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /** ¿Está actualmente en cargo? */
    public function estaActivo(): bool
    {
        return is_null($this->fecha_fin);
    }

    /** Período formateado para mostrar en vistas */
    public function periodoFormateado(): string
    {
        $inicio = $this->fecha_inicio
            ? $this->fecha_inicio->format('d/m/Y')
            : 'Sin fecha';

        $fin = $this->fecha_fin
            ? $this->fecha_fin->format('d/m/Y')
            : 'Presente';

        return "{$inicio} — {$fin}";
    }
}