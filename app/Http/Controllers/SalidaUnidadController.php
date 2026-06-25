<?php

namespace App\Http\Controllers;

use App\Models\SalidaUnidad;
use App\Models\Unidad;
use App\Models\ClaveSalida;
use App\Models\Voluntario;
use Illuminate\Http\Request;

class SalidaUnidadController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────

    public function index()
    {
        // Tramo activo por unidad: exactamente un registro con llegada_at = null
        // por unidad (puede ser la raíz o una sobresalida). Se carga salidaPadre
        // para poder acceder a los datos de la raíz cuando el activo es sobresalida.
        $salidasActivas = SalidaUnidad::with([
                'unidad.compania', 'claveSalida', 'oficial', 'voluntario', 'alMando',
                'salidaPadre.claveSalida',
                'sobresalidas.claveSalida', 'sobresalidas.alMando',
            ])
            ->whereNull('llegada_at')
            ->orderBy('salida_at', 'desc')
            ->get();

        // Historial: solo raíces cerradas para no mostrar cada tramo por separado
        $historial = SalidaUnidad::with(['unidad.compania', 'claveSalida', 'oficial', 'voluntario', 'alMando',
                'sobresalidas'])
            ->whereNotNull('llegada_at')
            ->whereNull('salida_padre_id')   // solo raíces en el historial
            ->orderBy('llegada_at', 'desc')  // primero la que llegó más recientemente
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

        $voluntariosAlMando = Voluntario::with('compania')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $conductorPorUnidad = [];

        foreach ($turnosCuarteleros as $turno) {
            foreach ($turno->unidades as $unidad) {
                $conductorPorUnidad[$unidad->id] = [
                    'tipo'   => 'cuartelero',
                    'id'     => $turno->cuartelero->id,
                    'nombre' => $turno->cuartelero->nombre . ' — ' . $turno->cuartelero->compania->nombre,
                ];
            }
        }

        foreach ($turnosMaquinistas as $turno) {
            foreach ($turno->unidades as $unidad) {
                $conductorPorUnidad[$unidad->id] = [
                    'tipo'   => 'maquinista',
                    'id'     => $turno->voluntario->id,
                    'nombre' => $turno->voluntario->nombre . ' — ' . $turno->voluntario->compania->nombre,
                ];
            }
        }

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

    // ─────────────────────────────────────────────────────────────────────
    // STORE (salida individual normal)
    // ─────────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        if ($request->has('unidades') && is_array($request->unidades)) {
            return $this->storeConjunta($request);
        }

        $request->validate([
            'unidad_id'         => 'required|exists:unidades,id',
            'clave_salida_id'   => 'required|exists:claves_salida,id',
            'direccion'         => 'required|string|max:255',
            'km_salida'         => 'nullable|numeric|min:0',
            'oficial_id'        => 'nullable|exists:voluntarios,id',
            'al_mando_id'       => 'nullable|exists:voluntarios,id',   // ← corregido: nullable
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
                ->with('error', 'Esta unidad ya tiene una salida activa sin cerrar. Si la unidad fue derivada a otro destino, use "Registrar Sobresalida".');
        }

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

        // ← corregido: solo validar conflicto si viene un voluntario al mando
        if ($request->al_mando_id) {
            $salidaAlMando = SalidaUnidad::where('al_mando_id', $request->al_mando_id)
                ->whereNull('llegada_at')
                ->first();

            if ($salidaAlMando) {
                $voluntario = Voluntario::find($request->al_mando_id);
                return redirect()->back()
                    ->withInput()
                    ->with('error', "El voluntario {$voluntario->nombre} ya está al mando de la unidad {$salidaAlMando->unidad->nombre} y aún no ha regresado.");
            }
        }

        $voluntarioId   = null;
        $conductorLibre = null;

        if ($request->conductor_id) {
            // Soportamos tres prefijos:
            //   v_{id}      → maquinista EN turno activo con esta unidad
            //   c_{id}      → cuartelero EN turno activo con esta unidad
            //   auth_v_{id} → maquinista autorizado, sin turno activo (flexible)
            //   auth_c_{id} → cuartelero autorizado, sin turno activo (flexible)
            $conductorId = $request->conductor_id;
            $sinTurno    = str_starts_with($conductorId, 'auth_');
            $conductorId = $sinTurno ? substr($conductorId, 5) : $conductorId; // quitar prefijo auth_

            $partes = explode('_', $conductorId, 2);
            $tipo   = $partes[0];
            $id     = $partes[1] ?? null;

            if ($tipo === 'v' && $id) {
                if (!$sinTurno) {
                    // Validar que esté en turno activo con esta unidad
                    $turnoActivo = \App\Models\RegistroTurno::whereNull('salida_at')
                        ->where('voluntario_id', $id)
                        ->whereHas('unidades', fn($q) => $q->where('unidades.id', $request->unidad_id))
                        ->first();

                    if (!$turnoActivo) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'El maquinista seleccionado no está en turno activo con esa unidad.');
                    }
                } else {
                    // Sin turno: verificar que al menos esté autorizado para esta unidad
                    $autorizado = Voluntario::whereHas('roles', fn($q) => $q->where('rol', 'maquinista')->where('activo', true))
                        ->whereHas('unidadesAutorizadas', fn($q) => $q->where('unidades.id', $request->unidad_id))
                        ->where('id', $id)
                        ->where('activo', true)
                        ->exists();

                    if (!$autorizado) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'El maquinista seleccionado no está autorizado para conducir esta unidad.');
                    }
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
                $cuartelero = \App\Models\Cuartelero::find($id);

                if (!$sinTurno) {
                    // Validar que esté en turno activo con esta unidad
                    $turnoActivo = \App\Models\RegistroTurnoCuartelero::whereNull('salida_at')
                        ->where('cuartelero_id', $id)
                        ->whereHas('unidades', fn($q) => $q->where('unidades.id', $request->unidad_id))
                        ->first();

                    if (!$turnoActivo) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'El cuartelero seleccionado no está en turno activo con esa unidad.');
                    }
                } else {
                    // Sin turno: verificar que esté autorizado
                    $autorizado = \App\Models\Cuartelero::where('id', $id)
                        ->where('activo', true)
                        ->whereHas('unidadesAutorizadas', fn($q) => $q->where('unidades.id', $request->unidad_id))
                        ->exists();

                    if (!$autorizado) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'El cuartelero seleccionado no está autorizado para conducir esta unidad.');
                    }
                }

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
            // Sin conductor seleccionado: se registra sin conductor
            $conductorLibre = null;
        }

        $salidaAt = now();
        if ($request->filled('salida_at')) {
            try {
                $horaAjustada = \Carbon\Carbon::parse($request->salida_at);
                if ($horaAjustada->lessThanOrEqualTo(now())) {
                    $salidaAt = $horaAjustada;
                }
            } catch (\Exception $e) {}
        }

        SalidaUnidad::create([
            'unidad_id'         => $request->unidad_id,
            'clave_salida_id'   => $request->clave_salida_id,
            'oficial_id'        => $request->oficial_id,
            'al_mando_id'       => $request->al_mando_id ?: null,   // ← null si no viene
            'voluntario_id'     => $voluntarioId,
            'conductor_libre'   => $conductorLibre,
            'direccion'         => $request->direccion,
            'cantidad_personal' => $request->cantidad_personal,
            'km_salida'         => $request->km_salida,
            'salida_at'         => $salidaAt,
            'observaciones'     => $request->observaciones,
            'salida_padre_id'   => null,
        ]);

        return redirect()->back()->with('success', 'Salida registrada exitosamente.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // SOBRESALIDA — FORMULARIO
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Muestra el formulario para registrar una sobresalida.
     * $salida es la salida activa actual de la unidad (raíz o última sobresalida).
     */
    public function createSobresalida(SalidaUnidad $salida)
    {
        abort_if($salida->llegada_at !== null, 422, 'Esta salida ya tiene llegada registrada.');

        $salida->load(['unidad.compania', 'claveSalida', 'oficial', 'voluntario', 'alMando', 'salidaPadre']);

        $claves = ClaveSalida::where('activa', true)->orderBy('tipo')->orderBy('codigo')->get();

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

        // La raíz real: si la salida actual ya es una sobresalida, el padre ES la raíz.
        $raiz = $salida->esSalidaRaiz() ? $salida : $salida->salidaPadre;

        return view('salidas.sobresalida', compact(
            'salida', 'raiz', 'claves', 'oficiales', 'voluntariosAlMando'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // SOBRESALIDA — GUARDAR
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Registra una sobresalida para una unidad que ya está activa.
     * $salida es la salida activa actual (raíz o última sobresalida).
     */
    public function storeSobresalida(Request $request, SalidaUnidad $salida)
    {
        // La unidad debe tener esta salida activa
        abort_if($salida->llegada_at !== null, 422, 'Esta salida ya está cerrada.');

        $request->validate([
            'clave_salida_id'   => 'required|exists:claves_salida,id',
            'direccion'         => 'required|string|max:255',
            'oficial_id'        => 'nullable|exists:voluntarios,id',
            'al_mando_id'       => 'nullable|exists:voluntarios,id',   // ← corregido: nullable
            'cantidad_personal' => 'nullable|integer|min:1',
            'observaciones'     => 'nullable|string',
            'salida_at'         => 'nullable|date|before_or_equal:now',
        ]);

        $clave = ClaveSalida::find($request->clave_salida_id);

        if ($clave->tipo === 'administrativa' && !$request->oficial_id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Las salidas administrativas requieren un oficial autorizante.');
        }

        if ($request->oficial_id) {
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

        // ← corregido: solo validar conflicto si viene un voluntario al mando
        if ($request->al_mando_id) {
            $salidaAlMando = SalidaUnidad::where('al_mando_id', $request->al_mando_id)
                ->whereNull('llegada_at')
                ->first();

            if ($salidaAlMando) {
                $voluntario = Voluntario::find($request->al_mando_id);
                return redirect()->back()
                    ->withInput()
                    ->with('error', "El voluntario {$voluntario->nombre} ya está al mando de otra salida activa.");
            }
        }

        // Determinar la raíz: si la salida actual ya es sobresalida, su padre es la raíz.
        // Si es raíz, ella misma es la raíz. Esto garantiza que salida_padre_id
        // SIEMPRE apunte al registro original de la cadena.
        $raizId = $salida->esSalidaRaiz() ? $salida->id : $salida->salida_padre_id;

        // Hora de la sobresalida
        $salidaAt = now();
        if ($request->filled('salida_at')) {
            try {
                $horaAjustada = \Carbon\Carbon::parse($request->salida_at);
                if ($horaAjustada->lessThanOrEqualTo(now())) {
                    $salidaAt = $horaAjustada;
                }
            } catch (\Exception $e) {}
        }

        // El km no se pide al operador. Como la unidad no regresó al cuartel,
        // el odómetro no cambió: el nuevo tramo hereda el km_salida del tramo
        // anterior (que a su vez heredó del km_salida original).
        // El km_llegada real lo ingresará el conductor al llegar al cuartel.
        $kmHeredado = $salida->km_salida ?? null;

        // ── Cerrar el tramo anterior ──────────────────────────────────────
        // Se cierra sin km_llegada (queda null) porque no hay odómetro
        // en el momento de la derivación. El km_recorrido tampoco se puede
        // calcular por el mismo motivo — queda como referencia incompleta.
        $salida->update([
            'llegada_at'   => $salidaAt,
            'km_llegada'   => null,
            'km_recorrido' => null,
        ]);

        // Crear el nuevo tramo (sobresalida activa) — conductor heredado
        SalidaUnidad::create([
            'unidad_id'         => $salida->unidad_id,
            'salida_padre_id'   => $raizId,
            'clave_salida_id'   => $request->clave_salida_id,
            'oficial_id'        => $clave->tipo === 'administrativa' ? $request->oficial_id : null,
            'al_mando_id'       => $request->al_mando_id ?: null,   // ← null si no viene
            'voluntario_id'     => $salida->voluntario_id,
            'conductor_libre'   => $salida->conductor_libre,
            'direccion'         => $request->direccion,
            'cantidad_personal' => $request->cantidad_personal ?? $salida->cantidad_personal,
            'km_salida'         => $kmHeredado,
            'salida_at'         => $salidaAt,
            'observaciones'     => $request->observaciones,
            // llegada_at intencionalmente null: este es el tramo activo
        ]);

        return redirect()->route('salidas.index')
            ->with('success', "Sobresalida registrada para {$salida->unidad->nombre}. La unidad continúa en servicio hacia {$request->direccion}.");
    }

    // ─────────────────────────────────────────────────────────────────────
    // SALIDA CONJUNTA
    // ─────────────────────────────────────────────────────────────────────

    private function resolverConductorParaUnidad(string $conductorId, string $conductorLibreInput, int $unidadId): array|\Illuminate\Http\RedirectResponse
    {
        if (!$conductorId) {
            return ['voluntario_id' => null, 'conductor_libre' => $conductorLibreInput ?: null];
        }

        // Soportamos auth_ (autorizado sin turno) igual que en store()
        $sinTurno = str_starts_with($conductorId, 'auth_');
        $conductorId = $sinTurno ? substr($conductorId, 5) : $conductorId;

        $partes = explode('_', $conductorId, 2);
        $tipo   = $partes[0];
        $id     = $partes[1] ?? null;

        if ($tipo === 'v' && $id) {
            if (!$sinTurno) {
                $turnoActivo = \App\Models\RegistroTurno::whereNull('salida_at')
                    ->where('voluntario_id', $id)
                    ->whereHas('unidades', fn($q) => $q->where('unidades.id', $unidadId))
                    ->first();

                if (!$turnoActivo) {
                    return redirect()->back()->withInput()
                        ->with('error', 'El maquinista seleccionado no está en turno activo con esa unidad.');
                }
            } else {
                $autorizado = Voluntario::whereHas('roles', fn($q) => $q->where('rol', 'maquinista')->where('activo', true))
                    ->whereHas('unidadesAutorizadas', fn($q) => $q->where('unidades.id', $unidadId))
                    ->where('id', $id)->where('activo', true)->exists();

                if (!$autorizado) {
                    return redirect()->back()->withInput()
                        ->with('error', 'El maquinista seleccionado no está autorizado para conducir esta unidad.');
                }
            }

            $yaEnSalida = SalidaUnidad::where('voluntario_id', $id)->whereNull('llegada_at')->first();
            if ($yaEnSalida) {
                $vol = Voluntario::find($id);
                return redirect()->back()->withInput()
                    ->with('error', "El maquinista {$vol->nombre} ya tiene una salida activa.");
            }

            return ['voluntario_id' => $id, 'conductor_libre' => null];

        } elseif ($tipo === 'c' && $id) {
            if (!$sinTurno) {
                $turnoActivo = \App\Models\RegistroTurnoCuartelero::whereNull('salida_at')
                    ->where('cuartelero_id', $id)
                    ->whereHas('unidades', fn($q) => $q->where('unidades.id', $unidadId))
                    ->first();

                if (!$turnoActivo) {
                    return redirect()->back()->withInput()
                        ->with('error', 'El cuartelero seleccionado no está en turno activo con esa unidad.');
                }
            } else {
                $autorizado = \App\Models\Cuartelero::where('id', $id)->where('activo', true)
                    ->whereHas('unidadesAutorizadas', fn($q) => $q->where('unidades.id', $unidadId))
                    ->exists();

                if (!$autorizado) {
                    return redirect()->back()->withInput()
                        ->with('error', 'El cuartelero seleccionado no está autorizado para conducir esta unidad.');
                }
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
            'unidades.*.al_mando_id'       => 'nullable|exists:voluntarios,id',   // ← corregido: nullable
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

        foreach ($request->unidades as $u) {
            $activa = SalidaUnidad::where('unidad_id', $u['unidad_id'])->whereNull('llegada_at')->first();
            if ($activa) {
                $unidad = Unidad::find($u['unidad_id']);
                return redirect()->back()->withInput()
                    ->with('error', "La unidad {$unidad->nombre} ya tiene una salida activa sin cerrar.");
            }
        }

        // ← corregido: solo validar conflicto si viene un voluntario al mando
        foreach ($request->unidades as $u) {
            if (!empty($u['al_mando_id'])) {
                $enSalida = SalidaUnidad::where('al_mando_id', $u['al_mando_id'])->whereNull('llegada_at')->first();
                if ($enSalida) {
                    $vol = Voluntario::find($u['al_mando_id']);
                    return redirect()->back()->withInput()
                        ->with('error', "El voluntario {$vol->nombre} ya está al mando de otra salida activa.");
                }
            }
        }

        $salidaAt = now();
        if ($request->filled('salida_at')) {
            try {
                $hora = \Carbon\Carbon::parse($request->salida_at);
                if ($hora->lessThanOrEqualTo(now())) $salidaAt = $hora;
            } catch (\Exception $e) {}
        }

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
                'al_mando_id'       => !empty($u['al_mando_id']) ? $u['al_mando_id'] : null,   // ← null si no viene
                'voluntario_id'     => $conductor['voluntario_id'],
                'conductor_libre'   => $conductor['conductor_libre'],
                'direccion'         => $request->direccion,
                'cantidad_personal' => $u['cantidad_personal'] ?? null,
                'km_salida'         => $u['km_salida'] ?? null,
                'salida_at'         => $salidaAt,
                'observaciones'     => $u['observaciones'] ?? null,
                'salida_padre_id'   => null,
            ]);

            $creadas++;
        }

        return redirect()->back()
            ->with('success', "Salida conjunta registrada: {$creadas} unidades despachadas.");
    }

    // ─────────────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────────────

    public function show(SalidaUnidad $salida)
    {
        $salida->load([
            'unidad.compania', 'claveSalida', 'oficial', 'voluntario', 'alMando',
            'salidaPadre.claveSalida',
            'sobresalidas.claveSalida', 'sobresalidas.alMando',
        ]);
        return view('salidas.show', compact('salida'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // REGISTRAR LLEGADA
    // ─────────────────────────────────────────────────────────────────────

    public function registrarLlegada(Request $request, SalidaUnidad $salida)
    {
        $request->validate([
            'km_llegada' => 'required|numeric|min:0',
            'km_salida'  => 'nullable|numeric|min:0',
            'llegada_at' => 'nullable|date|before_or_equal:now',
        ]);

        // Si el tramo activo es una sobresalida y no tiene km_salida propio,
        // intentar heredarlo de la raíz de la cadena.
        $kmSalidaEfectivo = $salida->km_salida;
        if ($kmSalidaEfectivo === null && $salida->esSobresalida()) {
            $kmSalidaEfectivo = $salida->salidaPadre?->km_salida;
        }
        $kmSalida  = $kmSalidaEfectivo ?? $request->km_salida;
        $kmLlegada = $request->km_llegada;

        $llegadaAt = now();
        if ($request->filled('llegada_at')) {
            try {
                $horaAjustada = \Carbon\Carbon::parse($request->llegada_at);
                if ($horaAjustada->lessThanOrEqualTo(now())) {
                    $llegadaAt = $horaAjustada;
                }
            } catch (\Exception $e) {}
        }

        // km_recorrido de este tramo final (km_salida del tramo → km_llegada final)
        $kmRecorridoTramo = $kmSalida ? ($kmLlegada - $kmSalida) : null;

        // Cerrar el tramo activo
        $salida->update([
            'llegada_at'    => $llegadaAt,
            'km_salida'     => $kmSalida,
            'km_llegada'    => $kmLlegada,
            'km_recorrido'  => $kmRecorridoTramo,
            'observaciones' => $request->observaciones ?? $salida->observaciones,
        ]);

        // Si es sobresalida, propagar el km_llegada final a TODOS los tramos
        // de la cadena (raíz + sobresalidas intermedias) que cerraron sin odómetro.
        // Así todos los registros quedan con el mismo km_llegada y su km_recorrido
        // calculado correctamente — todos comparten el mismo km_salida heredado.
        $kmTotalMsg = null;
        if ($salida->esSobresalida()) {
            $raiz = $salida->salidaPadre;
            if ($raiz) {
                $kmTotalRecorrido = ($raiz->km_salida !== null)
                    ? ($kmLlegada - $raiz->km_salida)
                    : null;

                // Actualizar la raíz
                $raiz->update([
                    'km_llegada'   => $kmLlegada,
                    'km_recorrido' => $kmTotalRecorrido,
                ]);

                // Actualizar todos los tramos intermedios (sobresalidas sin km_llegada)
                // El último tramo (el actual, $salida) ya se actualizó arriba.
                SalidaUnidad::where('salida_padre_id', $raiz->id)
                    ->where('id', '!=', $salida->id)
                    ->whereNull('km_llegada')
                    ->update([
                        'km_llegada'   => $kmLlegada,
                        'km_recorrido' => $kmTotalRecorrido,
                    ]);

                if ($kmTotalRecorrido !== null) {
                    $kmTotalMsg = number_format($kmTotalRecorrido, 0, ',', '.') . ' km totales en la cadena';
                }
            }
        }

        $msg = 'Llegada registrada.';
        if ($kmRecorridoTramo !== null) {
            $msg .= ' Km de este tramo: ' . number_format($kmRecorridoTramo, 0, ',', '.') . ' km.';
        }
        if ($kmTotalMsg) {
            $msg .= ' ' . $kmTotalMsg . '.';
        }

        // Verificar cuartelero para retorno de turno
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

    // ─────────────────────────────────────────────────────────────────────
    // EDIT / UPDATE
    // ─────────────────────────────────────────────────────────────────────

    public function edit(SalidaUnidad $salida)
    {
        abort_if(!$salida->esEditable(), 403, 'El período de edición de 12 horas ha expirado.');

        $salida->load(['unidad.compania', 'claveSalida', 'oficial', 'voluntario', 'alMando']);

        $claves = ClaveSalida::where('activa', true)->orderBy('tipo')->orderBy('codigo')->get();
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

        $kmYConductorEditables = $salida->kmYConductorEditables();

        return view('salidas.edit', compact('salida', 'claves', 'oficiales', 'voluntariosAlMando', 'kmYConductorEditables'));
    }

    public function update(Request $request, SalidaUnidad $salida)
    {
        abort_if(!$salida->esEditable(), 403, 'El período de edición de 12 horas ha expirado.');

        $request->validate([
            'clave_salida_id'   => 'required|exists:claves_salida,id',
            'direccion'         => 'required|string|max:255',
            'oficial_id'        => 'nullable|exists:voluntarios,id',
            'al_mando_id'       => 'nullable|exists:voluntarios,id',   // ← corregido: nullable
            'cantidad_personal' => 'nullable|integer|min:1',
            'km_llegada'        => 'nullable|numeric|min:0',
            'observaciones'     => 'nullable|string',
            'salida_at'         => 'required|date|before_or_equal:now',
        ]);

        $clave = ClaveSalida::find($request->clave_salida_id);

        if ($clave->tipo === 'administrativa' && !$request->oficial_id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Las salidas administrativas requieren un oficial autorizante.');
        }

        // ← corregido: solo validar conflicto si viene un voluntario al mando
        if ($request->al_mando_id) {
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
        }

        $kmLlegada = $request->km_llegada !== null ? (float) $request->km_llegada : null;

        if ($kmLlegada !== null && $salida->km_salida !== null && $kmLlegada < $salida->km_salida) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El km de llegada no puede ser menor al km de salida (' . number_format($salida->km_salida, 0, ',', '.') . ' km).');
        }

        $kmRecorrido = ($salida->km_salida !== null && $kmLlegada !== null)
            ? ($kmLlegada - $salida->km_salida)
            : null;

        $salida->update([
            'clave_salida_id'   => $request->clave_salida_id,
            'oficial_id'        => $clave->tipo === 'administrativa' ? $request->oficial_id : null,
            'al_mando_id'       => $request->al_mando_id ?: null,   // ← null si no viene
            'direccion'         => $request->direccion,
            'cantidad_personal' => $request->cantidad_personal,
            'km_llegada'        => $kmLlegada,
            'km_recorrido'      => $kmRecorrido,
            'salida_at'         => \Carbon\Carbon::parse($request->salida_at),
            'observaciones'     => $request->observaciones,
        ]);

        $msg = 'Salida actualizada correctamente.';
        if ($kmRecorrido !== null) {
            $msg .= ' Km recorridos: ' . number_format($kmRecorrido, 0, ',', '.') . ' km.';
        }

        return redirect()->route('salidas.index')->with('success', $msg);
    }

    // ─────────────────────────────────────────────────────────────────────
    // ÚLTIMO KM (AJAX)
    // ─────────────────────────────────────────────────────────────────────

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

    // ─────────────────────────────────────────────────────────────────────
    // CONDUCTORES AUTORIZADOS PARA UNA UNIDAD (AJAX)
    // Devuelve maquinistas y cuarteleros habilitados para conducir la unidad,
    // independientemente de si tienen turno activo en este momento.
    // ─────────────────────────────────────────────────────────────────────

    public function conductoresAutorizados(Unidad $unidad)
    {
        // Maquinistas autorizados: rol 'maquinista' activo + unidad en su tabla pivote
        // voluntario_unidad (relación unidadesAutorizadas() del modelo Voluntario).
        $maquinistas = Voluntario::with('compania')
            ->whereHas('roles', fn($q) => $q->where('rol', 'maquinista')->where('activo', true))
            ->whereHas('unidadesAutorizadas', fn($q) => $q->where('unidades.id', $unidad->id))
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(fn($v) => [
                'id'     => 'auth_v_' . $v->id,
                'nombre' => $v->nombre . ' — ' . $v->compania->nombre,
                'tipo'   => 'maquinista',
            ]);

        // Cuarteleros autorizados: unidad en su tabla pivote cuartelero_unidad
        // (relación unidadesAutorizadas() del modelo Cuartelero).
        // Cuartelero no tiene columna 'activo'; usa fecha_fin IS NULL (scopeActivos).
        $cuarteleros = \App\Models\Cuartelero::with('compania')
            ->whereHas('unidadesAutorizadas', fn($q) => $q->where('unidades.id', $unidad->id))
            ->activos()   // scope: whereNull('fecha_fin')
            ->orderBy('nombre')
            ->get()
            ->map(fn($c) => [
                'id'     => 'auth_c_' . $c->id,
                'nombre' => '[Cuartelero] ' . $c->nombre . ' — ' . $c->compania->nombre,
                'tipo'   => 'cuartelero',
            ]);

        return response()->json([
            'conductores' => $maquinistas->merge($cuarteleros)->values(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // RETORNO TURNO CUARTELERO
    // ─────────────────────────────────────────────────────────────────────

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
                $turnoMaquinista->update([
                    'salida_at'     => now(),
                    'total_minutos' => $turnoMaquinista->entrada_at->diffInMinutes(now()),
                ]);
            }
        }

        $turno = \App\Models\RegistroTurnoCuartelero::find($datos['turno_id']);
        if ($turno) {
            $turno->unidades()->syncWithoutDetaching($datos['unidades_auth']);
        }

        session()->forget('retomar_turno_cuartelero');

        return redirect()->route('salidas.index')
            ->with('success', 'Turno del cuartelero retomado con todas sus unidades.');
    }
}