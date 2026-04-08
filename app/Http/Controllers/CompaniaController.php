<?php

namespace App\Http\Controllers;

use App\Models\Compania;
use Illuminate\Http\Request;

class CompaniaController extends Controller
{
    public function index()
    {
        $companias = Compania::withCount(['voluntarios', 'unidades'])->get();
        return view('companias.index', compact('companias'));
    }

    public function create()
    {
        return view('companias.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'  => 'required|string|max:255',
            'numero'  => 'required|integer|unique:companias',
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:20',
        ]);

        Compania::create($request->all());
        return redirect()->route('companias.index')->with('success', 'Compañía creada exitosamente.');
    }

    public function edit(Compania $compania)
    {
        return view('companias.edit', compact('compania'));
    }

    public function update(Request $request, Compania $compania)
    {
        $request->validate([
            'nombre'   => 'required|string|max:255',
            'numero'   => 'required|integer|unique:companias,numero,' . $compania->id,
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:20',
        ]);

        $compania->update($request->all());
        return redirect()->route('companias.index')->with('success', 'Compañía actualizada.');
    }

    public function destroy(Compania $compania)
    {
        $compania->delete();
        return redirect()->route('companias.index')->with('success', 'Compañía eliminada.');
    }
}