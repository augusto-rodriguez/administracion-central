<?php

namespace App\Http\Controllers;

use App\Models\Compania;
use App\Models\RegistroTurno;
use Illuminate\Http\Request;

class EstadisticaController extends Controller
{
    public function index(Request $request)
    {
        $companias = Compania::where('activa', true)->orderBy('numero')->get();

        $anios = range(now()->year, 2024);
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
            4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $anio       = $request->anio ?? now()->year;
        $mes        = $request->mes ?? null;
        $companiaId = $request->compania_id ?? null;

        $query = RegistroTurno::with('voluntario.compania')
            ->whereNotNull('salida_at')
            ->whereYear('entrada_at', $anio);

        if ($mes) $query->whereMonth('entrada_at', $mes);
        if ($companiaId) $query->whereHas('voluntario', fn($q) => $q->where('compania_id', $companiaId));

        $turnos = $query->get();

        $rankingGlobal = $turnos->groupBy('voluntario_id')
            ->map(function($turnosVol) {
                $voluntario = $turnosVol->first()->voluntario;
                return [
                    'nombre'        => $voluntario->nombre,
                    'compania'      => $voluntario->compania->nombre ?? '—',
                    'total_minutos' => $turnosVol->sum('total_minutos'),
                    'total_turnos'  => $turnosVol->count(),
                ];
            })
            ->sortByDesc('total_minutos')
            ->values()
            ->take(10);

        $rankingPorCompania = $companias->map(function($compania) use ($anio, $mes) {
            $query = RegistroTurno::with('voluntario')
                ->whereNotNull('salida_at')
                ->whereYear('entrada_at', $anio)
                ->whereHas('voluntario', fn($q) => $q->where('compania_id', $compania->id));

            if ($mes) $query->whereMonth('entrada_at', $mes);

            $turnosComp = $query->get();

            $mejores = $turnosComp->groupBy('voluntario_id')
                ->map(function($turnosVol) {
                    return [
                        'nombre'        => $turnosVol->first()->voluntario->nombre,
                        'total_minutos' => $turnosVol->sum('total_minutos'),
                        'total_turnos'  => $turnosVol->count(),
                    ];
                })
                ->sortByDesc('total_minutos')
                ->values()
                ->take(5);

            return [
                'compania' => $compania,
                'mejores'  => $mejores,
            ];
        })->filter(fn($c) => $c['mejores']->isNotEmpty());

        $totalHoras       = intdiv($turnos->sum('total_minutos'), 60);
        $totalTurnos      = $turnos->count();
        $totalVoluntarios = $turnos->pluck('voluntario_id')->unique()->count();

        return view('estadisticas.index', compact(
            'companias', 'anios', 'meses', 'anio', 'mes', 'companiaId',
            'rankingGlobal', 'rankingPorCompania',
            'totalHoras', 'totalTurnos', 'totalVoluntarios'
        ));
    }
}