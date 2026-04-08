<?php

namespace App\Http\Controllers;

use App\Models\SalidaUnidad;
use App\Models\Voluntario;
use App\Models\RegistroTurno;
use App\Models\Unidad;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RegistroTurnoController extends Controller
{
    public function index()
    {
        $voluntarios = \App\Models\Voluntario::with(['compania', 'unidadesAutorizadas.compania'])
            ->where('activo', true)
            ->whereHas('roles', fn($q) => $q->where('rol', 'maquinista')->where('activo', true))
            ->orderBy('nombre')->get();

        $cuarteleros = \App\Models\Cuartelero::with(['compania', 'unidadesAutorizadas.compania'])
            ->where('activo', true)->orderBy('nombre')->get();

        // Historial unificado con una sola paginación
        $maquinistas = \App\Models\RegistroTurno::with(['voluntario.compania', 'unidades'])
            ->whereNotNull('salida_at')
            ->select('*', \DB::raw("'maquinista' as tipo"))
            ->get();

        $cuartelerosTurnos = \App\Models\RegistroTurnoCuartelero::with(['cuartelero.compania', 'unidades'])
            ->whereNotNull('salida_at')
            ->select('*', \DB::raw("'cuartelero' as tipo"))
            ->get();

        // Combinar, ordenar y paginar manualmente
        $historial = $maquinistas->concat($cuartelerosTurnos)
            ->sortByDesc('entrada_at')
            ->values();

        $perPage = 20;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $turnos = new \Illuminate\Pagination\LengthAwarePaginator(
            $historial->forPage($currentPage, $perPage),
            $historial->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        // Mantener compatibilidad con variables que usa la vista
        $turnosCuarteleros = collect(); // vacío, ya no se usa

        $unidadesEnTurno = [];
        foreach ($voluntarios as $voluntario) {
            if ($voluntario->turnoActivo) {
                $unidadesEnTurno[$voluntario->id] = $voluntario->turnoActivo
                    ->unidades->pluck('id')->toArray();
            }
        }

        $unidadesEnTurnoCuartelero = [];
        foreach ($cuarteleros as $cuartelero) {
            if ($cuartelero->turnoActivo) {
                $unidadesEnTurnoCuartelero[$cuartelero->id] = $cuartelero->turnoActivo
                    ->unidades->pluck('id')->toArray();
            }
        }

        return view('turnos.index', compact(
            'voluntarios', 'turnos',
            'cuarteleros', 'turnosCuarteleros',
            'unidadesEnTurno', 'unidadesEnTurnoCuartelero'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'voluntario_id' => 'required|exists:voluntarios,id',
            'unidades'      => 'required|array|min:1',
            'unidades.*'    => 'exists:unidades,id',
            'observaciones' => 'nullable|string',
            'entrada_at'    => 'nullable|date|before_or_equal:now',
        ]);

        $voluntario = \App\Models\Voluntario::findOrFail($request->voluntario_id);

        $unidadesEnUso = [];
        foreach ($request->unidades as $unidadId) {

            $salidaActiva = \App\Models\SalidaUnidad::where('unidad_id', $unidadId)
                ->whereNull('llegada_at')->first();

            if ($salidaActiva) {
                $unidad = \App\Models\Unidad::find($unidadId);
                return redirect()->back()
                    ->with('error', "La unidad {$unidad->nombre} está actualmente en una salida. No se puede asignar hasta que regrese al cuartel.");
            }

            $turnoMaquinista = \App\Models\RegistroTurno::whereNull('salida_at')
                ->where('voluntario_id', '!=', $voluntario->id)
                ->whereHas('unidades', fn($q) => $q->where('unidades.id', $unidadId))
                ->with('voluntario')
                ->first();

            $turnoCuartelero = \App\Models\RegistroTurnoCuartelero::whereNull('salida_at')
                ->whereHas('unidades', fn($q) => $q->where('unidades.id', $unidadId))
                ->with('cuartelero')
                ->first();

            if ($turnoMaquinista) {
                $unidad = \App\Models\Unidad::find($unidadId);
                $unidadesEnUso[] = [
                    'unidad_nombre'     => $unidad->nombre,
                    'voluntario_nombre' => $turnoMaquinista->voluntario->nombre,
                ];
            }

            if ($turnoCuartelero) {
                $unidad = \App\Models\Unidad::find($unidadId);
                $unidadesEnUso[] = [
                    'unidad_nombre'     => $unidad->nombre,
                    'voluntario_nombre' => 'Cuartelero ' . $turnoCuartelero->cuartelero->nombre,
                ];
            }
        }

        if (!empty($unidadesEnUso)) {
            session([
                'unidades_en_uso' => $unidadesEnUso,
                'form_data' => [
                    'voluntario_id' => $request->voluntario_id,
                    'unidades'      => $request->unidades,
                    'observaciones' => $request->observaciones,
                    'entrada_at'    => $request->entrada_at,
                ],
            ]);
            return redirect()->route('turnos.index');
        }

        if ($voluntario->turnoActivo) {
            $turnoExistente = $voluntario->turnoActivo;

            foreach ($turnoExistente->unidades as $unidad) {
                $salidaActiva = \App\Models\SalidaUnidad::where('unidad_id', $unidad->id)
                    ->whereNull('llegada_at')->first();

                if ($salidaActiva) {
                    return redirect()->back()
                        ->with('error', "No se pueden agregar unidades al turno. La unidad {$unidad->nombre} tiene una salida activa sin llegada registrada.");
                }
            }

            $unidadesActuales = $turnoExistente->unidades->pluck('id')->toArray();
            $unidadesNuevas   = array_diff($request->unidades, $unidadesActuales);

            if (empty($unidadesNuevas)) {
                return redirect()->back()
                    ->with('error', 'El voluntario ya tiene todas esas unidades en su turno activo.');
            }

            $turnoExistente->unidades()->attach($unidadesNuevas);
            return redirect()->back()
                ->with('success', 'Unidades agregadas al turno activo del voluntario.');
        }

        $entradaAt = $request->filled('entrada_at')
            ? Carbon::parse($request->entrada_at)
            : now();

        $turno = \App\Models\RegistroTurno::create([
            'voluntario_id' => $request->voluntario_id,
            'entrada_at'    => $entradaAt,
            'observaciones' => $request->observaciones,
        ]);

        $turno->unidades()->attach($request->unidades);

        return redirect()->back()->with('success', 'Turno registrado exitosamente.');
    }

    public function storeConfirmado(Request $request)
    {
        $formData = session('form_data');

        if (empty($formData) || empty($formData['voluntario_id'])) {
            return redirect()->route('turnos.index')
                ->with('error', 'La sesión expiró, por favor intenta nuevamente.');
        }

        $unidadesNuevas = array_map('intval', $formData['unidades']);

        // Recopilar turnos afectados con unidades cargadas ANTES de modificar nada
        $turnosMaquinistasAfectados = collect();
        $turnosCuarterosAfectados   = collect();

        foreach ($unidadesNuevas as $unidadId) {

            $turnoMaquinista = RegistroTurno::whereNull('salida_at')
                ->where('voluntario_id', '!=', $formData['voluntario_id'])
                ->whereHas('unidades', fn($q) => $q->where('unidades.id', $unidadId))
                ->first();

            if ($turnoMaquinista && !$turnosMaquinistasAfectados->contains('id', $turnoMaquinista->id)) {
                $turnosMaquinistasAfectados->push($turnoMaquinista->load('unidades'));
            }

            $turnoCuartelero = \App\Models\RegistroTurnoCuartelero::whereNull('salida_at')
                ->whereHas('unidades', fn($q) => $q->where('unidades.id', $unidadId))
                ->first();

            if ($turnoCuartelero && !$turnosCuarterosAfectados->contains('id', $turnoCuartelero->id)) {
                $turnosCuarterosAfectados->push($turnoCuartelero->load('unidades'));
            }
        }

        // Procesar maquinistas afectados
        foreach ($turnosMaquinistasAfectados as $turno) {
            $unidadesDelTurno  = $turno->unidades->pluck('id')->toArray();
            $unidadesRestantes = array_diff($unidadesDelTurno, $unidadesNuevas);

            if (empty($unidadesRestantes)) {
                // Turno queda vacío: cerrar SIN detach para preservar historial
                $turno->update([
                    'salida_at'     => now(),
                    'total_minutos' => $turno->entrada_at->diffInMinutes(now()),
                ]);
            } else {
                // Turno sigue activo con otras unidades: solo quitar las transferidas
                $unidadesAQuitar = array_intersect($unidadesDelTurno, $unidadesNuevas);
                $turno->unidades()->detach($unidadesAQuitar);
            }
        }

        // Procesar cuarteleros afectados
        foreach ($turnosCuarterosAfectados as $turno) {
            $unidadesDelTurno  = $turno->unidades->pluck('id')->toArray();
            $unidadesRestantes = array_diff($unidadesDelTurno, $unidadesNuevas);

            if (empty($unidadesRestantes)) {
                // Turno queda vacío: cerrar SIN detach para preservar historial
                $turno->update([
                    'salida_at'     => now(),
                    'total_minutos' => $turno->entrada_at->diffInMinutes(now()),
                ]);
            } else {
                // Turno sigue activo con otras unidades: solo quitar las transferidas
                $unidadesAQuitar = array_intersect($unidadesDelTurno, $unidadesNuevas);
                $turno->unidades()->detach($unidadesAQuitar);
            }
        }

        $voluntario = \App\Models\Voluntario::findOrFail($formData['voluntario_id']);

        if ($voluntario->turnoActivo) {
            $voluntario->turnoActivo->unidades()->syncWithoutDetaching($unidadesNuevas);
        } else {
            $this->crearTurno(
                $formData['voluntario_id'],
                $unidadesNuevas,
                $formData['observaciones'] ?? null,
                $formData['entrada_at'] ?? null,
            );
        }

        session()->forget(['unidades_en_uso', 'form_data']);

        return redirect()->route('turnos.index')->with('success', 'Cambio de turno registrado.');
    }

    public function registrarSalida(RegistroTurno $turno)
    {
        $unidadesConSalidaActiva = [];
        foreach ($turno->unidades as $unidad) {
            $salidaActiva = SalidaUnidad::where('unidad_id', $unidad->id)
                ->whereNull('llegada_at')->first();
            if ($salidaActiva) {
                $unidadesConSalidaActiva[] = $unidad->nombre;
            }
        }

        if (!empty($unidadesConSalidaActiva)) {
            return redirect()->back()
                ->with('error', 'No se puede cerrar el turno. Las siguientes unidades tienen salidas activas sin llegada registrada: '
                    . implode(', ', $unidadesConSalidaActiva) . '. Registra la llegada primero.');
        }

        $turno->update([
            'salida_at'     => now(),
            'total_minutos' => $turno->entrada_at->diffInMinutes(now()),
        ]);

        return redirect()->back()->with('success', 'Salida registrada correctamente.');
    }

    public function show(RegistroTurno $turno)
    {
        $turno->load(['voluntario.compania', 'unidades']);
        return view('turnos.show', compact('turno'));
    }

    public function quitarUnidad(RegistroTurno $turno, \App\Models\Unidad $unidad)
    {
        $salidaActiva = \App\Models\SalidaUnidad::where('unidad_id', $unidad->id)
            ->whereNull('llegada_at')->first();

        if ($salidaActiva) {
            return redirect()->back()
                ->with('error', "No se puede quitar la unidad {$unidad->nombre}, tiene una salida activa sin llegada registrada.");
        }

        $turno->load('unidades');
        $cantidadActual = $turno->unidades->count();

        if ($cantidadActual === 1) {
            // Última unidad: cerrar turno SIN detach para preservar historial
            $turno->update([
                'salida_at'     => now(),
                'total_minutos' => $turno->entrada_at->diffInMinutes(now()),
            ]);
            return redirect()->back()
                ->with('success', "Unidad {$unidad->nombre} removida. El turno fue cerrado al quedar sin unidades.");
        }

        $turno->unidades()->detach($unidad->id);
        return redirect()->back()
            ->with('success', "Unidad {$unidad->nombre} removida del turno.");
    }

    private function crearTurno($voluntarioId, $unidades, $observaciones = null, $entradaAt = null)
    {
        $turno = RegistroTurno::create([
            'voluntario_id' => $voluntarioId,
            'entrada_at'    => $entradaAt ? Carbon::parse($entradaAt) : now(),
            'observaciones' => $observaciones,
        ]);

        $turno->unidades()->attach($unidades);

        return $turno;
    }
}