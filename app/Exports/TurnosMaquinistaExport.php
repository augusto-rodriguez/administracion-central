<?php

namespace App\Exports;

use App\Models\RegistroTurno;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TurnosMaquinistaExport implements 
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithCustomStartCell,
    WithColumnWidths
{
    protected $maquinista_id;
    protected $maquinista_nombre;
    protected $desde;
    protected $hasta;

    public function __construct($maquinista_id, $maquinista_nombre, $desde, $hasta)
    {
        $this->maquinista_id     = $maquinista_id;
        $this->maquinista_nombre = $maquinista_nombre;
        $this->desde             = $desde;
        $this->hasta             = $hasta;
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function collection()
    {
        $query = RegistroTurno::with(['voluntario.compania', 'unidades'])
            ->where('voluntario_id', $this->maquinista_id)
            ->whereNotNull('salida_at');

        if ($this->desde && $this->hasta) {
            $query->whereDate('entrada_at', '>=', $this->desde)
                  ->whereDate('entrada_at', '<=', $this->hasta);
        }

        return $query->orderBy('entrada_at', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Maquinista',
            'Compañía',
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
            $turno->voluntario->compania->nombre ?? '—',
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
            'A' => 20,
            'B' => 25,
            'C' => 20,
            'D' => 15,
            'E' => 12,
            'F' => 15,
            'G' => 12,
            'H' => 18,
            'I' => 35,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $periodo = "Todos los registros";

        if ($this->desde && $this->hasta) {
            $desde = Carbon::parse($this->desde)->format('d/m/Y');
            $hasta = Carbon::parse($this->hasta)->format('d/m/Y');

            $periodo = "{$desde} al {$hasta}";
        }

        $titulo = "Horas Maquinista {$this->maquinista_nombre} del periodo {$periodo}";

        // combinar celdas para el título
        $sheet->mergeCells('A1:I1');

        $sheet->setCellValue('A1', $titulo);

        // estilo del título
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

        // estilo encabezados
        $sheet->getStyle('A2:I2')->applyFromArray([
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
}