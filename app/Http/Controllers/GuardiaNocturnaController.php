<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GuardiaNocturna;
use App\Models\GuardiaNocturnaCompania;
use App\Models\GuardiaNocturnaVoluntario;
use App\Models\GuardiaNocturnaUnidad;
use App\Models\Compania;
use App\Models\Voluntario;
use App\Models\Cuartelero;
use App\Models\RegistroTurno;
use App\Models\RegistroTurnoCuartelero;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;  

class GuardiaNocturnaController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = GuardiaNocturna::withCount([
                'companias',
                'companias as sin_reporte_count' => fn($q) => $q->where('sin_reporte', true),
            ])
            ->with('cerradoPor')
            ->orderByDesc('fecha');

        if ($request->filled('fecha')) {
            // Fecha exacta tiene prioridad
            $query->whereDate('fecha', $request->fecha);
        } else {
            if ($request->filled('desde')) {
                $query->whereDate('fecha', '>=', $request->desde);
            }
            if ($request->filled('hasta')) {
                $query->whereDate('fecha', '<=', $request->hasta);
            }
        }

        $guardias   = $query->paginate(20)->withQueryString();
        $guardiaHoy = GuardiaNocturna::activa();

        return view('guardias_nocturnas.index', compact('guardias', 'guardiaHoy'));
    }

    // ─────────────────────────────────────────────────────────────────
    // INICIAR
    // ─────────────────────────────────────────────────────────────────
    public function iniciar()
    {
        $existe = GuardiaNocturna::whereDate('fecha', today())->first();

        if ($existe) {
            $ruta = $existe->esCerrada()
                ? route('guardias-nocturnas.show', $existe)
                : route('guardias-nocturnas.edit', $existe);

            return redirect($ruta)
                ->with('warning', 'Ya existe una guardia nocturna para hoy.');
        }

        $guardia = GuardiaNocturna::create([
            'fecha'  => today(),
            'estado' => 'abierta',
        ]);

        return redirect()->route('guardias-nocturnas.edit', $guardia)
            ->with('success', 'Guardia nocturna iniciada.');
    }

    // ─────────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────────
    public function edit(GuardiaNocturna $guardia)
    {
        if ($guardia->esCerrada()) {
            return redirect()->route('guardias-nocturnas.show', $guardia)
                ->with('warning', 'Esta guardia ya fue cerrada.');
        }

        // ← Agregar cuarteleros y voluntarios al with
        $companias = Compania::where('activa', true)
            ->with([
                'voluntarios' => fn($q) => $q->where('activo', true)->orderBy('nombre'),
                'cuarteleros' => fn($q) => $q->where('activo', true)->orderBy('nombre'),
            ])
            ->orderBy('numero')
            ->get();

        $guardia->load([
            'companias.compania',
            'companias.oficialACargo',
            'companias.cuartelero',
            'companias.voluntarios.voluntario',
            'companias.unidades.unidad',
            'companias.unidades.maquinista',
            'companias.unidades.cuartelero',
        ]);

        return view('guardias_nocturnas.edit', compact('guardia', 'companias'));
    }

    // ─────────────────────────────────────────────────────────────────
    // HEREDAR — devuelve JSON con situación actual de la compañía
    // ─────────────────────────────────────────────────────────────────
    public function heredar(GuardiaNocturna $guardia, Compania $compania)
    {
        // Maquinistas activos de esta compañía con sus unidades
        $turnos = RegistroTurno::with(['voluntario', 'unidades'])
            ->whereNull('salida_at')
            ->whereHas('voluntario', fn($q) => $q->where('compania_id', $compania->id))
            ->get();

        // Cuarteleros activos de esta compañía con sus unidades
        $turnosCuarteleros = RegistroTurnoCuartelero::with(['cuartelero', 'unidades'])
            ->whereNull('salida_at')
            ->whereHas('cuartelero', fn($q) => $q->where('compania_id', $compania->id))
            ->get();

        // Unidades de la compañía sin conductor activo
        $unidadesCompania = \App\Models\Unidad::where('compania_id', $compania->id)
            ->where('activa', true)
            ->get();

        // Construir mapa unidad → responsable
        $mapaUnidades = [];

        foreach ($turnos as $turno) {
            foreach ($turno->unidades as $unidad) {
                $mapaUnidades[$unidad->id] = [
                    'unidad_id'     => $unidad->id,
                    'unidad_nombre' => $unidad->nombre,
                    'tipo'          => 'maquinista',
                    'responsable_id'     => $turno->voluntario->id,
                    'responsable_nombre' => $turno->voluntario->nombre,
                ];
            }
        }

        foreach ($turnosCuarteleros as $turno) {
            foreach ($turno->unidades as $unidad) {
                $mapaUnidades[$unidad->id] = [
                    'unidad_id'          => $unidad->id,
                    'unidad_nombre'      => $unidad->nombre,
                    'tipo'               => 'cuartelero',
                    'responsable_id'     => $turno->cuartelero->id,
                    'responsable_nombre' => $turno->cuartelero->nombre,
                ];
            }
        }

        // Agregar unidades sin conductor
        foreach ($unidadesCompania as $unidad) {
            if (!isset($mapaUnidades[$unidad->id])) {
                $mapaUnidades[$unidad->id] = [
                    'unidad_id'          => $unidad->id,
                    'unidad_nombre'      => $unidad->nombre,
                    'tipo'               => null,
                    'responsable_id'     => null,
                    'responsable_nombre' => null,
                ];
            }
        }

        return response()->json([
            'unidades'   => array_values($mapaUnidades),
            'sin_conductor' => collect($mapaUnidades)->whereNull('tipo')->count(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // GUARDAR COMPAÑÍA
    // ─────────────────────────────────────────────────────────────────
    public function guardarCompania(Request $request, GuardiaNocturna $guardia)
    {
        if ($guardia->esCerrada()) {
            return back()->with('error', 'Esta guardia ya fue cerrada.');
        }

        $request->validate([
            'compania_id'       => 'required|exists:companias,id',
            'oficial_a_cargo_id'=> 'nullable|exists:voluntarios,id',
            'cuartelero_id'     => 'nullable|exists:cuarteleros,id',
            'sin_reporte'       => 'boolean',
            'observaciones'     => 'nullable|string',
            'voluntarios'       => 'nullable|array',
            'voluntarios.*'     => 'exists:voluntarios,id',
            'unidades'          => 'nullable|array',
            'unidades.*.unidad_id'      => 'required|exists:unidades,id',
            'unidades.*.maquinista_id'  => 'nullable|exists:voluntarios,id',
            'unidades.*.cuartelero_id'  => 'nullable|exists:cuarteleros,id',
        ]);

        // Crear o actualizar registro de compañía
        $gnCompania = GuardiaNocturnaCompania::updateOrCreate(
            [
                'guardia_nocturna_id' => $guardia->id,
                'compania_id'         => $request->compania_id,
            ],
            [
                'oficial_a_cargo_id' => $request->oficial_a_cargo_id,
                'cuartelero_id'      => $request->cuartelero_id,
                'sin_reporte'        => $request->boolean('sin_reporte'),
                'observaciones'      => $request->observaciones,
            ]
        );

        // Sincronizar voluntarios
        $gnCompania->voluntarios()->delete();
        foreach ($request->voluntarios ?? [] as $voluntarioId) {
            GuardiaNocturnaVoluntario::create([
                'guardia_nocturna_compania_id' => $gnCompania->id,
                'voluntario_id'                => $voluntarioId,
                'hora_ingreso'                 => null, 
            ]);
        }
        // Sincronizar unidades
        $gnCompania->unidades()->delete();
        foreach ($request->unidades ?? [] as $u) {
            GuardiaNocturnaUnidad::create([
                'guardia_nocturna_compania_id' => $gnCompania->id,
                'unidad_id'                    => $u['unidad_id'],
                'maquinista_id'                => $u['maquinista_id'] ?? null,
                'cuartelero_id'                => $u['cuartelero_id'] ?? null,
            ]);
        }

        return back()->with('success', 'Compañía ' . $gnCompania->compania->nombre . ' guardada correctamente.');
    }

    // ─────────────────────────────────────────────────────────────────
    // CERRAR
    // ─────────────────────────────────────────────────────────────────
    public function cerrar(GuardiaNocturna $guardia)
    {
        if ($guardia->esCerrada()) {
            return back()->with('error', 'Esta guardia ya fue cerrada.');
        }

        $guardia->update([
            'estado'      => 'cerrada',
            'cerrado_por' => Auth::id(),
            'cerrado_at'  => now(),
        ]);

        return redirect()->route('guardias-nocturnas.show', $guardia)
            ->with('success', 'Guardia nocturna cerrada correctamente.');
    }

    // ─────────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────────
    public function show(GuardiaNocturna $guardia)
    {
        $guardia->load([
            'companias.compania.voluntarios',  
            'companias.oficialACargo',
            'companias.cuartelero',
            'companias.voluntarios.voluntario',
            'companias.unidades.unidad',
            'companias.unidades.maquinista',
            'companias.unidades.cuartelero',
            'cerradoPor',
        ]);

        return view('guardias_nocturnas.show', compact('guardia'));
    }

    public function agregarVoluntario(Request $request, GuardiaNocturna $guardia)
    {
        $request->validate([
            'compania_id'   => 'required|exists:companias,id',
            'voluntario_id' => 'required|exists:voluntarios,id',
            'hora_ingreso'  => 'nullable|date_format:H:i',
        ]);

        $gnCompania = GuardiaNocturnaCompania::where('guardia_nocturna_id', $guardia->id)
            ->where('compania_id', $request->compania_id)
            ->first();

        if (!$gnCompania) {
            $gnCompania = GuardiaNocturnaCompania::create([
                'guardia_nocturna_id' => $guardia->id,
                'compania_id'         => $request->compania_id,
                'sin_reporte'         => false,
            ]);
        }

        $yaExiste = GuardiaNocturnaVoluntario::where('guardia_nocturna_compania_id', $gnCompania->id)
            ->where('voluntario_id', $request->voluntario_id)
            ->exists();

        if ($yaExiste) {
            return back()->with('warning', 'Este voluntario ya está registrado en la guardia.');
        }

        GuardiaNocturnaVoluntario::create([
            'guardia_nocturna_compania_id' => $gnCompania->id,
            'voluntario_id'                => $request->voluntario_id,
            'hora_ingreso'                 => $request->hora_ingreso ?? null,
        ]);

        $voluntario = \App\Models\Voluntario::find($request->voluntario_id);

        return back()->with('success', "{$voluntario->nombre} agregado a la guardia nocturna.");
    }

    public function agregarObservacion(Request $request, GuardiaNocturna $guardia, GuardiaNocturnaCompania $gnCompania)
    {
        $request->validate([
            'observaciones' => 'required|string|max:1000',
        ]);

        $gnCompania->update([
            'observaciones' => $request->observaciones,
        ]);

        return back()->with('success', 'Observación guardada correctamente.');
    }

    public function exportarPdf(GuardiaNocturna $guardia)
    {
        $guardia->load([
            'companias.compania',
            'companias.oficialACargo',
            'companias.cuartelero',
            'companias.voluntarios.voluntario',
            'companias.unidades.unidad',
            'companias.unidades.maquinista',
            'companias.unidades.cuartelero',
            'cerradoPor',
        ]);

        $pdf = Pdf::loadView('guardias_nocturnas.pdf', compact('guardia'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('guardia_nocturna_' . $guardia->fecha->format('Y-m-d') . '.pdf');
    }
}