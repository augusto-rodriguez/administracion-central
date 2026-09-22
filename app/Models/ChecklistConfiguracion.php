<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistConfiguracion extends Model
{
    protected $table = 'checklist_configuracion';

    protected $fillable = ['clave', 'valor', 'descripcion'];

    /**
     * Obtener un valor de configuración por su clave.
     */
    public static function obtener(string $clave, string $default = null): ?string
    {
        return static::where('clave', $clave)->value('valor') ?? $default;
    }
}