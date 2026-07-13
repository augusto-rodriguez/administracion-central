<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    public $timestamps = false; // Solo usamos created_at manual

    protected $fillable = [
        'user_id',
        'evento',
        'email',
        'ip',
        'user_agent',
        'ciudad',
        'pais',
        'navegador',
        'plataforma',
        'dispositivo',
        'session_id',
        'exitoso',
        'motivo_fallo',
        'created_at',
    ];

    protected $casts = [
        'exitoso'    => 'boolean',
        'created_at' => 'datetime',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeExitosos($query)
    {
        return $query->where('exitoso', true);
    }

    public function scopeFallidos($query)
    {
        return $query->where('exitoso', false);
    }

    public function scopeEvento($query, string $evento)
    {
        return $query->where('evento', $evento);
    }

    public function scopeDeUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDesde($query, $fecha)
    {
        return $query->where('created_at', '>=', $fecha);
    }

    public function scopeHasta($query, $fecha)
    {
        return $query->where('created_at', '<=', $fecha);
    }

    public function scopeIpSospechosa($query, int $intentosMinimos = 5)
    {
        return $query->fallidos()
            ->select('ip')
            ->selectRaw('COUNT(*) as intentos')
            ->groupBy('ip')
            ->havingRaw('COUNT(*) >= ?', [$intentosMinimos]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public function getIconoEventoAttribute(): string
    {
        return match ($this->evento) {
            'login'   => '🟢',
            'logout'  => '🔵',
            'failed'  => '🔴',
            'lockout' => '⛔',
            default   => '⚪',
        };
    }

    public function getDescripcionEventoAttribute(): string
    {
        return match ($this->evento) {
            'login'   => 'Inicio de sesión',
            'logout'  => 'Cierre de sesión',
            'failed'  => 'Intento fallido',
            'lockout' => 'Cuenta bloqueada',
            default   => 'Desconocido',
        };
    }
}