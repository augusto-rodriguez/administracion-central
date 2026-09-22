<?php

namespace App\Http\Controllers;

use App\Models\ChecklistInspeccion;
use App\Models\ChecklistPlantilla;
use App\Models\ChecklistConfiguracion;
use App\Models\ChecklistRespuesta;
use App\Models\ChecklistHallazgo;
use App\Models\ChecklistHallazgoFoto;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ChecklistInspeccionController extends Controller
{
    /**
     * Listado de inspecciones del cuartelero logueado.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = ChecklistInspeccion::with(['unidad', 'cuartelero', 'plantilla'])
            ->withCount(['hallazgos as hallazgos_abiertos_count' => function ($q) {
                $q->whereNotIn('estado', ['resuelto', 'verificado']);
            }]);

        // Si es cuartelero, solo ve las suyas
        if ($user->rol === 'cuartelero' && $user->cuartelero_id) {
            $query->where('cuartelero_id', $user->cuartelero_id);
        }

        // Filtros opcionales
        if ($request->filled('unidad_id')) {
            $query->where('unidad_id', $request->unidad_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $inspecciones = $query->orderByDesc('created_at')->paginate(15);

        // Unidades para el filtro
        $unidades = $this->unidadesDelUsuario($user);

        return view('checklist.index', compact('inspecciones', 'unidades'));
    }

    /**
     * Formulario de selección de unidad para nueva inspección.
     */
    public function create()
    {
        $user = Auth::user();
        $unidades = $this->unidadesDelUsuario($user)->load('checklistPlantilla');
        $plantillas = ChecklistPlantilla::activas()->get();

        return view('checklist.create', compact('unidades', 'plantillas'));
    }

    /**
     * Crea la inspección en estado borrador y redirige al formulario de llenado.
     */
    public function store(Request $request)
    {
        $request->validate([
            'unidad_id'    => 'required|exists:unidades,id',
            'plantilla_id' => 'required|exists:checklist_plantillas,id',
        ]);

        $user = Auth::user();
        $cuarteleroId = $user->cuartelero_id;

        // Verificar si ya existe un borrador abierto hoy para esta unidad
        $existeBorrador = ChecklistInspeccion::where('cuartelero_id', $cuarteleroId)
            ->where('unidad_id', $request->unidad_id)
            ->where('estado', 'borrador')
            ->whereDate('fecha', today())
            ->first();

        if ($existeBorrador) {
            // Verificar si el borrador está vencido
            if ($this->inspeccionVencida($existeBorrador)) {
                return redirect()->route('checklist.index')
                    ->with('error', 'Tenías un borrador para esta unidad pero el plazo expiró. Contacta al administrador para reabrirlo.');
            }

            return redirect()->route('checklist.edit', $existeBorrador)
                ->with('info', 'Ya tenías un borrador abierto para esta unidad. Puedes continuar desde aquí.');
        }

        // Verificar límite de inspecciones completadas por unidad por día
        $maxPorDia = (int) ChecklistConfiguracion::obtener('max_inspecciones_unidad_dia', '1');

        $completadasHoy = ChecklistInspeccion::where('unidad_id', $request->unidad_id)
            ->where('estado', 'completado')
            ->whereDate('fecha', today())
            ->count();

        if ($completadasHoy >= $maxPorDia) {
            return redirect()->route('checklist.index')
                ->with('error', "Esta unidad ya tiene {$completadasHoy} inspección(es) completada(s) hoy. El máximo permitido es {$maxPorDia}.");
        }

        $inspeccion = ChecklistInspeccion::create([
            'unidad_id'     => $request->unidad_id,
            'cuartelero_id' => $cuarteleroId,
            'plantilla_id'  => $request->plantilla_id,
            'fecha'         => today(),
            'estado'        => 'borrador',
        ]);

        return redirect()->route('checklist.edit', $inspeccion)
            ->with('success', 'Inspección iniciada. Completa cada sección del checklist.');
    }

    /**
     * Formulario de llenado del checklist (por secciones).
     */
    public function edit(ChecklistInspeccion $inspeccion)
    {
        $this->autorizarAcceso($inspeccion);

        if ($inspeccion->estaCompletado()) {
            return redirect()->route('checklist.show', $inspeccion);
        }

        // Verificar si está vencida (solo bloquea a cuarteleros, admin puede entrar)
        if ($this->inspeccionVencida($inspeccion) && Auth::user()->rol === 'cuartelero') {
            return redirect()->route('checklist.index')
                ->with('error', 'El plazo para completar esta inspección ha expirado. Contacta al administrador para reabrirlo.');
        }

        // Cargar plantilla con secciones activas e ítems activos
        $plantilla = $inspeccion->plantilla->load([
            'secciones' => function ($q) {
                $q->activas()->orderBy('orden');
            },
            'secciones.itemsActivos',
        ]);

        // Respuestas ya guardadas (para autoguardado)
        $respuestasGuardadas = $inspeccion->respuestas->keyBy('item_id');

        return view('checklist.edit', compact('inspeccion', 'plantilla', 'respuestasGuardadas'));
    }

    /**
     * Guardar respuestas parciales (autoguardado por sección).
     */
    public function update(Request $request, ChecklistInspeccion $inspeccion)
    {
        $this->autorizarAcceso($inspeccion);

        if ($inspeccion->estaCompletado()) {
            return back()->with('error', 'Esta inspección ya fue completada y no se puede editar.');
        }

        $request->validate([
            'kilometraje'               => 'nullable|string|max:50',
            'hora_motor'                => 'nullable|string|max:50',
            'hora_bomba'                => 'nullable|string|max:50',
            'hora_ultimo_cambio_aceite' => 'nullable|string|max:100',
            'proxima_mantencion'        => 'nullable|string|max:100',
            'observaciones'             => 'nullable|string|max:5000',
            'respuestas'                => 'nullable|array',
            'respuestas.*'              => 'nullable|string|max:50',
        ]);

        // Actualizar datos operativos
        $inspeccion->update($request->only([
            'kilometraje',
            'hora_motor',
            'hora_bomba',
            'hora_ultimo_cambio_aceite',
            'proxima_mantencion',
            'observaciones',
        ]));

        // Guardar/actualizar respuestas
        if ($request->has('respuestas')) {
            foreach ($request->respuestas as $itemId => $valor) {
                if (is_null($valor)) continue;

                ChecklistRespuesta::updateOrCreate(
                    [
                        'inspeccion_id' => $inspeccion->id,
                        'item_id'       => $itemId,
                    ],
                    ['valor' => $valor]
                );
            }
        }

        // Si es una petición AJAX (autoguardado), responder JSON
        if ($request->ajax()) {
            return response()->json(['message' => 'Guardado']);
        }

        return back()->with('success', 'Progreso guardado correctamente.');
    }

    /**
     * Completar la inspección: validar, generar hallazgos y notificar.
     */
    public function completar(Request $request, ChecklistInspeccion $inspeccion)
    {
        $this->autorizarAcceso($inspeccion);

        if ($inspeccion->estaCompletado()) {
            return back()->with('error', 'Esta inspección ya fue completada.');
        }

        // Verificar si está vencida
        if ($this->inspeccionVencida($inspeccion) && Auth::user()->rol === 'cuartelero') {
            return redirect()->route('checklist.index')
                ->with('error', 'El plazo para completar esta inspección ha expirado. Contacta al administrador para reabrirlo.');
        }

        // Verificar que se hayan respondido todos los ítems activos
        $plantilla = $inspeccion->plantilla->load([
            'secciones' => fn($q) => $q->activas(),
            'secciones.itemsActivos',
        ]);

        $totalItems = $plantilla->secciones->flatMap->itemsActivos->count();
        $totalRespuestas = $inspeccion->respuestas()->count();

        if ($totalRespuestas < $totalItems) {
            $faltantes = $totalItems - $totalRespuestas;
            return back()->with('error', "Faltan {$faltantes} ítems por responder. Revisa todas las secciones.");
        }

        DB::transaction(function () use ($inspeccion) {
            // Marcar como completado
            $inspeccion->completar();

            // Generar hallazgos automáticos
            $this->generarHallazgos($inspeccion);
        });

        return redirect()->route('checklist.show', $inspeccion)
            ->with('success', 'Inspección completada y enviada correctamente.');
    }

    /**
     * Ver inspección completada (solo lectura).
     */
    public function show(ChecklistInspeccion $inspeccion)
    {
        $this->autorizarAcceso($inspeccion);

        $inspeccion->load([
            'unidad',
            'cuartelero',
            'plantilla.secciones' => fn($q) => $q->orderBy('orden'),
            'plantilla.secciones.items' => fn($q) => $q->orderBy('orden'),
            'respuestas',
            'hallazgos.item',
            'hallazgos.fotos',
        ]);

        $respuestasMap = $inspeccion->respuestas->keyBy('item_id');

        return view('checklist.show', compact('inspeccion', 'respuestasMap'));
    }

    /**
     * Subir foto asociada a un hallazgo durante la inspección.
     */
    public function subirFoto(Request $request, ChecklistInspeccion $inspeccion)
    {
        $this->autorizarAcceso($inspeccion);

        $request->validate([
            'foto'        => 'required|image|mimes:jpeg,jpg,png,webp|max:10240',
            'hallazgo_id' => 'nullable|exists:checklist_hallazgos,id',
            'item_id'     => 'required|exists:checklist_items,id',
            'tipo'        => 'nullable|in:problema,resolucion',
        ]);

        // Subir archivo
        $ruta = $request->file('foto')->store(
            'checklist/hallazgos/' . now()->format('Y/m'),
            'public'
        );

        // Si ya existe el hallazgo, adjuntar directamente
        if ($request->filled('hallazgo_id')) {
            ChecklistHallazgoFoto::create([
                'hallazgo_id'     => $request->hallazgo_id,
                'ruta'            => $ruta,
                'nombre_original' => $request->file('foto')->getClientOriginalName(),
                'tipo'            => $request->tipo ?? 'problema',
            ]);
        } else {
            // Guardar temporalmente en sesión para asociar al completar
            $fotosTemp = session()->get("checklist_fotos_{$inspeccion->id}", []);
            $fotosTemp[] = [
                'item_id'         => $request->item_id,
                'ruta'            => $ruta,
                'nombre_original' => $request->file('foto')->getClientOriginalName(),
            ];
            session()->put("checklist_fotos_{$inspeccion->id}", $fotosTemp);
        }

        if ($request->ajax()) {
            return response()->json(['message' => 'Foto subida', 'ruta' => $ruta]);
        }

        return back()->with('success', 'Foto adjuntada correctamente.');
    }

    /**
     * Reabrir una inspección vencida o completada (solo admin).
     */
    public function reabrir(ChecklistInspeccion $inspeccion)
    {
        $inspeccion->update([
            'estado'        => 'borrador',
            'completado_at' => null,
            'fecha'         => today(),
        ]);

        // Eliminar hallazgos generados automáticamente si se reabre
        $inspeccion->hallazgos()->delete();

        return redirect()->route('checklist.index')
            ->with('success', "Inspección #{$inspeccion->id} reabierta. El cuartelero puede completarla nuevamente.");
    }

    // ═════════════════════════════════════════════════════════════════
    // MÉTODOS PRIVADOS
    // ═════════════════════════════════════════════════════════════════

    /**
     * Genera hallazgos automáticos a partir de las respuestas problemáticas.
     */
    private function generarHallazgos(ChecklistInspeccion $inspeccion): void
    {
        $respuestas = $inspeccion->respuestas()->with('item.seccion')->get();
        $fotosTemp  = session()->pull("checklist_fotos_{$inspeccion->id}", []);

        foreach ($respuestas as $respuesta) {
            $severidad = $respuesta->calcularSeveridad();

            if (is_null($severidad)) continue;

            $hallazgo = ChecklistHallazgo::create([
                'inspeccion_id' => $inspeccion->id,
                'item_id'       => $respuesta->item_id,
                'severidad'     => $severidad,
                'descripcion'   => null,
                'estado'        => 'abierto',
                'notificado'    => false,
            ]);

            // Asociar fotos temporales que correspondan a este ítem
            foreach ($fotosTemp as $foto) {
                if ($foto['item_id'] == $respuesta->item_id) {
                    ChecklistHallazgoFoto::create([
                        'hallazgo_id'     => $hallazgo->id,
                        'ruta'            => $foto['ruta'],
                        'nombre_original' => $foto['nombre_original'],
                        'tipo'            => 'problema',
                    ]);
                }
            }
        }

        // Disparar notificaciones por correo
        $this->notificarHallazgos($inspeccion);
    }

    /**
     * Envía correos de notificación según la severidad de los hallazgos.
     */
    private function notificarHallazgos(ChecklistInspeccion $inspeccion): void
    {
        $hallazgos = $inspeccion->hallazgos()
            ->pendientesNotificacion()
            ->with('item.seccion')
            ->get();

        if ($hallazgos->isEmpty()) return;

        // Obtener emails configurados
        $emailsRaw = ChecklistConfiguracion::obtener('emails_notificacion_hallazgos', '');
        $emails = array_filter(array_map('trim', explode(',', $emailsRaw)));

        // Agrupar por severidad
        $criticos = $hallazgos->where('severidad', 'critico');
        $atencion = $hallazgos->where('severidad', 'atencion');
        $info     = $hallazgos->where('severidad', 'info');

        // Verificar si solo notifica críticos
        $soloCriticos = ChecklistConfiguracion::obtener('notificar_solo_criticos', 'no') === 'si';

        if ($soloCriticos && $criticos->isEmpty()) {
            // No hay críticos y está configurado para solo notificar esos, marcar y salir
            $hallazgos->each(fn($h) => $h->update(['notificado' => true, 'notificado_at' => now()]));
            return;
        }

        // Marcar todos como notificados
        $hallazgos->each(fn($h) => $h->update(['notificado' => true, 'notificado_at' => now()]));

        // Enviar correo si hay destinatarios configurados
        if (!empty($emails)) {
            $inspeccion->load(['unidad', 'cuartelero']);

            try {
                Mail::to($emails[0])
                    ->cc(array_slice($emails, 1))
                    ->send(new ChecklistHallazgosMail($inspeccion, $criticos, $atencion, $info));
            } catch (\Exception $e) {
                \Log::error("Error enviando correo de hallazgos: " . $e->getMessage());
            }
        }
    }

    /**
     * Verifica si una inspección borrador ha superado el tiempo límite.
     * Usa horas desde la creación en vez de hora fija, para cubrir turnos nocturnos.
     */
    private function inspeccionVencida(ChecklistInspeccion $inspeccion): bool
    {
        if ($inspeccion->estaCompletado()) return false;

        $horasLimite = (int) ChecklistConfiguracion::obtener('horas_para_completar', '12');
        $fechaLimite = $inspeccion->created_at->copy()->addHours($horasLimite);

        return now()->greaterThan($fechaLimite);
    }

    /**
     * Obtiene las unidades a las que el usuario tiene acceso.
     */
    private function unidadesDelUsuario($user)
    {
        if ($user->rol === 'cuartelero' && $user->cuartelero_id) {
            return Unidad::whereHas('cuarteleros', function ($q) use ($user) {
                $q->where('cuartelero_id', $user->cuartelero_id);
            })->with('checklistPlantilla')->orderBy('nombre')->get();
        }

        return Unidad::with('checklistPlantilla')->orderBy('nombre')->get();
    }

    /**
     * Verifica que el usuario tenga acceso a la inspección.
     */
    private function autorizarAcceso(ChecklistInspeccion $inspeccion): void
    {
        $user = Auth::user();

        if ($user->rol === 'cuartelero' && $user->cuartelero_id !== $inspeccion->cuartelero_id) {
            abort(403, 'No tienes permiso para acceder a esta inspección.');
        }
    }

    /**
     * Eliminar una inspección y todo lo relacionado (solo admin).
     */
    public function destroy(ChecklistInspeccion $inspeccion)
    {
        // Eliminar fotos físicas del storage
        $hallazgos = $inspeccion->hallazgos()->with('fotos')->get();
        foreach ($hallazgos as $hallazgo) {
            foreach ($hallazgo->fotos as $foto) {
                \Storage::disk('public')->delete($foto->ruta);
            }
        }

        // También eliminar fotos temporales en sesión si existieran
        session()->forget("checklist_fotos_{$inspeccion->id}");

        $unidad = $inspeccion->unidad->nombre;
        $fecha = $inspeccion->fecha->format('d/m/Y');

        // El delete en cascada elimina respuestas, hallazgos, fotos y comentarios
        $inspeccion->delete();

        return redirect()->route('checklist.index')
            ->with('success', "Inspección de {$unidad} ({$fecha}) eliminada junto con todos sus hallazgos y datos asociados.");
    }
}