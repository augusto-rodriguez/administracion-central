<?php

namespace App\Http\Controllers;

use App\Models\LibroNovedades;
use App\Models\Unidad;
use App\Models\RegistroTurno;
use App\Models\RegistroTurnoCuartelero;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibroNovedadesController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // INDEX — Lista de libros anteriores + botón para iniciar turno activo
    // ─────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $libros = LibroNovedades::with('operador')
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->paginate(20);

        $libroActivo = LibroNovedades::where('estado', 'borrador')->latest()->first();

        return view('libro_novedades.index', compact('libros', 'libroActivo'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INICIAR — Crea el libro con snapshot automático del estado actual
    // ─────────────────────────────────────────────────────────────────────────
    public function iniciar(Request $request)
    {
        $request->validate([
            'turno'       => 'required|in:dia,noche',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin'    => 'required|date_format:H:i',
        ]);

        $ahora  = Carbon::now('America/Santiago');
        $fecha  = $ahora->toDateString();
        $turno  = $request->input('turno');

        $horaInicio = $request->input('hora_inicio') . ':00';
        $horaFin    = $request->input('hora_fin') . ':00';

        // ── Verificar si ya existe un libro en borrador ──────────────
        $libroBorrador = LibroNovedades::where('estado', 'borrador')->first();

        if ($libroBorrador) {
            return redirect(route('libro-novedades.edit', $libroBorrador))
                ->with('warning', 'Ya hay un turno en curso. Debes cerrarlo antes de iniciar uno nuevo.');
        }

        // ── Verificar duplicado por fecha + turno ────────────────────
        $existe = LibroNovedades::where('fecha', $fecha)
                                ->where('turno', $turno)
                                ->first();

        if ($existe) {
            $destino = $existe->estado === 'borrador'
                ? route('libro-novedades.edit', $existe)
                : route('libro-novedades.show', $existe);

            return redirect($destino)
                ->with('warning', 'Ya existe un libro para ese turno en la fecha de hoy.');
        }

        // ── Verificar si el operador es el mismo del turno anterior ──
        $ultimoLibro = LibroNovedades::where('estado', 'cerrado')
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->first();

        $mismoOperador = $ultimoLibro && $ultimoLibro->operador_id === Auth::id();

        if ($mismoOperador && !$request->boolean('confirmar_mismo_operador')) {
            return back()
                ->withInput()
                ->with('alerta_mismo_operador', true)
                ->with('turno_anterior_fecha', $ultimoLibro->fecha->format('d/m/Y'))
                ->with('turno_anterior_turno', $ultimoLibro->turno_label);
        }

        // ── Snapshot maquinistas activos ─────────────────────────────
        $maquinistas = RegistroTurno::whereNull('salida_at')
            ->with(['voluntario', 'unidades'])
            ->get()
            ->map(fn($r) => [
                'voluntario_id' => $r->voluntario_id,
                'nombre'        => $r->voluntario->nombre ?? '—',
                'unidades'      => $r->unidades->map(fn($u) => [
                    'unidad_id' => $u->id,
                    'nombre'    => $u->nombre,
                    'patente'   => $u->patente,
                ])->values(),
            ])->values()->toArray();

        // ── Snapshot cuarteleros activos ─────────────────────────────
        $cuarteleros = RegistroTurnoCuartelero::whereNull('salida_at')
            ->with(['cuartelero', 'unidades'])
            ->get()
            ->map(fn($r) => [
                'cuartelero_id' => $r->cuartelero_id,
                'nombre'        => $r->cuartelero->nombre ?? '—',
                'unidades'      => $r->unidades->map(fn($u) => [
                    'unidad_id' => $u->id,
                    'nombre'    => $u->nombre,
                    'patente'   => $u->patente,
                ])->values(),
            ])->values()->toArray();

        // ── Snapshot unidades fuera de servicio ──────────────────────
        $fueraServicio = Unidad::where('activa', false)
            ->get()
            ->map(fn($u) => [
                'unidad_id' => $u->id,
                'nombre'    => $u->nombre,
                'patente'   => $u->patente,
                'tipo'      => $u->tipo,
            ])->values()->toArray();

        // ── Operador del turno anterior ──────────────────────────────
        $operadorAnterior = LibroNovedades::where('estado', 'cerrado')
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->first()
            ?->operador
            ?->nombre;

        $libro = LibroNovedades::create([
            'fecha'                              => $fecha,
            'turno'                              => $turno,
            'hora_inicio'                        => $horaInicio,
            'hora_fin'                           => $horaFin,
            'operador_id'                        => Auth::id(),
            'operador_turno_anterior'            => $operadorAnterior,
            'maquinistas_al_recibir'             => $maquinistas,
            'cuarteleros_al_recibir'             => $cuarteleros,
            'unidades_fuera_servicio_al_recibir' => $fueraServicio,
            'estado'                             => 'borrador',
        ]);

        return redirect()->route('libro-novedades.edit', $libro)
            ->with('success', 'Turno iniciado correctamente. Completa los datos del libro.');
    }
    // ─────────────────────────────────────────────────────────────────────────
    // EDIT — Formulario principal del libro en curso
    // ─────────────────────────────────────────────────────────────────────────
    public function edit(LibroNovedades $libroNovedade)
    {
        $libro = $libroNovedade;
        $libro->load('operador', 'cerradoPor');

        return view('libro_novedades.edit', compact('libro'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPDATE — Guarda notas y textos libres (guardado parcial)
    // ─────────────────────────────────────────────────────────────────────────
    public function update(Request $request, LibroNovedades $libroNovedade)
    {
        $libro = $libroNovedade;

        if ($libro->estado === 'cerrado') {
            return back()->with('error', 'Este libro ya fue cerrado y no puede modificarse.');
        }

        $validated = $request->validate([
            'hora_inicio'                    => 'required|date_format:H:i',
            'hora_fin'                       => 'required|date_format:H:i',
            'operador_turno_anterior'        => 'nullable|string|max:255',
            'novedades_cronologicas'         => 'nullable|string',
            'observaciones_telecomunicaciones' => 'nullable|string',
            'novedades_viper'                => 'nullable|string',
        ]);

        // Convertir HH:MM a HH:MM:SS para la BD
        $validated['hora_inicio'] .= ':00';
        $validated['hora_fin']    .= ':00';

        $libro->update($validated);

        return back()->with('success', 'Libro guardado correctamente.');
    }

    public function cerrar(Request $request, LibroNovedades $libroNovedade)
    {
        $libro = $libroNovedade;

        if ($libro->estado === 'cerrado') {
            return back()->with('error', 'Este libro ya está cerrado.');
        }

        $ahora = Carbon::now('America/Santiago');

        // ── Horas (desde request o BD) ───────────────────────────────
        $horaInicio = $request->input('hora_inicio')
            ? $request->input('hora_inicio') . ':00'
            : $libro->hora_inicio;

        $horaFin = $request->input('hora_fin')
            ? $request->input('hora_fin') . ':00'
            : $libro->hora_fin;

        // ── Fecha RAW desde BD (evita reinterpretación de Eloquent) ─────────
        $fechaStr = $libro->getRawOriginal('fecha'); // "2026-03-23"

        // ── Rango en hora local Chile (la BD NO guarda en UTC) ──────────────
        $inicioTurno = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $fechaStr . ' ' . $horaInicio,
            'America/Santiago'
        );

        $finTurno = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $fechaStr . ' ' . $horaFin,
            'America/Santiago'
        );

        // Turno noche (cruza medianoche)
        if ($libro->turno === 'noche' && $horaInicio > $horaFin) {
            $finTurno->addDay();
        }

        // ── Snapshot maquinistas al entregar ─────────────────────────
        $maquinistas = RegistroTurno::whereNull('salida_at')
            ->with(['voluntario', 'unidades'])
            ->get()
            ->map(fn($r) => [
                'voluntario_id' => $r->voluntario_id,
                'nombre'        => $r->voluntario->nombre ?? '—',
                'unidades'      => $r->unidades->map(fn($u) => [
                    'unidad_id' => $u->id,
                    'nombre'    => $u->nombre,
                    'patente'   => $u->patente,
                ])->values(),
            ])->values()->toArray();

        // ── Snapshot cuarteleros al entregar ─────────────────────────
        $cuarteleros = RegistroTurnoCuartelero::whereNull('salida_at')
            ->with(['cuartelero', 'unidades'])
            ->get()
            ->map(fn($r) => [
                'cuartelero_id' => $r->cuartelero_id,
                'nombre'        => $r->cuartelero->nombre ?? '—',
                'unidades'      => $r->unidades->map(fn($u) => [
                    'unidad_id' => $u->id,
                    'nombre'    => $u->nombre,
                    'patente'   => $u->patente,
                ])->values(),
            ])->values()->toArray();

        // ── Unidades fuera de servicio ───────────────────────────────
        $fueraServicio = Unidad::where('activa', false)
            ->get()
            ->map(fn($u) => [
                'unidad_id' => $u->id,
                'nombre'    => $u->nombre,
                'patente'   => $u->patente,
                'tipo'      => $u->tipo,
            ])->values()->toArray();

        // ── Salidas del turno ────────────────────────────────────────
        $salidasDelTurno = \App\Models\SalidaUnidad::whereBetween('salida_at', [$inicioTurno, $finTurno])
            ->with('claveSalida')
            ->get();

        $salidasAdmin      = $salidasDelTurno->filter(fn($s) => $s->claveSalida?->tipo === 'administrativa')->pluck('id')->values()->toArray();
        $salidasEmergencia = $salidasDelTurno->filter(fn($s) => $s->claveSalida?->tipo === 'emergencia')->pluck('id')->values()->toArray();

        // ── FUNCIÓN reutilizable para rango ──────────────────────────
        $filtroTurno = function ($q) use ($inicioTurno, $finTurno) {
            $q->where('entrada_at', '<=', $finTurno)
            ->where(function ($q2) use ($inicioTurno) {
                $q2->whereNull('salida_at')
                    ->orWhere('salida_at', '>=', $inicioTurno);
            });
        };

        // ── MAQUINISTAS (arrastre + dentro turno) ────────────────────
        $puestasMaquinistas = RegistroTurno::where($filtroTurno)
            ->with(['voluntario', 'unidades'])
            ->get()
            ->groupBy('voluntario_id')
            ->map(fn($regs) => $regs->sortByDesc('entrada_at')->first())
            ->map(fn($r) => [
                'tipo'          => 'maquinista',
                'nombre'        => $r->voluntario->nombre ?? '—',
                'entrada_at'    => $r->getRawOriginal('entrada_at'),
                'salida_at'     => $r->getRawOriginal('salida_at'),
                'total_minutos' => $r->total_minutos,
                'es_arrastre'   => Carbon::parse($r->getRawOriginal('entrada_at')) < $inicioTurno,
                'unidades'      => $r->unidades->map(fn($u) => $u->nombre)->implode(', '),
            ])->values()->toArray();

        // ── CUARTELEROS (arrastre + dentro turno) ───────────────────
        $puestasCuarteleros = RegistroTurnoCuartelero::where($filtroTurno)
            ->with(['cuartelero', 'unidades'])
            ->get()
            ->groupBy('cuartelero_id')
            ->map(fn($regs) => $regs->sortByDesc('entrada_at')->first())
            ->map(fn($r) => [
                'tipo'          => 'cuartelero',
                'nombre'        => $r->cuartelero->nombre ?? '—',
                'entrada_at'    => $r->getRawOriginal('entrada_at'),
                'salida_at'     => $r->getRawOriginal('salida_at'),
                'total_minutos' => $r->total_minutos,
                'es_arrastre'   => Carbon::parse($r->getRawOriginal('entrada_at')) < $inicioTurno,
                'unidades'      => $r->unidades->map(fn($u) => $u->nombre)->implode(', '),
            ])->values()->toArray();

        // ── Unir y ordenar ───────────────────────────────────────────
        $todasPuestas = array_merge($puestasMaquinistas, $puestasCuarteleros);

        usort($todasPuestas, fn($a, $b) => strtotime($a['entrada_at']) <=> strtotime($b['entrada_at']));

        // ── Guardar libro ────────────────────────────────────────────
        $libro->update([
            'hora_inicio'                         => $horaInicio,
            'hora_fin'                            => $horaFin,
            // Nueva seccion
            'novedades_cronologicas'              => $request->input('novedades_cronologicas'),
            'observaciones_telecomunicaciones'    => $request->input('observaciones_telecomunicaciones'),
            'novedades_viper'                     => $request->input('novedades_viper'),
            'operador_turno_anterior'             => $request->input('operador_turno_anterior') ?? $libro->operador_turno_anterior,
            'maquinistas_al_entregar'             => $maquinistas,
            'cuarteleros_al_entregar'             => $cuarteleros,
            'unidades_fuera_servicio_al_entregar' => $fueraServicio,
            'puestas_en_servicio'                 => $todasPuestas,
            'salidas_administrativas'             => $salidasAdmin,
            'salidas_emergencia'                  => $salidasEmergencia,
            'estado'                              => 'cerrado',
            'cerrado_por'                         => Auth::id(),
            'cerrado_at'                          => $ahora,
        ]);

        return redirect()->route('libro-novedades.show', $libro)
            ->with('success', 'Turno cerrado correctamente. El libro está listo para exportar.');
    }
    // ─────────────────────────────────────────────────────────────────────────
    // SHOW — Vista de solo lectura / previsualización para exportar
    // ─────────────────────────────────────────────────────────────────────────
    public function show(LibroNovedades $libroNovedade)
    {
        $libro = $libroNovedade->load('operador', 'cerradoPor');

        $idsAdmin      = $libro->salidas_administrativas ?? [];
        $idsEmergencia = $libro->salidas_emergencia ?? [];

        $salidasAdmin = \App\Models\SalidaUnidad::whereIn('id', $idsAdmin)
            ->with('unidad', 'claveSalida', 'oficial')
            ->get();

        $salidasEmergencia = \App\Models\SalidaUnidad::whereIn('id', $idsEmergencia)
            ->with('unidad', 'claveSalida')
            ->get();

        // Puestas en servicio ya vienen como array JSON, no necesitan consulta
        $puestasEnServicio = $libro->puestas_en_servicio ?? [];

        return view('libro_novedades.show', compact(
            'libro', 'salidasAdmin', 'salidasEmergencia', 'puestasEnServicio'
        ));
    }
}