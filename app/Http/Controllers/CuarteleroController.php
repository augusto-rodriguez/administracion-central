<?php

namespace App\Http\Controllers;

use App\Models\Cuartelero;
use App\Models\Compania;
use App\Models\Unidad;
use Illuminate\Http\Request;

class CuarteleroController extends Controller
{
    public function index()
    {
        // Activos primero, luego histórico ordenado por compañía
        $cuarteleros = Cuartelero::with(['compania', 'unidadesAutorizadas', 'turnoActivo'])
            ->orderByRaw('fecha_fin IS NOT NULL')   // activos primero
            ->orderBy('compania_id')
            ->orderBy('nombre')
            ->get();

        return view('cuarteleros.index', compact('cuarteleros'));
    }

    public function create()
    {
        $companias = Compania::where('activa', true)->orderBy('numero')->get();

        // IDs de compañías que ya tienen un cuartelero activo (sin fecha_fin)
        $companiasOcupadas = Cuartelero::whereNull('fecha_fin')
                                       ->pluck('compania_id')
                                       ->toArray();

        return view('cuarteleros.create', compact('companias', 'companiasOcupadas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'compania_id'  => 'required|exists:companias,id',
            'nombre'       => 'required|string|max:255',
            'rut'          => 'nullable|string|unique:cuarteleros,rut',
            'telefono'     => 'nullable|string|max:20',
            'fecha_inicio' => 'required|date',
        ]);

        // Verificar que la compañía no tenga ya un cuartelero activo
        $yaExiste = Cuartelero::where('compania_id', $request->compania_id)
                              ->whereNull('fecha_fin')
                              ->exists();

        if ($yaExiste) {
            return back()->withInput()->withErrors([
                'compania_id' => 'Esta compañía ya tiene un cuartelero activo. Debes dar de baja al actual antes de registrar uno nuevo.',
            ]);
        }

        $cuartelero = Cuartelero::create([
            ...$request->only('compania_id', 'nombre', 'rut', 'telefono'),
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin'    => null,
            'activo'       => true,
        ]);

        // Autorizar todas las unidades activas de su compañía por defecto
        $unidades = Unidad::where('compania_id', $request->compania_id)
                          ->where('activa', true)->pluck('id');
        $cuartelero->unidadesAutorizadas()->sync($unidades);

        return redirect()->route('cuarteleros.show', $cuartelero)
                         ->with('success', 'Cuartelero registrado exitosamente.');
    }

    public function show(Cuartelero $cuartelero)
    {
        $cuartelero->load(['compania', 'unidadesAutorizadas.compania', 'turnos.unidades']);
        $unidades = Unidad::with('compania')->where('activa', true)->get();

        // Historial: otros cuarteleros de la misma compañía (excluyendo el actual)
        $historial = Cuartelero::where('compania_id', $cuartelero->compania_id)
                               ->where('id', '!=', $cuartelero->id)
                               ->whereNotNull('fecha_fin')
                               ->orderByDesc('fecha_fin')
                               ->get();

        return view('cuarteleros.show', compact('cuartelero', 'unidades', 'historial'));
    }

    public function edit(Cuartelero $cuartelero)
    {
        $companias = Compania::where('activa', true)->orderBy('numero')->get();
        return view('cuarteleros.edit', compact('cuartelero', 'companias'));
    }

    public function update(Request $request, Cuartelero $cuartelero)
    {
        $request->validate([
            'compania_id'  => 'required|exists:companias,id',
            'nombre'       => 'required|string|max:255',
            'rut'          => 'nullable|string|unique:cuarteleros,rut,' . $cuartelero->id,
            'telefono'     => 'nullable|string|max:20',
            'fecha_inicio' => 'required|date',
        ]);

        $cuartelero->update($request->only(
            'compania_id', 'nombre', 'rut', 'telefono', 'fecha_inicio'
        ));

        return redirect()->route('cuarteleros.show', $cuartelero)
                         ->with('success', 'Cuartelero actualizado.');
    }

    /**
     * Cierra el período del cuartelero actual y opcionalmente crea el reemplazante.
     * Ruta: POST /cuarteleros/{cuartelero}/cerrar
     */
    public function cerrar(Request $request, Cuartelero $cuartelero)
    {
        $request->validate([
            'fecha_fin'   => 'required|date|after_or_equal:' . $cuartelero->fecha_inicio,
            'motivo_fin'  => 'nullable|string|max:255',
        ]);

        $cuartelero->update([
            'fecha_fin'  => $request->fecha_fin,
            'motivo_fin' => $request->motivo_fin,
            'activo'     => false,
        ]);

        return redirect()->route('cuarteleros.index')
                         ->with('success', "Período de {$cuartelero->nombre} cerrado. Ahora puedes registrar al reemplazante.");
    }

    // ─── Unidades autorizadas ────────────────────────────────────

    public function autorizarUnidad(Request $request, Cuartelero $cuartelero)
    {
        $request->validate(['unidad_id' => 'required|exists:unidades,id']);
        $cuartelero->unidadesAutorizadas()->syncWithoutDetaching([$request->unidad_id]);
        return redirect()->back()->with('success', 'Unidad autorizada correctamente.');
    }

    public function revocarUnidad(Request $request, Cuartelero $cuartelero)
    {
        $cuartelero->unidadesAutorizadas()->detach($request->unidad_id);
        return redirect()->back()->with('success', 'Autorización revocada.');
    }
}