<?php

namespace App\Http\Controllers;

use App\Models\ChecklistPlantilla;
use App\Models\ChecklistSeccion;
use App\Models\ChecklistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChecklistPlantillaController extends Controller
{
    /**
     * Listado de plantillas.
     */
    public function index()
    {
        $plantillas = ChecklistPlantilla::withCount('secciones')
            ->orderByDesc('activa')
            ->orderBy('nombre')
            ->get();

        return view('checklist-plantillas.index', compact('plantillas'));
    }

    /**
     * Formulario de creación de plantilla.
     */
    public function create()
    {
        return view('checklist-plantillas.create');
    }

    /**
     * Guardar nueva plantilla.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string|max:2000',
            'tipo_unidad'  => 'nullable|string|max:50',
        ]);

        $plantilla = ChecklistPlantilla::create($request->only(['nombre', 'descripcion', 'tipo_unidad']));

        // Se redirige directo a la edición para agregar secciones e ítems
        return redirect()->route('checklist-plantillas.edit', $plantilla)
            ->with('success', 'Plantilla creada. Ahora puedes agregar secciones e ítems.');
    }

    public function edit(ChecklistPlantilla $plantilla)
    {
        // Se cuentan respuestas y hallazgos para saber qué ítems tienen historial
        // (esos se desactivan en lugar de borrarse).
        $plantilla->load([
            'secciones' => fn($q) => $q->orderBy('orden'),
            'secciones.items' => fn($q) => $q->withCount(['respuestas', 'hallazgos'])->orderBy('orden'),
        ]);

        $todasUnidades = \App\Models\Unidad::with('checklistPlantilla')
            ->orderBy('nombre')
            ->get();

        $unidadesAsignadas = $todasUnidades
            ->where('checklist_plantilla_id', $plantilla->id)
            ->pluck('id')
            ->toArray();

        return view('checklist-plantillas.edit', compact('plantilla', 'todasUnidades', 'unidadesAsignadas'));
    }

    public function update(Request $request, ChecklistPlantilla $plantilla)
    {
        $request->validate([
            'nombre'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string|max:2000',
            'tipo_unidad'  => 'nullable|string|max:50',
            'activa'       => 'nullable|boolean',
            'unidades_ids' => 'nullable|array',
            'unidades_ids.*' => 'exists:unidades,id',

            'secciones'                        => 'nullable|array',
            'secciones.*.id'                   => 'nullable|exists:checklist_secciones,id',
            'secciones.*.nombre'               => 'required|string|max:255',
            'secciones.*.descripcion'          => 'nullable|string|max:2000',
            'secciones.*.tipo_respuesta'       => 'required|in:nivel,estado,documento',
            'secciones.*.orden'                => 'required|integer|min:0',
            'secciones.*.activa'               => 'nullable|boolean',

            'secciones.*.items'                => 'nullable|array',
            'secciones.*.items.*.id'           => 'nullable|exists:checklist_items,id',
            'secciones.*.items.*.nombre'       => 'required|string|max:255',
            'secciones.*.items.*.es_critico'   => 'nullable|boolean',
            'secciones.*.items.*.orden'        => 'required|integer|min:0',
            'secciones.*.items.*.activo'       => 'nullable|boolean',
        ], [], [
            'secciones.*.nombre'          => 'nombre de la sección',
            'secciones.*.tipo_respuesta'  => 'tipo de respuesta',
            'secciones.*.orden'           => 'orden de la sección',
            'secciones.*.items.*.nombre'  => 'nombre del ítem',
            'secciones.*.items.*.orden'   => 'orden del ítem',
        ]);

        $resumen = [
            'items_eliminados'       => 0,
            'items_desactivados'     => 0,
            'secciones_eliminadas'   => 0,
            'secciones_desactivadas' => 0,
        ];

        DB::transaction(function () use ($request, $plantilla, &$resumen) {
            // ── Datos de la plantilla ──────────────────────────────────
            $plantilla->update([
                'nombre'      => $request->nombre,
                'descripcion' => $request->descripcion,
                'tipo_unidad' => $request->tipo_unidad,
                'activa'      => $request->boolean('activa', true),
            ]);

            // ── Asignación de unidades ─────────────────────────────────
            \App\Models\Unidad::where('checklist_plantilla_id', $plantilla->id)
                ->whereNotIn('id', $request->unidades_ids ?? [])
                ->update(['checklist_plantilla_id' => null]);

            if ($request->has('unidades_ids')) {
                \App\Models\Unidad::whereIn('id', $request->unidades_ids)
                    ->update(['checklist_plantilla_id' => $plantilla->id]);
            }

            // ── Secciones e ítems ──────────────────────────────────────
            $seccionesConservadas = [];

            foreach ($request->input('secciones', []) as $seccionData) {
                // Sección existente: debe pertenecer a ESTA plantilla
                $seccion = !empty($seccionData['id'])
                    ? $plantilla->secciones()->whereKey($seccionData['id'])->firstOrFail()
                    : new ChecklistSeccion(['plantilla_id' => $plantilla->id]);

                $seccion->fill([
                    'nombre'         => $seccionData['nombre'],
                    'descripcion'    => $seccionData['descripcion'] ?? null,
                    'tipo_respuesta' => $seccionData['tipo_respuesta'],
                    'orden'          => $seccionData['orden'],
                    'activa'         => !empty($seccionData['activa']),
                ]);
                $seccion->save();

                $seccionesConservadas[] = $seccion->id;

                $itemsConservados = [];

                foreach ($seccionData['items'] ?? [] as $itemData) {
                    // Ítem existente: debe pertenecer a ESTA sección
                    $item = !empty($itemData['id'])
                        ? ChecklistItem::where('seccion_id', $seccion->id)->whereKey($itemData['id'])->firstOrFail()
                        : new ChecklistItem(['seccion_id' => $seccion->id]);

                    $item->fill([
                        'nombre'     => $itemData['nombre'],
                        'es_critico' => !empty($itemData['es_critico']),
                        'orden'      => $itemData['orden'],
                        'activo'     => !empty($itemData['activo']),
                    ]);
                    $item->save();

                    $itemsConservados[] = $item->id;
                }

                // Ítems que se quitaron del formulario
                $itemsQuitados = ChecklistItem::where('seccion_id', $seccion->id)
                    ->whereNotIn('id', $itemsConservados)
                    ->get();

                foreach ($itemsQuitados as $item) {
                    $this->quitarItem($item, $resumen);
                }
            }

            // Secciones que se quitaron del formulario
            $seccionesQuitadas = ChecklistSeccion::where('plantilla_id', $plantilla->id)
                ->whereNotIn('id', $seccionesConservadas)
                ->get();

            foreach ($seccionesQuitadas as $seccion) {
                $this->quitarSeccion($seccion, $resumen);
            }
        });

        return redirect()->route('checklist-plantillas.edit', $plantilla)
            ->with('success', $this->mensajeResumen($resumen));
    }

    // ═════════════════════════════════════════════════════════════════
    // MÉTODOS PRIVADOS
    // ═════════════════════════════════════════════════════════════════

    /**
     * Quita un ítem. Si ya tiene respuestas o hallazgos, se desactiva para
     * no perder el historial de inspecciones; si no, se elimina.
     */
    private function quitarItem(ChecklistItem $item, array &$resumen): void
    {
        $tieneHistorial = $item->respuestas()->exists() || $item->hallazgos()->exists();

        if ($tieneHistorial) {
            if ($item->activo) {
                $item->update(['activo' => false]);
                $resumen['items_desactivados']++;
            }
            return;
        }

        $item->delete();
        $resumen['items_eliminados']++;
    }

    /**
     * Quita una sección completa con la misma regla: si le quedan ítems con
     * historial, la sección se desactiva; si queda vacía, se elimina.
     */
    private function quitarSeccion(ChecklistSeccion $seccion, array &$resumen): void
    {
        foreach (ChecklistItem::where('seccion_id', $seccion->id)->get() as $item) {
            $this->quitarItem($item, $resumen);
        }

        $quedanItems = ChecklistItem::where('seccion_id', $seccion->id)->exists();

        if ($quedanItems) {
            if ($seccion->activa) {
                $seccion->update(['activa' => false]);
                $resumen['secciones_desactivadas']++;
            }
            return;
        }

        $seccion->delete();
        $resumen['secciones_eliminadas']++;
    }

    private function mensajeResumen(array $r): string
    {
        $detalles = array_filter([
            $r['secciones_eliminadas']   ? "{$r['secciones_eliminadas']} sección(es) eliminada(s)" : null,
            $r['secciones_desactivadas'] ? "{$r['secciones_desactivadas']} sección(es) desactivada(s) por tener historial" : null,
            $r['items_eliminados']       ? "{$r['items_eliminados']} ítem(s) eliminado(s)" : null,
            $r['items_desactivados']     ? "{$r['items_desactivados']} ítem(s) desactivado(s) por tener historial" : null,
        ]);

        $mensaje = 'Plantilla actualizada correctamente.';

        return $detalles ? $mensaje . ' ' . ucfirst(implode(', ', $detalles)) . '.' : $mensaje;
    }
}