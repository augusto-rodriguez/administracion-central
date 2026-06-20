<?php

namespace App\Http\Controllers;

use App\Models\SalidaUnidad;
use App\Models\Cuartelero;
use App\Models\RegistroTurnoCuartelero;
use App\Models\Unidad;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TurnoCuarteleroController extends Controller
{
    public function index()
    {
        $cuarteleros = Cuartelero::with(['compania', 'unidadesAutorizadas', 'turnoActivo'])
            ->where('activo', true)->orderBy('nombre')->get();

        $turnos = RegistroTurnoCuartelero::with(['cuartelero.compania', 'unidades'])
            ->whereNotNull('salida_at')
            ->orderBy('entrada_at', 'desc')
            ->paginate(20);

        return view('cuarteleros.turnos', compact('cuarteleros', 'turnos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cuartelero_id' => 'required|exists:cuarteleros,id',
            'unidades'      => 'required|array|min:1',
            'unidades.*'    => 'exists:unidades,id',
            'observaciones' => 'nullable|string',
            'entrada_at'    => 'nullable|date|before_or_equal:now',
        ]);

        $cuartelero = Cuartelero::findOrFail($request->cuartelero_id);

        $unidadesEnUso = [];
        foreach ($request->unidades as $unidadId) {

            $salidaActiva = SalidaUnidad::where('unidad_id', $unidadId)
                ->whereNull('llegada_at')->first();

            if ($salidaActiva) {
                $unidad = Unidad::find($unidadId);
                return redirect()->back()
                    ->with('error', "La unidad {$unidad->nombre} está actualmente en una salida. No se puede asignar hasta que regrese al cuartel.");
            }

            $turnoMaquinista = \App\Models\RegistroTurno::whereNull('salida_at')
                ->whereHas('unidades', fn($q) => $q->where('unidades.id', $unidadId))
                ->with('voluntario')
                ->first();

            $turnoCuarteleroOtro = RegistroTurnoCuartelero::whereNull('salida_at')
                ->where('cuartelero_id', '!=', $cuartelero->id)
                ->whereHas('unidades', fn($q) => $q->where('unidades.id', $unidadId))
                ->with('cuartelero')
                ->first();

            if ($turnoMaquinista) {
                $unidad = Unidad::find($unidadId);
                $unidadesEnUso[] = [
                    'unidad_nombre'     => $unidad->nombre,
                    'voluntario_nombre' => $turnoMaquinista->voluntario->nombre . ' (Maquinista)',
                ];
            }

            if ($turnoCuarteleroOtro) {
                $unidad = Unidad::find($unidadId);
                $unidadesEnUso[] = [
                    'unidad_nombre'     => $unidad->nombre,
                    'voluntario_nombre' => $turnoCuarteleroOtro->cuartelero->nombre . ' (Cuartelero)',
                ];
            }
        }

        if (!empty($unidadesEnUso)) {
            session([
                'unidades_en_uso_cuartelero' => $unidadesEnUso,
                'form_data_cuartelero' => [
                    'cuartelero_id' => $request->cuartelero_id,
                    'unidades'      => $request->unidades,
                    'observaciones' => $request->observaciones,
                    'entrada_at'    => $request->entrada_at,
                ],
            ]);
            return redirect()->route('turnos.index');
        }

        if ($cuartelero->turnoActivo) {
            $turnoExistente = $cuartelero->turnoActivo;

            foreach ($turnoExistente->unidades as $unidad) {
                $salidaActiva = SalidaUnidad::where('unidad_id', $unidad->id)
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
                    ->with('error', 'El cuartelero ya tiene todas esas unidades en su turno activo.');
            }

            $turnoExistente->unidades()->attach($unidadesNuevas);
            return redirect()->back()
                ->with('success', 'Unidades agregadas al turno activo del cuartelero.');
        }

        $entradaAt = $request->filled('entrada_at')
            ? Carbon::parse($request->entrada_at)
            : now();

        $turno = RegistroTurnoCuartelero::create([
            'cuartelero_id' => $request->cuartelero_id,
            'entrada_at'    => $entradaAt,
            'observaciones' => $request->observaciones,
        ]);

        $turno->unidades()->attach($request->unidades);

        return redirect()->back()->with('success', 'Turno de cuartelero registrado exitosamente.');
    }

    public function storeConfirmado(Request $request)
    {
        $formData = session('form_data_cuartelero');

        if (empty($formData) || empty($formData['cuartelero_id'])) {
            return redirect()->route('turnos.index')
                ->with('error', 'La sesión expiró, por favor intenta nuevamente.');
        }

        $unidadesNuevas = array_map('intval', $formData['unidades']);

        // Recopilar turnos afectados con unidades cargadas ANTES de modificar nada
        $turnosMaquinistasAfectados = collect();
        $turnosCuarterosAfectados   = collect();

        foreach ($unidadesNuevas as $unidadId) {

            $turnoMaquinista = \App\Models\RegistroTurno::whereNull('salida_at')
                ->whereHas('unidades', fn($q) => $q->where('unidades.id', $unidadId))
                ->first();

            if ($turnoMaquinista && !$turnosMaquinistasAfectados->contains('id', $turnoMaquinista->id)) {
                $turnosMaquinistasAfectados->push($turnoMaquinista->load('unidades'));
            }

            $turnoCuarteleroOtro = RegistroTurnoCuartelero::whereNull('salida_at')
                ->where('cuartelero_id', '!=', $formData['cuartelero_id'])
                ->whereHas('unidades', fn($q) => $q->where('unidades.id', $unidadId))
                ->first();

            if ($turnoCuarteleroOtro && !$turnosCuarterosAfectados->contains('id', $turnoCuarteleroOtro->id)) {
                $turnosCuarterosAfectados->push($turnoCuarteleroOtro->load('unidades'));
            }
        }

        // Procesar maquinistas afectados
        foreach ($turnosMaquinistasAfectados as $turno) {
            $unidadesDelTurno  = $turno->unidades->pluck('id')->toArray();
            $unidadesRestantes = array_diff($unidadesDelTurno, $unidadesNuevas);

            if (empty($unidadesRestantes)) {
                // Turno queda sin conductores activos: cerrar SIN detach para preservar historial
                $turno->update([
                    'salida_at'     => now(),
                    'total_minutos' => $turno->entrada_at->diffInMinutes(now()),
                ]);
            }
            // Si quedan otras unidades el turno sigue activo. Sin detach en ningún caso.
        }

        // Procesar cuarteleros afectados
        foreach ($turnosCuarterosAfectados as $turno) {
            $unidadesDelTurno  = $turno->unidades->pluck('id')->toArray();
            $unidadesRestantes = array_diff($unidadesDelTurno, $unidadesNuevas);

            if (empty($unidadesRestantes)) {
                // Turno queda sin conductores activos: cerrar SIN detach para preservar historial
                $turno->update([
                    'salida_at'     => now(),
                    'total_minutos' => $turno->entrada_at->diffInMinutes(now()),
                ]);
            }
            // Ídem: sin detach, la unidad queda en el historial de ambos turnos.
        }

        $cuartelero = Cuartelero::findOrFail($formData['cuartelero_id']);

        $entradaAt = !empty($formData['entrada_at'])
            ? Carbon::parse($formData['entrada_at'])
            : now();

        if ($cuartelero->turnoActivo) {
            $cuartelero->turnoActivo->unidades()->syncWithoutDetaching($unidadesNuevas);
        } else {
            $turno = RegistroTurnoCuartelero::create([
                'cuartelero_id' => $formData['cuartelero_id'],
                'entrada_at'    => $entradaAt,
                'observaciones' => $formData['observaciones'] ?? null,
            ]);
            $turno->unidades()->attach($unidadesNuevas);
        }

        session()->forget(['unidades_en_uso_cuartelero', 'form_data_cuartelero']);

        return redirect()->route('turnos.index')->with('success', 'Cambio de turno registrado.');
    }

    public function registrarSalida(RegistroTurnoCuartelero $turno)
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

        // ✅ Leer la hora que envió el modal; si no viene, usar now()
        $salidaAt = request()->filled('salida_at')
            ? Carbon::parse(request()->input('salida_at'))
            : now();

        // Seguridad: no permitir hora futura
        if ($salidaAt->gt(now())) {
            $salidaAt = now();
        }

        $turno->update([
            'salida_at'     => $salidaAt,
            'total_minutos' => $turno->entrada_at->diffInMinutes($salidaAt),
        ]);

        return redirect()->back()->with('success', 'Salida registrada correctamente.');
    }

    public function quitarUnidad(RegistroTurnoCuartelero $turno, \App\Models\Unidad $unidad)
    {
        $salidaActiva = SalidaUnidad::where('unidad_id', $unidad->id)
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

    public function edit(RegistroTurnoCuartelero $turno)
    {
        // Solo turnos cerrados y dentro de las 12 horas posteriores al cierre
        if (!$turno->salida_at || $turno->salida_at->lt(now()->subHours(12))) {
            abort(403, 'No se puede editar este turno.');
        }

        $turno->load(['cuartelero.compania', 'unidades']);
        return view('turnos.edit_cuartelero', compact('turno'));
    }

    public function update(Request $request, RegistroTurnoCuartelero $turno)
    {
        // Solo turnos cerrados y dentro de las 12 horas posteriores al cierre
        if (!$turno->salida_at || $turno->salida_at->lt(now()->subHours(12))) {
            abort(403, 'No se puede editar este turno.');
        }

        $request->validate([
            'entrada_at'    => 'required|date|before_or_equal:salida_at',
            'salida_at'     => 'required|date|after_or_equal:entrada_at|before_or_equal:now',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $entrada = Carbon::parse($request->entrada_at);
        $salida  = Carbon::parse($request->salida_at);

        $turno->update([
            'entrada_at'    => $entrada,
            'salida_at'     => $salida,
            'total_minutos' => $entrada->diffInMinutes($salida),
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('turnos.index')
            ->with('success', 'Turno de cuartelero actualizado correctamente.');
    }
}