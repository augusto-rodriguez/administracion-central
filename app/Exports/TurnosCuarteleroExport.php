<?php

namespace App\Exports;

use App\Models\RegistroTurnoCuartelero;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class TurnosCuarteleroExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles,
    WithCustomStartCell,
    WithColumnWidths
{
    protected $cuartelero_id;
    protected $cuartelero_nombre;
    protected $desde;
    protected $hasta;

    public function __construct($cuartelero_id, $cuartelero_nombre, $desde, $hasta)
    {
        $this->cuartelero_id     = $cuartelero_id;
        $this->cuartelero_nombre = $cuartelero_nombre;
        $this->desde             = $desde;
        $this->hasta             = $hasta;
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function collection()
    {
        $query = RegistroTurnoCuartelero::with(['cuartelero.compania', 'unidades'])
            ->where('cuartelero_id', $this->cuartelero_id)
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
            'Cuartelero',
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
            $turno->cuartelero->nombre,
            $turno->cuartelero->compania->nombre ?? '—',
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

        $titulo = "Turnos cuartelero {$this->cuartelero_nombre} del periodo {$periodo}";

        // Combinar celdas para el título
        $sheet->mergeCells('A1:I1');

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

        // Estilo encabezados
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