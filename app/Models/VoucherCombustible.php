<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherCombustible extends Model
{
    protected $table = 'vouchers_combustible';

    protected $fillable = [
        'fecha_carga',
        'unidad_id',
        'km_carga',
        'conductor_nombre',
        'numero_voucher',
        'litros',
        'valor_unitario',
        'total',
        'observaciones',
        'registrado_por',
    ];

    protected $casts = [
        'fecha_carga' => 'date',
    ];

    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function getTotalFormateadoAttribute(): string
    {
        return '$' . number_format($this->total, 0, ',', '.');
    }

    public function getValorUnitarioFormateadoAttribute(): string
    {
        return '$' . number_format($this->valor_unitario, 0, ',', '.');
    }

    public function getLitrosFormateadosAttribute(): string
    {
        return number_format($this->litros, 3, ',', '.') . ' L';
    }
}