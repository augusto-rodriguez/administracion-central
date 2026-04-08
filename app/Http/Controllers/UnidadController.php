<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use App\Models\Compania;
use Illuminate\Http\Request;

class UnidadController extends Controller
{
    public function index(Request $request)
    {
        $companias = Compania::where('activa', true)->orderBy('numero')->get();

        $unidades = Unidad::with('compania')
            ->when($request->compania_id, fn($q) => $q->where('compania_id', $request->compania_id))
            ->get();

        return view('unidades.index', compact('unidades', 'companias'));
    }

    public function create()
    {
        $companias = Compania::where('activa', true)->get();
        return view('unidades.create', compact('companias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'compania_id' => 'required|exists:companias,id',
            'nombre'      => 'required|string|max:255',
            'patente'     => 'required|string|unique:unidades',
            'tipo'        => 'required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        Unidad::create($request->all());
        return redirect()->route('unidades.index')->with('success', 'Unidad creada exitosamente.');
    }

    public function edit(Unidad $unidad)
    {
        $companias = Compania::where('activa', true)->get();
        return view('unidades.edit', compact('unidad', 'companias'));
    }

    public function update(Request $request, Unidad $unidad)
    {
        $request->validate([
            'compania_id' => 'required|exists:companias,id',
            'nombre'      => 'required|string|max:255',
            'patente'     => 'required|string|unique:unidades,patente,' . $unidad->id,
            'tipo'        => 'required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        $unidad->update($request->all());
        return redirect()->route('unidades.index')->with('success', 'Unidad actualizada.');
    }

    public function destroy(Unidad $unidad)
    {
        $unidad->delete();
        return redirect()->route('unidades.index')->with('success', 'Unidad eliminada.');
    }
}