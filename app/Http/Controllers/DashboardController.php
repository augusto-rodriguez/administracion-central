<?php

namespace App\Http\Controllers;

use App\Models\Compania;
use App\Models\Voluntario;
use App\Models\Unidad;
use App\Models\Cuartelero;
use App\Models\RegistroTurno;
use App\Models\RegistroTurnoCuartelero;
use App\Models\GuardiaComandante;
use App\Models\VoluntarioRol;
use Illuminate\Http\Request;   // ← faltaba
use Carbon\Carbon;             // ← faltaba

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
        $comandantes   = VoluntarioRol::where('rol', 'comandante')
            ->where('activo', true)
            ->with('voluntario')
            ->orderBy('rango')
            ->get();

        return view('dashboard', compact(
            'totalCompanias', 'totalUnidades',
            'totalVoluntarios', 'totalCuarteleros',
            'enServicio', 'enServicioCuarteleros',
            'turnosActivos', 'turnosActivosCuarteleros',
            'salidasActivas', 'guardiaActual',
            'comandantes'
        ));
    }

    public function guardarGuardia(Request $request)
    {
        $request->validate([
            'voluntario_id' => 'required|exists:voluntarios,id',
        ]);

        $ahora = Carbon::now('America/Santiago');

        // Calcular inicio de guardia (domingo anterior a las 21:00)
        $domingoInicio = $ahora->copy()->startOfWeek(Carbon::SUNDAY);

        // Si hoy es domingo pero aún no son las 21:00, la guardia activa
        // empezó el domingo anterior
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