<?php

namespace App\Exports;

use App\Models\VoucherCombustible;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VouchersCombustibleExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $companiaId;
    protected $unidadId;

    public function __construct($mes, $anio, $companiaId = null, $unidadId = null)
    {
        $this->mes       = $mes;
        $this->anio      = $anio;
        $this->companiaId = $companiaId;
        $this->unidadId  = $unidadId;
    }

    public function collection()
    {
        $query = VoucherCombustible::with(['unidad.compania'])
            ->orderBy('fecha_carga', 'asc');

        if ($this->companiaId) {
            $query->whereHas('unidad', fn($q) => $q->where('compania_id', $this->companiaId));
        }

        if ($this->unidadId) {
            $query->where('unidad_id', $this->unidadId);
        }

        if ($this->mes) {
            $query->whereMonth('fecha_carga', $this->mes)
                ->whereYear('fecha_carga', $this->anio);
        } else {
            $query->whereYear('fecha_carga', $this->anio);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'N° Voucher',
            'Fecha Carga',
            'Unidad',
            'Compañía',
            'Km Carga',
            'Conductor',
            'Litros',
            'Valor Unitario ($)',
            'Total ($)',
            'Observaciones',
        ];
    }

    public function map($v): array
    {
        return [
            $v->numero_voucher,
            $v->fecha_carga->format('d/m/Y'),
            $v->unidad->nombre,
            $v->unidad->compania->nombre,
            $v->km_carga,
            $v->conductor_nombre,
            number_format($v->litros, 3, ',', '.'),
            $v->valor_unitario,
            $v->total,
            $v->observaciones ?? '',
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

    public function title(): string
    {
        return 'Vouchers Combustible';
    }
}