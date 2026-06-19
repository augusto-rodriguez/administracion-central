<?php

namespace App\Http\Controllers;

use App\Models\SalidaUnidad;
use App\Models\Unidad;
use App\Models\ClaveSalida;
use App\Models\Voluntario;
use Illuminate\Http\Request;

class SalidaUnidadController extends Controller
{
    public function index()
    {
        $salidasActivas = SalidaUnidad::with(['unidad.compania', 'claveSalida', 'oficial', 'voluntario', 'alMando'])
            ->whereNull('llegada_at')
            ->orderBy('salida_at', 'desc')
            ->get();

        $historial = SalidaUnidad::with(['unidad.compania', 'claveSalida', 'oficial', 'voluntario', 'alMando'])
            ->whereNotNull('llegada_at')
            ->orderBy('salida_at', 'desc')
            ->paginate(20);

        $unidades = Unidad::with('compania')->where('activa', true)->orderBy('nombre')->get();
        $claves   = ClaveSalida::where('activa', true)->orderBy('tipo')->orderBy('codigo')->get();

        $oficiales = Voluntario::with('compania')
            ->whereHas('roles', fn($q) => $q->where('rol', 'oficial')
                ->where('activo', true)
                ->where('puede_autorizar_salidas', true))
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $turnosMaquinistas = \App\Models\RegistroTurno::with(['voluntario.compania', 'unidades'])
            ->whereNull('salida_at')->get();

        $turnosCuarteleros = \App\Models\RegistroTurnoCuartelero::with(['cuartelero.compania', 'unidades'])
            ->whereNull('salida_at')->get();

        // Voluntarios al mando — todos los voluntarios activos
        $voluntariosAlMando = Voluntario::with('compania')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        // Construir mapa unidad_id => conductor para autocompletar.
        // Un maquinista en turno activo con una unidad tiene prioridad sobre
        // un cuartelero que también la tenga asignada (puede ocurrir ahora que
        // las unidades se preservan en ambos turnos al hacer un cambio de conductor).
        $conductorPorUnidad = [];

        // Primero cuarteleros (menor prioridad)
        foreach ($turnosCuarteleros as $turno) {
            foreach ($turno->unidades as $unidad) {
                $conductorPorUnidad[$unidad->id] = [
                    'tipo'   => 'cuartelero',
                    'id'     => $turno->cuartelero->id,
                    'nombre' => $turno->cuartelero->nombre . ' — ' . $turno->cuartelero->compania->nombre,
                ];
            }
        }

        // Luego maquinistas (mayor prioridad: sobreescriben al cuartelero si comparten unidad)
        foreach ($turnosMaquinistas as $turno) {
            foreach ($turno->unidades as $unidad) {
                $conductorPorUnidad[$unidad->id] = [
                    'tipo'   => 'maquinista',
                    'id'     => $turno->voluntario->id,
                    'nombre' => $turno->voluntario->nombre . ' — ' . $turno->voluntario->compania->nombre,
                ];
            }
        }

        // Lista de conductores disponibles para el select
        $conductores = collect();
        foreach ($turnosMaquinistas as $turno) {
            $conductores->push([
                'tipo'   => 'maquinista',
                'id'     => 'v_' . $turno->voluntario->id,
                'nombre' => $turno->voluntario->nombre . ' — ' . $turno->voluntario->compania->nombre,
            ]);
        }
        foreach ($turnosCuarteleros as $turno) {
            $conductores->push([
                'tipo'   => 'cuartelero',
                'id'     => 'c_' . $turno->cuartelero->id,
                'nombre' => '[Cuartelero] ' . $turno->cuartelero->nombre . ' — ' . $turno->cuartelero->compania->nombre,
            ]);
        }

        return view('salidas.index', compact(
            'salidasActivas', 'historial',
            'unidades', 'claves', 'oficiales',
            'conductores', 'conductorPorUnidad',
            'voluntariosAlMando'
        ));
    }

    public function store(Request $request)
    {
        // ── Modo salida conjunta: delegar al método especializado ──
        if ($request->has('unidades') && is_array($request->unidades)) {
            return $this->storeConjunta($request);
        }

        $request->validate([
            'unidad_id'         => 'required|exists:unidades,id',
            'clave_salida_id'   => 'required|exists:claves_salida,id',
            'direccion'         => 'required|string|max:255',
            'km_salida'         => 'nullable|numeric|min:0',
            'oficial_id'        => 'nullable|exists:voluntarios,id',
            'al_mando_id'       => 'required|exists:voluntarios,id',
            'conductor_id'      => 'nullable|string',
            'conductor_libre'   => 'nullable|string|max:255',
            'cantidad_personal' => 'nullable|integer|min:1',
            'observaciones'     => 'nullable|string',
            'salida_at'         => 'nullable|date|before_or_equal:now',
        ]);

        // Verificar que la unidad no tenga salida activa
        $salidaActiva = SalidaUnidad::where('unidad_id', $request->unidad_id)
            ->whereNull('llegada_at')->first();

        if ($salidaActiva) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Esta unidad ya tiene una salida activa sin cerrar.');
        }

        // Validar oficial obligatorio si la clave es administrativa
        $clave = ClaveSalida::find($request->clave_salida_id);

        if ($clave && $clave->tipo === 'administrativa') {
            if (!$request->oficial_id) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Las salidas administrativas requieren un oficial autorizante.');
            }

            $oficialValido = Voluntario::whereHas('roles', fn($q) => $q->where('rol', 'oficial')
                ->where('activo', true)
                ->where('puede_autorizar_salidas', true))
                ->where('id', $request->oficial_id)
                ->exists();

            if (!$oficialValido) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'El oficial seleccionado no está autorizado para autorizar salidas.');
            }
        }

        // Validar que el voluntario al mando no tenga ya una salida activa
        $salidaAlMando = SalidaUnidad::where('al_mando_id', $request->al_mando_id)
            ->whereNull('llegada_at')
            ->first();

        if ($salidaAlMando) {
            $voluntario = Voluntario::find($request->al_mando_id);
            return redirect()->back()
                ->withInput()
                ->with('error', "El voluntario {$voluntario->nombre} ya está al mando de la unidad {$salidaAlMando->unidad->nombre} y aún no ha regresado.");
        }

        $voluntarioId   = null;
        $conductorLibre = null;

        if ($request->conductor_id) {
            $partes = explode('_', $request->conductor_id, 2);
            $tipo   = $partes[0];
            $id     = $partes[1] ?? null;

            if ($tipo === 'v' && $id) {
                $turnoActivo = \App\Models\RegistroTurno::whereNull('salida_at')
                    ->where('voluntario_id', $id)
                    ->whereHas('unidades', fn($q) => $q->where('unidades.id', $request->unidad_id))
                    ->first();

                if (!$turnoActivo) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'El maquinista seleccionado no está en turno activo con esa unidad.');
                }

                $salidaConductor = SalidaUnidad::where('voluntario_id', $id)
                    ->whereNull('llegada_at')->first();

                if ($salidaConductor) {
                    $voluntario = Voluntario::find($id);
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "El maquinista {$voluntario->nombre} ya tiene una salida activa en la unidad {$salidaConductor->unidad->nombre}.");
                }

                $voluntarioId = $id;

            } elseif ($tipo === 'c' && $id) {
                $turnoActivo = \App\Models\RegistroTurnoCuartelero::whereNull('salida_at')
                    ->where('cuartelero_id', $id)
                    ->whereHas('unidades', fn($q) => $q->where('unidades.id', $request->unidad_id))
                    ->first();

                if (!$turnoActivo) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'El cuartelero seleccionado no está en turno activo con esa unidad.');
                }

                $cuartelero = \App\Models\Cuartelero::find($id);
                $salidaConductor = SalidaUnidad::where('conductor_libre', '[Cuartelero] ' . $cuartelero->nombre)
                    ->whereNull('llegada_at')->first();

                if ($salidaConductor) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "El cuartelero {$cuartelero->nombre} ya tiene una salida activa en la unidad {$salidaConductor->unidad->nombre}.");
                }

                $conductorLibre = '[Cuartelero] ' . $cuartelero->nombre;
            }

        } else {
            $conductorLibre = $request->conductor_libre;
        }

        // ── Determinar hora de salida ──
        // Si el operador ajustó la hora, usar esa; si no, usar la hora actual.
        // Validación extra: si la hora ajustada es futura (por reloj desincronizado),
        // se descarta silenciosamente y se usa now().
        $salidaAt = now();

        if ($request->filled('salida_at')) {
            try {
                $horaAjustada = \Carbon\Carbon::parse($request->salida_at);
                if ($horaAjustada->lessThanOrEqualTo(now())) {
                    $salidaAt = $horaAjustada;
                }
            } catch (\Exception $e) {
                // Si falla el parseo, se mantiene now() — no se interrumpe el registro.
            }
        }

        SalidaUnidad::create([
            'unidad_id'         => $request->unidad_id,
            'clave_salida_id'   => $request->clave_salida_id,
            'oficial_id'        => $request->oficial_id,
            'al_mando_id'       => $request->al_mando_id,
            'voluntario_id'     => $voluntarioId,
            'conductor_libre'   => $conductorLibre,
            'direccion'         => $request->direccion,
            'cantidad_personal' => $request->cantidad_personal,
            'km_salida'         => $request->km_salida,
            'salida_at'         => $salidaAt,
            'observaciones'     => $request->observaciones,
        ]);

        return redirect()->back()->with('success', 'Salida registrada exitosamente.');
    }


    // ─────────────────────────────────────────────────────────────────────
    // SALIDA CONJUNTA
    // ─────────────────────────────────────────────────────────────────────

    private function resolverConductorParaUnidad(string $conductorId, string $conductorLibreInput, int $unidadId): array|\Illuminate\Http\RedirectResponse
    {
        if (!$conductorId) {
            return ['voluntario_id' => null, 'conductor_libre' => $conductorLibreInput ?: null];
        }

        $partes = explode('_', $conductorId, 2);
        $tipo   = $partes[0];
        $id     = $partes[1] ?? null;

        if ($tipo === 'v' && $id) {
            $turnoActivo = \App\Models\RegistroTurno::whereNull('salida_at')
                ->where('voluntario_id', $id)
                ->whereHas('unidades', fn($q) => $q->where('unidades.id', $unidadId))
                ->first();

            if (!$turnoActivo) {
                return redirect()->back()->withInput()
                    ->with('error', 'El maquinista seleccionado no está en turno activo con esa unidad.');
            }

            $yaEnSalida = SalidaUnidad::where('voluntario_id', $id)->whereNull('llegada_at')->first();
            if ($yaEnSalida) {
                $vol = Voluntario::find($id);
                return redirect()->back()->withInput()
                    ->with('error', "El maquinista {$vol->nombre} ya tiene una salida activa.");
            }

            return ['voluntario_id' => $id, 'conductor_libre' => null];

        } elseif ($tipo === 'c' && $id) {
            $turnoActivo = \App\Models\RegistroTurnoCuartelero::whereNull('salida_at')
                ->where('cuartelero_id', $id)
                ->whereHas('unidades', fn($q) => $q->where('unidades.id', $unidadId))
                ->first();

            if (!$turnoActivo) {
                return redirect()->back()->withInput()
                    ->with('error', 'El cuartelero seleccionado no está en turno activo con esa unidad.');
            }

            $cuartelero = \App\Models\Cuartelero::find($id);
            $yaEnSalida = SalidaUnidad::where('conductor_libre', '[Cuartelero] ' . $cuartelero->nombre)
                ->whereNull('llegada_at')->first();

            if ($yaEnSalida) {
                return redirect()->back()->withInput()
                    ->with('error', "El cuartelero {$cuartelero->nombre} ya tiene una salida activa.");
            }

            return ['voluntario_id' => null, 'conductor_libre' => '[Cuartelero] ' . $cuartelero->nombre];
        }

        return ['voluntario_id' => null, 'conductor_libre' => null];
    }

    public function storeConjunta(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'clave_salida_id'              => 'required|exists:claves_salida,id',
            'direccion'                    => 'required|string|max:255',
            'oficial_id'                   => 'nullable|exists:voluntarios,id',
            'salida_at'                    => 'nullable|date|before_or_equal:now',
            'unidades'                     => 'required|array|min:2',
            'unidades.*.unidad_id'         => 'required|exists:unidades,id',
            'unidades.*.al_mando_id'       => 'required|exists:voluntarios,id',
            'unidades.*.conductor_id'      => 'nullable|string',
            'unidades.*.conductor_libre'   => 'nullable|string|max:255',
            'unidades.*.km_salida'         => 'nullable|numeric|min:0',
            'unidades.*.cantidad_personal' => 'nullable|integer|min:1',
            'unidades.*.observaciones'     => 'nullable|string',
        ]);

        $clave = ClaveSalida::find($request->clave_salida_id);

        if ($clave->tipo === 'administrativa' && !$request->oficial_id) {
            return redirect()->back()->withInput()
                ->with('error', 'Las salidas administrativas requieren un oficial autorizante.');
        }

        // Verificar que ninguna unidad tenga salida activa
        foreach ($request->unidades as $u) {
            $activa = SalidaUnidad::where('unidad_id', $u['unidad_id'])->whereNull('llegada_at')->first();
            if ($activa) {
                $unidad = Unidad::find($u['unidad_id']);
                return redirect()->back()->withInput()
                    ->with('error', "La unidad {$unidad->nombre} ya tiene una salida activa sin cerrar.");
            }
        }

        // Verificar que ningún voluntario al mando ya esté en salida activa
        foreach ($request->unidades as $u) {
            $enSalida = SalidaUnidad::where('al_mando_id', $u['al_mando_id'])->whereNull('llegada_at')->first();
            if ($enSalida) {
                $vol = Voluntario::find($u['al_mando_id']);
                return redirect()->back()->withInput()
                    ->with('error', "El voluntario {$vol->nombre} ya está al mando de otra salida activa.");
            }
        }

        // Determinar hora de salida
        $salidaAt = now();
        if ($request->filled('salida_at')) {
            try {
                $hora = \Carbon\Carbon::parse($request->salida_at);
                if ($hora->lessThanOrEqualTo(now())) $salidaAt = $hora;
            } catch (\Exception $e) {}
        }

        // Crear una SalidaUnidad por cada fila del formulario
        $creadas = 0;
        foreach ($request->unidades as $u) {
            $conductor = $this->resolverConductorParaUnidad(
                $u['conductor_id'] ?? '',
                $u['conductor_libre'] ?? '',
                (int) $u['unidad_id']
            );

            if ($conductor instanceof \Illuminate\Http\RedirectResponse) {
                return $conductor;
            }

            SalidaUnidad::create([
                'unidad_id'         => $u['unidad_id'],
                'clave_salida_id'   => $request->clave_salida_id,
                'oficial_id'        => $clave->tipo === 'administrativa' ? $request->oficial_id : null,
                'al_mando_id'       => $u['al_mando_id'],
                'voluntario_id'     => $conductor['voluntario_id'],
                'conductor_libre'   => $conductor['conductor_libre'],
                'direccion'         => $request->direccion,
                'cantidad_personal' => $u['cantidad_personal'] ?? null,
                'km_salida'         => $u['km_salida'] ?? null,
                'salida_at'         => $salidaAt,
                'observaciones'     => $u['observaciones'] ?? null,
            ]);

            $creadas++;
        }

        return redirect()->back()
            ->with('success', "Salida conjunta registrada: {$creadas} unidades despachadas.");
    }

    public function show(SalidaUnidad $salida)
    {
        $salida->load(['unidad.compania', 'claveSalida', 'oficial', 'voluntario', 'alMando']);
        return view('salidas.show', compact('salida'));
    }

    public function registrarLlegada(Request $request, SalidaUnidad $salida)
    {
        $request->validate([
            'km_llegada' => 'required|numeric|min:0',
            'km_salida'  => 'nullable|numeric|min:0',
            'llegada_at' => 'nullable|date|before_or_equal:now',
        ]);

        $kmSalida  = $salida->km_salida ?? $request->km_salida;
        $kmLlegada = $request->km_llegada;

        // Usar la hora enviada si es válida; si no, now()
        $llegadaAt = now();
        if ($request->filled('llegada_at')) {
            try {
                $horaAjustada = \Carbon\Carbon::parse($request->llegada_at);
                if ($horaAjustada->lessThanOrEqualTo(now())) {
                    $llegadaAt = $horaAjustada;
                }
            } catch (\Exception $e) {}
        }

        $salida->update([
            'llegada_at'    => $llegadaAt,
            'km_salida'     => $kmSalida,
            'km_llegada'    => $kmLlegada,
            'km_recorrido'  => $kmSalida ? ($kmLlegada - $kmSalida) : null,
            'observaciones' => $request->observaciones ?? $salida->observaciones,
        ]);

        $msg = 'Llegada registrada.';
        if ($kmSalida) $msg .= ' Km recorridos: ' . number_format($kmLlegada - $kmSalida, 0, ',', '.') . ' km.';

        // Verificar si el conductor es cuartelero y tiene turno activo
        $cuarteleroId = null;

        if ($salida->conductor_libre && str_starts_with($salida->conductor_libre, '[Cuartelero] ')) {
            $nombreCuartelero = str_replace('[Cuartelero] ', '', $salida->conductor_libre);
            $cuartelero = \App\Models\Cuartelero::where('nombre', $nombreCuartelero)->first();
            if ($cuartelero) $cuarteleroId = $cuartelero->id;
        }

        if ($cuarteleroId) {
            $turnoCuartelero = \App\Models\RegistroTurnoCuartelero::whereNull('salida_at')
                ->where('cuartelero_id', $cuarteleroId)
                ->with('unidades')
                ->first();

            if ($turnoCuartelero) {
                $unidadesAutorizadas   = \App\Models\Cuartelero::find($cuarteleroId)
                    ->unidadesAutorizadas()->pluck('unidades.id')->toArray();

                $unidadesEnTurnoActual = $turnoCuartelero->unidades->pluck('id')->toArray();
                $unidadesFaltantes     = array_diff($unidadesAutorizadas, $unidadesEnTurnoActual);

                $conflictos = [];
                foreach ($unidadesFaltantes as $unidadId) {
                    $turnoMaquinista = \App\Models\RegistroTurno::whereNull('salida_at')
                        ->whereHas('unidades', fn($q) => $q->where('unidades.id', $unidadId))
                        ->with(['voluntario', 'unidades'])
                        ->first();

                    if ($turnoMaquinista) {
                        $unidad = \App\Models\Unidad::find($unidadId);
                        $conflictos[] = [
                            'unidad_id'     => $unidadId,
                            'unidad_nombre' => $unidad->nombre,
                            'turno_id'      => $turnoMaquinista->id,
                            'maquinista'    => $turnoMaquinista->voluntario->nombre,
                        ];
                    }
                }

                if (!empty($conflictos)) {
                    session([
                        'retomar_turno_cuartelero' => [
                            'cuartelero_id' => $cuarteleroId,
                            'turno_id'      => $turnoCuartelero->id,
                            'unidades_auth' => $unidadesAutorizadas,
                            'conflictos'    => $conflictos,
                        ]
                    ]);

                    return redirect()->route('salidas.index')
                        ->with('success', $msg)
                        ->with('sugerir_retomar', true);
                }
            }
        }

        return redirect()->back()->with('success', $msg);
    }

    public function ultimoKm(Unidad $unidad)
    {
        $ultima = SalidaUnidad::where('unidad_id', $unidad->id)
            ->whereNotNull('km_llegada')
            ->orderBy('llegada_at', 'desc')
            ->first();

        return response()->json([
            'km'    => $ultima?->km_llegada,
            'fecha' => $ultima?->llegada_at?->format('d/m/Y H:i'),
        ]);
    }

    public function retornarTurnoCuartelero()
    {
        $datos = session('retomar_turno_cuartelero');

        if (!$datos) {
            return redirect()->route('salidas.index')->with('error', 'Sesión expirada.');
        }

        foreach ($datos['conflictos'] as $conflicto) {
            $turnoMaquinista = \App\Models\RegistroTurno::with('unidades')->find($conflicto['turno_id']);
            if (!$turnoMaquinista) continue;

            $unidadesRestantes = $turnoMaquinista->unidades
                ->pluck('id')
                ->reject(fn($id) => $id == $conflicto['unidad_id']);

            if ($unidadesRestantes->isEmpty()) {
                // Turno queda vacío: cerrar SIN detach para preservar historial
                $turnoMaquinista->update([
                    'salida_at'     => now(),
                    'total_minutos' => $turnoMaquinista->entrada_at->diffInMinutes(now()),
                ]);
            }
            // Si le quedan otras unidades, el turno sigue activo sin tocar nada.
            // La unidad devuelta al cuartelero queda en el historial de ambos conductores.
        }

        $turno = \App\Models\RegistroTurnoCuartelero::find($datos['turno_id']);
        if ($turno) {
            $turno->unidades()->syncWithoutDetaching($datos['unidades_auth']);
        }

        session()->forget('retomar_turno_cuartelero');

        return redirect()->route('salidas.index')
            ->with('success', 'Turno del cuartelero retomado con todas sus unidades.');
    }

    public function edit(SalidaUnidad $salida)
    {
        abort_if(!$salida->esEditable(), 403, 'El período de edición de 12 horas ha expirado.');

        $salida->load(['unidad.compania', 'claveSalida', 'oficial', 'voluntario', 'alMando']);

        $claves   = ClaveSalida::where('activa', true)->orderBy('tipo')->orderBy('codigo')->get();
        $oficiales = Voluntario::with('compania')
            ->whereHas('roles', fn($q) => $q->where('rol', 'oficial')
                ->where('activo', true)
                ->where('puede_autorizar_salidas', true))
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
        $voluntariosAlMando = Voluntario::with('compania')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('salidas.edit', compact('salida', 'claves', 'oficiales', 'voluntariosAlMando'));
    }

    public function update(Request $request, SalidaUnidad $salida)
    {
        abort_if(!$salida->esEditable(), 403, 'El período de edición de 12 horas ha expirado.');

        $request->validate([
            'clave_salida_id'   => 'required|exists:claves_salida,id',
            'direccion'         => 'required|string|max:255',
            'oficial_id'        => 'nullable|exists:voluntarios,id',
            'al_mando_id'       => 'required|exists:voluntarios,id',
            'cantidad_personal' => 'nullable|integer|min:1',
            'observaciones'     => 'nullable|string',
            'salida_at'         => 'required|date|before_or_equal:now',
        ]);

        $clave = ClaveSalida::find($request->clave_salida_id);

        if ($clave->tipo === 'administrativa' && !$request->oficial_id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Las salidas administrativas requieren un oficial autorizante.');
        }

        // Verificar al mando no tiene otra salida activa (excepto esta misma)
        $salidaAlMando = SalidaUnidad::where('al_mando_id', $request->al_mando_id)
            ->whereNull('llegada_at')
            ->where('id', '!=', $salida->id)
            ->first();

        if ($salidaAlMando) {
            $voluntario = Voluntario::find($request->al_mando_id);
            return redirect()->back()
                ->withInput()
                ->with('error', "El voluntario {$voluntario->nombre} ya está al mando de otra unidad activa.");
        }

        $salida->update([
            'clave_salida_id'   => $request->clave_salida_id,
            'oficial_id'        => $clave->tipo === 'administrativa' ? $request->oficial_id : null,
            'al_mando_id'       => $request->al_mando_id,
            'direccion'         => $request->direccion,
            'cantidad_personal' => $request->cantidad_personal,
            'salida_at'         => \Carbon\Carbon::parse($request->salida_at),
            'observaciones'     => $request->observaciones,
        ]);

        return redirect()->route('salidas.index')
            ->with('success', 'Salida actualizada correctamente.');
    }
}