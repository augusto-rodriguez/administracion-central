<?php

namespace App\Exports;

use App\Models\SalidaUnidad;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalidasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $filtros;

    public function __construct(array $filtros)
    {
        $this->filtros = $filtros;
    }

    public function collection()
    {
        $query = SalidaUnidad::with(['unidad.compania', 'claveSalida', 'oficial', 'voluntario'])
            ->whereNotNull('llegada_at');

        if (!empty($this->filtros['desde'])) {
            $query->whereDate('salida_at', '>=', $this->filtros['desde']);
        }
        if (!empty($this->filtros['hasta'])) {
            $query->whereDate('salida_at', '<=', $this->filtros['hasta']);
        }
        if (!empty($this->filtros['compania_id'])) {
            $query->whereHas('unidad', fn($q) => $q->where('compania_id', $this->filtros['compania_id']));
        }
        if (!empty($this->filtros['unidad_id'])) {
            $query->where('unidad_id', $this->filtros['unidad_id']);
        }
        if (!empty($this->filtros['clave_salida_id'])) {
            $query->where('clave_salida_id', $this->filtros['clave_salida_id']);
        }
        if (!empty($this->filtros['oficial_id'])) {
            $query->where('oficial_id', $this->filtros['oficial_id']);
        }

        return $query->orderBy('salida_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Unidad', 'Compañía', 'Clave', 'Descripción',
            'Dirección', 'Conductor', 'Oficial Autorizante',
            'Fecha Salida', 'Hora Salida',
            'Fecha Llegada', 'Hora Llegada',
            'Tiempo', 'Km Salida', 'Km Llegada', 'Km Recorridos',
            'Personal', 'Observaciones',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,  // Unidad
            'B' => 18,  // Compañía
            'C' => 7,  // Clave
            'D' => 35,  // Descripción
            'E' => 50,  // Dirección
            'F' => 25,  // Conductor
            'G' => 25,  // Oficial
            'H' => 12,  // Fecha Salida
            'I' => 10,  // Hora Salida
            'J' => 12,  // Fecha Llegada
            'K' => 10,  // Hora Llegada
            'L' => 12,  // Tiempo
            'M' => 12,  // Km Salida
            'N' => 12,  // Km Llegada
            'O' => 7,  // Km Recorridos
            'P' => 7,  // Personal
            'Q' => 30,  // Observaciones
        ];
    }

    public function map($salida): array
    {
        $minutos = $salida->salida_at && $salida->llegada_at
            ? $salida->salida_at->diffInMinutes($salida->llegada_at) : 0;
        $horas = intdiv($minutos, 60);
        $mins  = $minutos % 60;

        return [
            $salida->unidad->nombre,
            $salida->unidad->compania->nombre ?? '—',
            $salida->claveSalida->codigo,
            $salida->claveSalida->descripcion,
            $salida->direccion,
            $salida->conductor_nombre,
            $salida->oficial?->nombre ?? '—',
            $salida->salida_at->format('d/m/Y'),
            $salida->salida_at->format('H:i'),
            $salida->llegada_at?->format('d/m/Y') ?? '—',
            $salida->llegada_at?->format('H:i') ?? '—',
            "{$horas}h {$mins}min",
            $salida->km_salida ?? '—',
            $salida->km_llegada ?? '—',
            $salida->km_recorrido ?? '—',
            $salida->cantidad_personal ?? '—',
            $salida->observaciones ?? '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'C0392B']],
            ],
        ];
    }
}