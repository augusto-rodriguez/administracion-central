<?php

namespace App\Exports;

use App\Models\SalidaUnidad;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class SalidasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithDrawings, WithCustomStartCell, WithEvents
{
    protected $filtros;
    protected $columnasSeleccionadas;

    protected const COLUMNAS = [
        'unidad'        => ['heading' => 'Unidad',              'width' => 8],
        'compania'      => ['heading' => 'Compañía',            'width' => 18],
        'clave'         => ['heading' => 'Clave',               'width' => 7],
        'descripcion'   => ['heading' => 'Descripción',         'width' => 35],
        'direccion'     => ['heading' => 'Dirección',           'width' => 50],
        'conductor'     => ['heading' => 'Conductor',           'width' => 25],
        'al_mando'      => ['heading' => 'Al Mando',            'width' => 25],
        'oficial'       => ['heading' => 'Oficial Autorizante', 'width' => 25],
        'fecha_salida'  => ['heading' => 'Fecha Salida',        'width' => 12],
        'hora_salida'   => ['heading' => 'Hora Salida',         'width' => 10],
        'fecha_llegada' => ['heading' => 'Fecha Llegada',       'width' => 12],
        'hora_llegada'  => ['heading' => 'Hora Llegada',        'width' => 10],
        'tiempo'        => ['heading' => 'Tiempo',              'width' => 12],
        'km_salida'     => ['heading' => 'Km Salida',           'width' => 12],
        'km_llegada'    => ['heading' => 'Km Llegada',          'width' => 12],
        'km_recorridos' => ['heading' => 'Km Recorridos',       'width' => 7],
        'personal'      => ['heading' => 'Personal',            'width' => 7],
        'observaciones' => ['heading' => 'Observaciones',       'width' => 30],
    ];

    public function __construct(array $filtros, array $columnas = [])
    {
        $this->filtros = $filtros;
        $this->columnasSeleccionadas = !empty($columnas)
            ? array_intersect_key(self::COLUMNAS, array_flip($columnas))
            : self::COLUMNAS;
    }

    public function collection()
    {
        $query = SalidaUnidad::with(['unidad.compania', 'claveSalida', 'oficial', 'voluntario', 'alMando'])
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

    public function startCell(): string
    {
        return 'A5';
    }

    public function headings(): array
    {
        return array_column($this->columnasSeleccionadas, 'heading');
    }

    public function columnWidths(): array
    {
        $widths = [];
        $col = 'A';
        foreach ($this->columnasSeleccionadas as $config) {
            $widths[$col] = $config['width'];
            $col++;
        }
        return $widths;
    }

    public function map($salida): array
    {
        $minutos = $salida->salida_at && $salida->llegada_at
            ? $salida->salida_at->diffInMinutes($salida->llegada_at) : 0;
        $horas = intdiv($minutos, 60);
        $mins  = $minutos % 60;

        $todas = [
            'unidad'        => $salida->unidad->nombre,
            'compania'      => $salida->unidad->compania->nombre ?? '—',
            'clave'         => $salida->claveSalida->codigo,
            'descripcion'   => $salida->claveSalida->descripcion,
            'direccion'     => $salida->direccion,
            'conductor'     => $salida->conductor_nombre,
            'al_mando'      => $salida->alMando?->nombre ?? '—',
            'oficial'       => $salida->oficial?->nombre ?? '—',
            'fecha_salida'  => $salida->salida_at->format('d/m/Y'),
            'hora_salida'   => $salida->salida_at->format('H:i'),
            'fecha_llegada' => $salida->llegada_at?->format('d/m/Y') ?? '—',
            'hora_llegada'  => $salida->llegada_at?->format('H:i') ?? '—',
            'tiempo'        => "{$horas}h {$mins}min",
            'km_salida'     => $salida->km_salida ?? '—',
            'km_llegada'    => $salida->km_llegada ?? '—',
            'km_recorridos' => $salida->km_recorrido ?? '—',
            'personal'      => $salida->cantidad_personal ?? '—',
            'observaciones' => $salida->observaciones ?? '—',
        ];

        return array_values(array_intersect_key($todas, $this->columnasSeleccionadas));
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo Cuerpo de Bomberos');
        $drawing->setPath(public_path('images/logo_SanPedroDeLaPaz.png'));
        $drawing->setHeight(60);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

    public function registerEvents(): array
    {
        $columnas = $this->columnasSeleccionadas;

        return [
            AfterSheet::class => function (AfterSheet $event) use ($columnas) {
                $sheet = $event->sheet->getDelegate();

                // Título al lado del logo
                $sheet->setCellValue('C1', 'Cuerpo de Bomberos San Pedro de la Paz');
                $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14);

                $sheet->setCellValue('C2', 'Reporte de Salidas');
                $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('666666');

                // Fecha de generación
                $sheet->setCellValue('C3', 'Generado: ' . now()->format('d/m/Y H:i'));
                $sheet->getStyle('C3')->getFont()->setSize(9)->getColor()->setRGB('999999');

                // Altura de las filas del encabezado con logo
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(5);

                // Estilo del encabezado de columnas (fila 5)
                $cantidadColumnas = count($columnas);
                $ultimaColumna = $this->getColumnLetter($cantidadColumnas);
                $rango = "A5:{$ultimaColumna}5";

                $sheet->getStyle($rango)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType'   => 'solid',
                        'startColor' => ['rgb' => 'C0392B'],
                    ],
                ]);
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Los estilos del header se manejan en registerEvents
        return [];
    }

    /**
     * Convierte un número de columna (1-based) a letra de Excel (A, B, ..., Z, AA, AB, ...).
     */
    private function getColumnLetter(int $numero): string
    {
        $letra = '';
        while ($numero > 0) {
            $numero--;
            $letra = chr(65 + ($numero % 26)) . $letra;
            $numero = intdiv($numero, 26);
        }
        return $letra;
    }
}