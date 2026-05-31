<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;

class CargoController extends Controller
{
    public function index()
    {
        $cargos = Cargo::orderBy('tipo')->orderBy('orden')->get();
        return view('cargos.index', compact('cargos'));
    }

    public function create()
    {
        return view('cargos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'tipo'        => 'required|in:compania,general',
            'descripcion' => 'nullable|string',
            'activo'      => 'boolean',
            'es_unico'    => 'boolean',
        ]);

        Cargo::create([
            'nombre'      => $request->nombre,
            'tipo'        => $request->tipo,
            'descripcion' => $request->descripcion,
            'activo'      => $request->boolean('activo', true),
            'es_unico'    => $request->boolean('es_unico', true),
            'orden'       => 99,
        ]);

        return redirect()->route('cargos.index')
                         ->with('success', 'Cargo creado exitosamente.');
    }

    public function show(Cargo $cargo)
    {
        return redirect()->route('cargos.index');
    }

    public function edit(Cargo $cargo)
    {
        return view('cargos.edit', compact('cargo'));
    }

    public function update(Request $request, Cargo $cargo)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'tipo'        => 'required|in:compania,general',
            'descripcion' => 'nullable|string',
            'activo'      => 'boolean',
            'es_unico'    => 'boolean',
        ]);

        $cargo->update([
            'nombre'      => $request->nombre,
            'tipo'        => $request->tipo,
            'descripcion' => $request->descripcion,
            'activo'      => $request->boolean('activo'),
            'es_unico'    => $request->boolean('es_unico'),
        ]);

        return redirect()->route('cargos.index')
                         ->with('success', 'Cargo actualizado exitosamente.');
    }

    public function destroy(Cargo $cargo)
    {
        if ($cargo->asignacionesActivas()->exists()) {
            return back()->with('error', 'No se puede eliminar un cargo con voluntarios asignados actualmente.');
        }

        $cargo->delete();

        return redirect()->route('cargos.index')
                         ->with('success', 'Cargo eliminado exitosamente.');
    }
}