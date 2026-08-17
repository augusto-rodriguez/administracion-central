<?php

namespace App\Http\Controllers;

use App\Models\Compania;
use App\Models\Voluntario;
use App\Models\Unidad;
use App\Models\Cuartelero;
use App\Models\RegistroTurno;
use App\Models\RegistroTurnoCuartelero;
use App\Models\GuardiaComandante;
use App\Models\Cargo;
use App\Models\VoluntarioCargo;
use App\Models\LibroNovedades;
use App\Models\OficialFueraServicio;
use App\Models\SalidaUnidad;
use App\Models\ClaveSalida;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCompanias   = Compania::where('activa', true)->count();
        $totalUnidades    = Unidad::where('activa', true)->count();
        $totalVoluntarios = Voluntario::where('activo', true)->count();
        $totalCuarteleros = Cuartelero::where('activo', true)->count();

        $enServicio            = RegistroTurno::whereNull('salida_at')->count();
        $enServicioCuarteleros = RegistroTurnoCuartelero::whereNull('salida_at')->count();

        $turnosActivos = RegistroTurno::with(['voluntario.compania', 'unidades'])
            ->whereNull('salida_at')
            ->orderBy('entrada_at', 'desc')
            ->get();

        $turnosActivosCuarteleros = RegistroTurnoCuartelero::with(['cuartelero.compania', 'unidades'])
            ->whereNull('salida_at')
            ->orderBy('entrada_at', 'desc')
            ->get();

        $salidasActivas = SalidaUnidad::with(['unidad.compania', 'claveSalida', 'voluntario'])
            ->whereNull('llegada_at')
            ->orderBy('salida_at', 'desc')
            ->get();

        $guardiaActual = GuardiaComandante::activa();

        // Buscar voluntarios con cargos de comandancia
        $cargosComandante = Cargo::where('tipo', 'general')
            ->where('activo', true)
            ->whereIn('nombre', [
                'Comandante',
                '1er Comandante',
                '2do Comandante',
                '3er Comandante',
                'Segundo Comandante',
                'Tercer Comandante',
            ])
            ->orderBy('orden')
            ->get();

        $comandantes = VoluntarioCargo::whereIn('cargo_id', $cargosComandante->pluck('id'))
            ->whereNull('compania_id')
            ->where('activo', true)
            ->with(['voluntario', 'cargo'])
            ->orderBy('cargo_id')
            ->get();

        $libroActivo = LibroNovedades::where('estado', 'borrador')->latest()->first();

        // Oficiales con cargo general activo
        $oficiales = VoluntarioCargo::where('activo', true)
            ->whereNull('compania_id')
            ->whereHas('cargo', fn($q) => $q->where('tipo', 'general')->where('activo', true))
            ->with(['voluntario.compania', 'cargo'])
            ->orderBy('cargo_id')
            ->get();

        // Oficiales actualmente fuera de servicio
        $fueraServicio = OficialFueraServicio::whereNull('fecha_fin')
            ->orWhere('fecha_fin', '>=', today())
            ->with('voluntario')
            ->get()
            ->keyBy('voluntario_id');

        // Toda la oficialidad activa (generales + de compañía)
        $todosOficiales = VoluntarioCargo::where('activo', true)
            ->whereHas('cargo')
            ->with(['voluntario', 'cargo', 'compania'])
            ->orderBy('compania_id')
            ->orderBy('cargo_id')
            ->get()
            ->unique(fn($vc) => $vc->voluntario_id . '-' . $vc->compania_id);

        // ═══════════════════════════════════════════════════════════════
        // ESTADÍSTICAS DE EMERGENCIAS
        // ═══════════════════════════════════════════════════════════════

        $user = auth()->user();
        $esCapitan = $user->esCapitanCia();
        $companiaId = null;

        if ($esCapitan) {
            $companiaId = $user->voluntario?->compania_id;
        }

        // IDs de claves de emergencia
        $clavesEmergenciaIds = ClaveSalida::where('tipo', 'emergencia')
            ->where('activa', true)
            ->pluck('id');

        // Base query para emergencias
        $emergenciasBaseQuery = function () use ($clavesEmergenciaIds, $companiaId) {
            $query = SalidaUnidad::whereIn('clave_salida_id', $clavesEmergenciaIds);
            if ($companiaId) {
                $query->whereHas('unidad', fn($q) => $q->where('compania_id', $companiaId));
            }
            return $query;
        };

        $ahora = Carbon::now('America/Santiago');
        $inicioMes = $ahora->copy()->startOfMonth();
        $finMes = $ahora->copy()->endOfMonth();

        // 1) Emergencias del mes actual
        $emergenciasMes = $emergenciasBaseQuery()
            ->whereBetween('salida_at', [$inicioMes, $finMes])
            ->count();

        $totalSalidasMes = SalidaUnidad::whereBetween('salida_at', [$inicioMes, $finMes])
            ->when($companiaId, fn($q) => $q->whereHas('unidad', fn($q2) => $q2->where('compania_id', $companiaId)))
            ->count();

        $porcentajeMes = $totalSalidasMes > 0
            ? round(($emergenciasMes / $totalSalidasMes) * 100, 1)
            : 0;

        // 2) Emergencias por semana (últimas 8 semanas)
        $semanasAtras = 8;
        $emergenciasPorSemana = [];

        for ($i = $semanasAtras - 1; $i >= 0; $i--) {
            $inicioSemana = $ahora->copy()->subWeeks($i)->startOfWeek(Carbon::MONDAY);
            $finSemana = $inicioSemana->copy()->endOfWeek(Carbon::SUNDAY);

            $countEmergencias = $emergenciasBaseQuery()
                ->whereBetween('salida_at', [$inicioSemana, $finSemana])
                ->count();

            $totalSemana = SalidaUnidad::whereBetween('salida_at', [$inicioSemana, $finSemana])
                ->when($companiaId, fn($q) => $q->whereHas('unidad', fn($q2) => $q2->where('compania_id', $companiaId)))
                ->count();

            $emergenciasPorSemana[] = [
                'label'       => $inicioSemana->format('d/m'),
                'emergencias' => $countEmergencias,
                'total'       => $totalSemana,
                'porcentaje'  => $totalSemana > 0
                    ? round(($countEmergencias / $totalSemana) * 100, 1)
                    : 0,
            ];
        }

        // 3) Distribución por clave de emergencia (mes actual)
        $distribucionClaves = $emergenciasBaseQuery()
            ->whereBetween('salida_at', [$inicioMes, $finMes])
            ->select('clave_salida_id', DB::raw('COUNT(*) as total'))
            ->groupBy('clave_salida_id')
            ->with('claveSalida')
            ->orderByDesc('total')
            ->get()
            ->map(fn($item) => [
                'codigo'      => $item->claveSalida->codigo,
                'descripcion' => $item->claveSalida->descripcion,
                'total'       => $item->total,
                'porcentaje'  => $emergenciasMes > 0
                    ? round(($item->total / $emergenciasMes) * 100, 1)
                    : 0,
            ]);

        // Datos para los gráficos (JSON)
        $chartData = [
            'mesActual' => [
                'emergencias'  => $emergenciasMes,
                'total'        => $totalSalidasMes,
                'porcentaje'   => $porcentajeMes,
                'nombreMes'    => $ahora->translatedFormat('F Y'),
            ],
            'semanas' => $emergenciasPorSemana,
            'claves'  => $distribucionClaves->toArray(),
        ];

        return view('dashboard', compact(
            'totalCompanias', 'totalUnidades',
            'totalVoluntarios', 'totalCuarteleros',
            'enServicio', 'enServicioCuarteleros',
            'turnosActivos', 'turnosActivosCuarteleros',
            'salidasActivas', 'guardiaActual',
            'comandantes', 'libroActivo',
            'oficiales', 'fueraServicio',
            'todosOficiales',
            'chartData', 'emergenciasMes', 'porcentajeMes'
        ));
    }

    public function guardarGuardia(Request $request)
    {
        $request->validate([
            'voluntario_id' => 'required|exists:voluntarios,id',
        ]);

        $ahora = Carbon::now('America/Santiago');

        $domingoInicio = $ahora->copy()->startOfWeek(Carbon::SUNDAY);

        if ($ahora->dayOfWeek === Carbon::SUNDAY && $ahora->hour < 21) {
            $domingoInicio->subWeek();
        }

        $fechaInicio = $domingoInicio->toDateString();
        $fechaFin    = $domingoInicio->copy()->addDays(7)->toDateString();

        GuardiaComandante::updateOrCreate(
            ['fecha_inicio' => $fechaInicio],
            [
                'voluntario_id' => $request->voluntario_id,
                'fecha_fin'     => $fechaFin,
            ]
        );

        return back()->with('success', 'Comandante de guardia actualizado.');
    }

    public function registrarFueraServicio(Request $request)
    {
        $request->validate([
            'voluntario_id' => 'required|exists:voluntarios,id',
            'fecha_inicio'  => 'required|date',
            'motivo'        => 'nullable|string|max:255',
        ]);

        OficialFueraServicio::create([
            'voluntario_id' => $request->voluntario_id,
            'fecha_inicio'  => $request->fecha_inicio,
            'motivo'        => $request->motivo,
        ]);

        return back()->with('success', 'Oficial registrado como fuera de servicio.');
    }

    public function vuelveServicio($id)
    {
        OficialFueraServicio::findOrFail($id)->update([
            'fecha_fin' => today(),
        ]);

        return back()->with('success', 'Oficial vuelve a servicio activo.');
    }
}