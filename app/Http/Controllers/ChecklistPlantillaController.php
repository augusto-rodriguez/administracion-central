<?php

namespace App\Http\Controllers;

use App\Models\ChecklistPlantilla;
use App\Models\ChecklistSeccion;
use App\Models\ChecklistItem;
use Illuminate\Http\Request;

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
     * Guardar nueva plantilla con secciones e ítems.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string|max:2000',
            'tipo_unidad'  => 'nullable|string|max:50',
        ]);

        ChecklistPlantilla::create($request->only(['nombre', 'descripcion', 'tipo_unidad']));

        return redirect()->route('checklist-plantillas.index')
            ->with('success', 'Plantilla creada. Ahora puedes agregar secciones e ítems.');
    }

    public function edit(ChecklistPlantilla $plantilla)
    {
        $plantilla->load([
            'secciones' => fn($q) => $q->orderBy('orden'),
            'secciones.items' => fn($q) => $q->orderBy('orden'),
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
        ]);

        // Actualizar plantilla
        $plantilla->update([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'tipo_unidad' => $request->tipo_unidad,
            'activa'      => $request->boolean('activa', true),
        ]);

        // Actualizar asignación de unidades
        // Primero desasignar las que ya no están
        \App\Models\Unidad::where('checklist_plantilla_id', $plantilla->id)
            ->whereNotIn('id', $request->unidades_ids ?? [])
            ->update(['checklist_plantilla_id' => null]);

        // Asignar las seleccionadas
        if ($request->has('unidades_ids')) {
            \App\Models\Unidad::whereIn('id', $request->unidades_ids)
                ->update(['checklist_plantilla_id' => $plantilla->id]);
        }

        // Actualizar secciones e ítems
        if ($request->has('secciones')) {
            foreach ($request->secciones as $seccionData) {
                $seccion = isset($seccionData['id'])
                    ? ChecklistSeccion::find($seccionData['id'])
                    : new ChecklistSeccion(['plantilla_id' => $plantilla->id]);

                $seccion->fill([
                    'nombre'         => $seccionData['nombre'],
                    'descripcion'    => $seccionData['descripcion'] ?? null,
                    'tipo_respuesta' => $seccionData['tipo_respuesta'],
                    'orden'          => $seccionData['orden'],
                    'activa'         => !empty($seccionData['activa']),
                ]);
                $seccion->save();

                if (!empty($seccionData['items'])) {
                    foreach ($seccionData['items'] as $itemData) {
                        $item = isset($itemData['id'])
                            ? ChecklistItem::find($itemData['id'])
                            : new ChecklistItem(['seccion_id' => $seccion->id]);

                        $item->fill([
                            'nombre'     => $itemData['nombre'],
                            'es_critico' => !empty($itemData['es_critico']),
                            'orden'      => $itemData['orden'],
                            'activo'     => !empty($itemData['activo']),
                        ]);
                        $item->save();
                    }
                }
            }
        }

        return redirect()->route('checklist-plantillas.edit', $plantilla)
            ->with('success', 'Plantilla actualizada correctamente.');
    }
}