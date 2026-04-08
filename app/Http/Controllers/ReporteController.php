<?php

namespace App\Http\Controllers;

use App\Models\Compania;
use App\Models\Voluntario;
use App\Models\Cuartelero;
use App\Models\RegistroTurno;
use App\Models\RegistroTurnoCuartelero;
use App\Exports\TurnosExport;
use App\Exports\TurnosMaquinistaExport;
use App\Exports\TurnosCuarteleroExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $companias   = Compania::where('activa', true)->orderBy('numero')->get();
        $voluntarios = Voluntario::with('compania')->where('activo', true)->orderBy('nombre')->get();
        $cuarteleros = Cuartelero::with('compania')->where('activo', true)->orderBy('nombre')->get();

        $turnos       = collect();
        $totalMinutos = 0;
        $tab          = $request->tab ?? 'compania';

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
            4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $anios = range(now()->year, 2025);

        // Reporte por compañía (maquinistas)
        if ($tab === 'compania' && $request->filled('compania_id')) {
            $query = RegistroTurno::with(['voluntario.compania', 'unidades'])
                ->whereHas('voluntario', fn($q) => $q->where('compania_id', $request->compania_id))
                ->whereNotNull('salida_at');

            if ($request->filled('mes') && $request->filled('anio')) {
                $query->whereMonth('entrada_at', $request->mes)
                      ->whereYear('entrada_at', $request->anio);
            } elseif ($request->filled('anio')) {
                $query->whereYear('entrada_at', $request->anio);
            }

            $turnos       = $query->orderBy('entrada_at', 'asc')->get();
            $totalMinutos = $turnos->sum('total_minutos');
        }

        // Reporte por voluntario
        if ($tab === 'voluntario' && $request->filled('voluntario_id')) {
            $query = RegistroTurno::with(['voluntario.compania', 'unidades'])
                ->where('voluntario_id', $request->voluntario_id)
                ->whereNotNull('salida_at');

            if ($request->filled('desde') && $request->filled('hasta')) {
                $query->whereDate('entrada_at', '>=', $request->desde)
                      ->whereDate('entrada_at', '<=', $request->hasta);
            }

            $turnos       = $query->orderBy('entrada_at', 'asc')->get();
            $totalMinutos = $turnos->sum('total_minutos');
        }

        // Reporte por cuartelero
        if ($tab === 'cuartelero' && $request->filled('cuartelero_id')) {
            $query = RegistroTurnoCuartelero::with(['cuartelero.compania', 'unidades'])
                ->where('cuartelero_id', $request->cuartelero_id)
                ->whereNotNull('salida_at');

            if ($request->filled('desde') && $request->filled('hasta')) {
                $query->whereDate('entrada_at', '>=', $request->desde)
                      ->whereDate('entrada_at', '<=', $request->hasta);
            }

            $turnos       = $query->orderBy('entrada_at', 'asc')->get();
            $totalMinutos = $turnos->sum('total_minutos');
        }

        return view('reportes.index', compact(
            'companias', 'voluntarios', 'cuarteleros', 'turnos',
            'totalMinutos', 'anios', 'meses', 'tab'
        ));
    }

    public function exportar(Request $request)
    {
        $compania = Compania::findOrFail($request->compania_id);
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
            4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        $periodo  = $request->mes ? $meses[$request->mes] . '_' . $request->anio : $request->anio;
        $filename = 'turnos_' . str_replace(' ', '_', $compania->nombre) . '_' . $periodo . '.xlsx';

        return Excel::download(
            new TurnosExport($request->compania_id, $request->anio, $request->mes, $compania->nombre),
            $filename
        );
    }

    public function exportarVoluntario(Request $request)
    {
        $voluntario = Voluntario::findOrFail($request->voluntario_id);
        $filename   = 'turnos_' . str_replace(' ', '_', $voluntario->nombre) . '_' . $request->desde . '_' . $request->hasta . '.xlsx';

        return Excel::download(
            new TurnosMaquinistaExport(
                $voluntario->id,
                $voluntario->nombre,
                $request->desde,
                $request->hasta
            ),
            $filename
        );
    }

    public function exportarCuartelero(Request $request)
    {
        $cuartelero = Cuartelero::findOrFail($request->cuartelero_id);

        $filename = 'turnos_cuartelero_' .
            str_replace(' ', '_', $cuartelero->nombre) . '_' .
            $request->desde . '_' .
            $request->hasta . '.xlsx';

        return Excel::download(
            new TurnosCuarteleroExport(
                $cuartelero->id,
                $cuartelero->nombre,
                $request->desde,
                $request->hasta
            ),
            $filename
        );
    }
    public function combustible(Request $request)
    {
        $companias = Compania::where('activa', true)->orderBy('numero')->get();
        $unidades  = \App\Models\Unidad::with('compania')->where('activa', true)->orderBy('nombre')->get();
        $anios     = range(now()->year, 2025);
        $meses     = [
            1 => 'Enero',    2 => 'Febrero',   3 => 'Marzo',
            4 => 'Abril',    5 => 'Mayo',       6 => 'Junio',
            7 => 'Julio',    8 => 'Agosto',     9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $anio      = $request->anio ?? now()->year;
        $mes       = $request->mes;
        $companiaId = $request->compania_id;

        // Query base
        $query = \App\Models\VoucherCombustible::with(['unidad.compania'])
            ->whereYear('fecha_carga', $anio);

        if ($mes) {
            $query->whereMonth('fecha_carga', $mes);
        }

        if ($companiaId) {
            $query->whereHas('unidad', fn($q) => $q->where('compania_id', $companiaId));
        }

        $vouchers = $query->orderBy('fecha_carga', 'asc')->get();

        // ── Resumen ejecutivo ──────────────────────────────────────────
        $totalGasto   = $vouchers->sum('total');
        $totalLitros  = $vouchers->sum('litros');
        $totalVouchers = $vouchers->count();
        $precioPromedio = $totalLitros > 0
            ? round($totalGasto / $totalLitros)
            : 0;

        // Gasto por mes (para gráfico evolución)
        $gastoPorMes = $vouchers->groupBy(fn($v) => $v->fecha_carga->month)
            ->map(fn($group) => [
                'total'  => $group->sum('total'),
                'litros' => $group->sum('litros'),
                'count'  => $group->count(),
            ]);

        // Gasto por compañía
        $gastoPorCompania = $vouchers->groupBy(fn($v) => $v->unidad->compania->nombre)
            ->map(fn($group) => [
                'total'   => $group->sum('total'),
                'litros'  => $group->sum('litros'),
                'count'   => $group->count(),
            ])->sortByDesc('total');

        // Ranking unidades más costosas
        $rankingUnidades = $vouchers->groupBy(fn($v) => $v->unidad->nombre)
            ->map(fn($group) => [
                'unidad'   => $group->first()->unidad->nombre,
                'compania' => $group->first()->unidad->compania->nombre,
                'total'    => $group->sum('total'),
                'litros'   => $group->sum('litros'),
                'count'    => $group->count(),
            ])->sortByDesc('total')->take(10);

        // Evolución precio promedio por mes
        $precioPorMes = $vouchers->groupBy(fn($v) => $v->fecha_carga->month)
            ->map(fn($group) => $group->sum('litros') > 0
                ? round($group->sum('total') / $group->sum('litros'))
                : 0
            );

        return view('reportes.combustible', compact(
            'companias', 'unidades', 'anios', 'meses',
            'anio', 'mes', 'companiaId',
            'totalGasto', 'totalLitros', 'totalVouchers', 'precioPromedio',
            'gastoPorMes', 'gastoPorCompania', 'rankingUnidades', 'precioPorMes',
            'vouchers'
        ));
    }

    public function guardiasNocturnas(Request $request)
    {
        $companias = Compania::where('activa', true)->orderBy('numero')->get();
        $anios     = range(now()->year, 2025);
        $meses     = [
            1 => 'Enero',    2 => 'Febrero',   3 => 'Marzo',
            4 => 'Abril',    5 => 'Mayo',       6 => 'Junio',
            7 => 'Julio',    8 => 'Agosto',     9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $anio      = $request->anio ?? now()->year;
        $mes       = $request->mes ?? null;
        $companiaId = $request->compania_id ?? null;

        // ── Query base de guardias cerradas ──────────────────────────
        $queryBase = \App\Models\GuardiaNocturna::where('estado', 'cerrada')
            ->whereYear('fecha', $anio);

        if ($mes) {
            $queryBase->whereMonth('fecha', $mes);
        }

        $guardiaIds = $queryBase->pluck('id');

        // ── Query base de companias ───────────────────────────────────
        $queryCompanias = \App\Models\GuardiaNocturnaCompania::whereIn('guardia_nocturna_id', $guardiaIds)
            ->where('sin_reporte', false);

        if ($companiaId) {
            $queryCompanias->where('compania_id', $companiaId);
        }

        $gnCompaniaIds = $queryCompanias->pluck('id');

        // ── 1. Ranking asistencia voluntarios ────────────────────────
        $rankingAsistencia = \App\Models\GuardiaNocturnaVoluntario::whereIn('guardia_nocturna_compania_id', $gnCompaniaIds)
            ->with(['voluntario.compania', 'guardiaCompania.compania'])
            ->get()
            ->groupBy('voluntario_id')
            ->map(fn($regs) => [
                'nombre'   => $regs->first()->voluntario->nombre ?? '—',
                'compania' => $regs->first()->voluntario->compania->nombre ?? '—',
                'total'    => $regs->count(),
            ])
            ->sortByDesc('total')
            ->take(20)
            ->values();

        // ── 2. Oficiales más frecuentes a cargo ───────────────────────
        $rankingOficiales = \App\Models\GuardiaNocturnaCompania::whereIn('id', $gnCompaniaIds)
            ->whereNotNull('oficial_a_cargo_id')
            ->with(['oficialACargo', 'compania'])
            ->get()
            ->groupBy('oficial_a_cargo_id')
            ->map(fn($regs) => [
                'nombre'   => $regs->first()->oficialACargo->nombre ?? '—',
                'compania' => $regs->first()->compania->nombre ?? '—',
                'total'    => $regs->count(),
            ])
            ->sortByDesc('total')
            ->take(20)
            ->values();

        // ── 3. Resumen por compañía ───────────────────────────────────
        $resumenCompanias = \App\Models\GuardiaNocturnaCompania::whereIn('guardia_nocturna_id', $guardiaIds)
            ->with('compania')
            ->get()
            ->groupBy('compania_id')
            ->map(fn($regs) => [
                'compania'         => $regs->first()->compania->nombre ?? '—',
                'numero'           => $regs->first()->compania->numero ?? '—',
                'total_guardias'   => $regs->count(),
                'sin_reporte'      => $regs->where('sin_reporte', true)->count(),
                'con_reporte'      => $regs->where('sin_reporte', false)->count(),
                'promedio_vol'     => round(
                    $regs->where('sin_reporte', false)->map(
                        fn($r) => $r->voluntarios()->count()
                    )->avg() ?? 0, 1
                ),
            ])
            ->sortBy('numero')
            ->values();

        // ── 4. Maquinistas más frecuentes en unidades ─────────────────
        $rankingMaquinistas = \App\Models\GuardiaNocturnaUnidad::whereIn('guardia_nocturna_compania_id', $gnCompaniaIds)
            ->whereNotNull('maquinista_id')
            ->with(['maquinista.compania', 'unidad'])
            ->get()
            ->groupBy('maquinista_id')
            ->map(fn($regs) => [
                'nombre'   => $regs->first()->maquinista->nombre ?? '—',
                'compania' => $regs->first()->maquinista->compania->nombre ?? '—',
                'total'    => $regs->count(),
                'unidades' => $regs->pluck('unidad.nombre')->unique()->implode(', '),
            ])
            ->sortByDesc('total')
            ->take(20)
            ->values();

        // ── 5. Evolución mensual ──────────────────────────────────────
        $evolucionMensual = \App\Models\GuardiaNocturna::where('estado', 'cerrada')
            ->whereYear('fecha', $anio)
            ->with(['companias.voluntarios'])
            ->get()
            ->groupBy(fn($g) => $g->fecha->month)
            ->map(fn($guardias) => [
                'promedio_vol' => round(
                    $guardias->map(fn($g) =>
                        $g->companias->where('sin_reporte', false)->sum(fn($c) => $c->voluntarios->count())
                    )->avg() ?? 0, 1
                ),
            ]);

        foreach (range(1, 12) as $m) {
            if (!isset($evolucionMensual[$m])) {
                $evolucionMensual[$m] = ['promedio_vol' => 0];
            }
        }
        $evolucionMensual = collect($evolucionMensual)->sortKeys();

        $historial = \App\Models\GuardiaNocturna::withCount([
            'companias',
            'companias as sin_reporte_count' => fn($q) => $q->where('sin_reporte', true),
        ])
        ->with('cerradoPor')
        ->when($request->filled('hist_fecha'), fn($q) => $q->whereDate('fecha', $request->hist_fecha))
        ->when(!$request->filled('hist_fecha') && $request->filled('hist_desde'), fn($q) => $q->whereDate('fecha', '>=', $request->hist_desde))
        ->when(!$request->filled('hist_fecha') && $request->filled('hist_hasta'), fn($q) => $q->whereDate('fecha', '<=', $request->hist_hasta))
        ->orderByDesc('fecha')
        ->paginate(20)
        ->withQueryString();
        
        return view('reportes.guardias_nocturnas', compact(
            'companias', 'anios', 'meses',
            'anio', 'mes', 'companiaId',
            'rankingAsistencia', 'rankingOficiales',
            'resumenCompanias', 'rankingMaquinistas',
            'evolucionMensual', 'historial'
        ));
    }
}