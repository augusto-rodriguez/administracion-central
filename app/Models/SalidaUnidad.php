<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalidaUnidad extends Model
{
    protected $table = 'salidas_unidad';

    protected $fillable = [
        'unidad_id', 'clave_salida_id', 'voluntario_id', 'oficial_id', 'al_mando_id',
        'conductor_libre', 'direccion', 'cantidad_personal',
        'km_salida', 'km_llegada', 'km_recorrido',
        'salida_at', 'llegada_at', 'observaciones',
        'salida_padre_id',
    ];

    protected $casts = [
        'salida_at'  => 'datetime',
        'llegada_at' => 'datetime',
    ];

    // ─────────────────────────────────────────────────────────────────────
    // RELACIONES EXISTENTES
    // ─────────────────────────────────────────────────────────────────────

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

    // ─────────────────────────────────────────────────────────────────────
    // RELACIONES DE SOBRESALIDA
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Salida raíz a la que pertenece esta sobresalida.
     * NULL si este registro ES la raíz.
     */
    public function salidaPadre(): BelongsTo
    {
        return $this->belongsTo(SalidaUnidad::class, 'salida_padre_id');
    }

    /**
     * Todas las sobresalidas que tienen a este registro como raíz.
     */
    public function sobresalidas(): HasMany
    {
        return $this->hasMany(SalidaUnidad::class, 'salida_padre_id')
                    ->orderBy('salida_at', 'asc');
    }

    // ─────────────────────────────────────────────────────────────────────
    // ACCESSORS EXISTENTES
    // ─────────────────────────────────────────────────────────────────────

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

    // ─────────────────────────────────────────────────────────────────────
    // HELPERS DE SOBRESALIDA
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Indica si este registro es una sobresalida (tiene padre).
     */
    public function esSobresalida(): bool
    {
        return !is_null($this->salida_padre_id);
    }

    /**
     * Indica si este registro es una salida raíz (sin padre).
     */
    public function esSalidaRaiz(): bool
    {
        return is_null($this->salida_padre_id);
    }

    /**
     * Cantidad de sobresalidas encadenadas a esta raíz.
     */
    public function totalSobresalidas(): int
    {
        return $this->sobresalidas()->count();
    }

    /**
     * Obtiene el tramo activo de la cadena (llegada_at = null).
     * Con el nuevo diseño, al registrar una sobresalida el tramo anterior
     * se cierra automáticamente, por lo que siempre hay exactamente UN
     * registro activo por unidad. Este método lo ubica partiendo desde
     * la raíz o desde cualquier sobresalida de la cadena.
     */
    public function ultimaSalidaActiva(): ?self
    {
        // Si este mismo registro está activo, devolverlo directamente
        if ($this->llegada_at === null) return $this;

        // Si es raíz, buscar entre sus sobresalidas la que esté activa
        if ($this->esSalidaRaiz()) {
            return $this->sobresalidas()->whereNull('llegada_at')->first();
        }

        // Si es sobresalida, buscar entre los hijos del padre (la raíz)
        return static::where('salida_padre_id', $this->salida_padre_id)
            ->whereNull('llegada_at')
            ->first();
    }

    /**
     * Determina si este registro puede ser editado (ventana de 12 horas).
     * Siempre editable dentro de la ventana — los campos sensibles (km_salida,
     * conductor) se bloquean en la vista cuando hay sobresalidas encadenadas.
     */
    public function esEditable(): bool
    {
        return $this->salida_at->diffInHours(now()) < 12;
    }

    /**
     * Indica si km_salida y conductor son editables.
     * No lo son cuando la raíz ya tiene sobresalidas que heredaron esos valores.
     */
    public function kmYConductorEditables(): bool
    {
        if ($this->esSalidaRaiz() && $this->sobresalidas()->exists()) {
            return false;
        }
        return true;
    }
}