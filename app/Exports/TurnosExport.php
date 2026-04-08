<?php

namespace App\Exports;

use App\Models\RegistroTurno;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TurnosExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles, 
    WithTitle,
    WithColumnWidths,
    WithCustomStartCell
{
    protected $compania_id;
    protected $anio;
    protected $mes;
    protected $companiaName;

    public function __construct($compania_id, $anio, $mes, $companiaName)
    {
        $this->compania_id  = $compania_id;
        $this->anio         = $anio;
        $this->mes          = $mes;
        $this->companiaName = $companiaName;
    }

    public function startCell(): string
    {
        return 'A2'; // encabezados comienzan en fila 3
    }

    public function collection()
    {
        $query = RegistroTurno::with(['voluntario.compania', 'unidades'])
            ->whereHas('voluntario', fn($q) => $q->where('compania_id', $this->compania_id))
            ->whereNotNull('salida_at');

        if ($this->mes) {
            $query->whereMonth('entrada_at', $this->mes)
                  ->whereYear('entrada_at', $this->anio);
        } else {
            $query->whereYear('entrada_at', $this->anio);
        }

        return $query->orderBy('entrada_at', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Maquinista',
            'Unidades',
            'Fecha Entrada',
            'Hora Entrada',
            'Fecha Salida',
            'Hora Salida',
            'Tiempo en Servicio',
            'Observaciones',
        ];
    }

    public function map($turno): array
    {
        $horas   = intdiv($turno->total_minutos, 60);
        $minutos = $turno->total_minutos % 60;

        return [
            $turno->voluntario->nombre,
            $turno->unidades->pluck('nombre')->join(', '),
            $turno->entrada_at->format('d/m/Y'),
            $turno->entrada_at->format('H:i'),
            $turno->salida_at->format('d/m/Y'),
            $turno->salida_at->format('H:i'),
            "{$horas}h {$minutos}min",
            $turno->observaciones ?? '',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // Maquinista (doble ancho)
            'B' => 20,
            'C' => 15,
            'D' => 12,
            'E' => 15,
            'F' => 12,
            'G' => 18,
            'H' => 35,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $periodo = $this->mes
            ? sprintf('%02d/%s', $this->mes, $this->anio)
            : $this->anio;

        $titulo = "Horas Maquinistas {$this->companiaName} del periodo {$periodo}";

        // Combinar celdas del título
        $sheet->mergeCells('A1:H1');

        // Escribir título
        $sheet->setCellValue('A1', $titulo);

        // Estilo del título
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center'
            ]
        ]);

        // Estilo encabezados (fila 2 ahora)
        $sheet->getStyle('A2:H2')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => 'C0392B']
            ],
        ]);
    }

    public function title(): string
    {
        return 'Turnos';
    }
}