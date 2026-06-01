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
use Illuminate\Http\Request;
use Carbon\Carbon;

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

        $salidasActivas = \App\Models\SalidaUnidad::with(['unidad.compania', 'claveSalida', 'voluntario'])
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

        return view('dashboard', compact(
            'totalCompanias', 'totalUnidades',
            'totalVoluntarios', 'totalCuarteleros',
            'enServicio', 'enServicioCuarteleros',
            'turnosActivos', 'turnosActivosCuarteleros',
            'salidasActivas', 'guardiaActual',
            'comandantes', 'libroActivo'
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
}