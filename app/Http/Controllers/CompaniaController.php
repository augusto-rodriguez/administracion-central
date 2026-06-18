<?php

namespace App\Http\Controllers;

use App\Models\Compania;
use App\Models\Especialidad;
use Illuminate\Http\Request;

class CompaniaController extends Controller
{
    public function index()
    {
        $companias = Compania::where('numero', '!=', 0)
            ->withCount(['voluntarios', 'unidades'])
            ->with('especialidades')
            ->orderBy('numero')
            ->get();
        return view('companias.index', compact('companias'));
    }

    public function create()
    {
        $especialidades = Especialidad::where('activa', true)->orderBy('nombre')->get();
        return view('companias.create', compact('especialidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'    => 'required|string|max:255',
            'numero'    => 'required|integer|unique:companias',
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:20',
        ]);

        $compania = Compania::create($request->only(['nombre', 'numero', 'direccion', 'telefono']));

        if ($request->has('especialidades')) {
            $compania->especialidades()->sync($request->especialidades);
        }

        return redirect()->route('companias.index')->with('success', 'Compañía creada exitosamente.');
    }

    public function edit(Compania $compania)
    {
        $especialidades = Especialidad::where('activa', true)->orderBy('nombre')->get();
        $compania->load('especialidades');
        return view('companias.edit', compact('compania', 'especialidades'));
    }

    public function update(Request $request, Compania $compania)
    {
        $request->validate([
            'nombre'   => 'required|string|max:255',
            'numero'   => 'required|integer|unique:companias,numero,' . $compania->id,
            'direccion' => 'nullable|string|max:255',
            'telefono'  => 'nullable|string|max:20',
        ]);

        $compania->update($request->only(['nombre', 'numero', 'direccion', 'telefono', 'activa']));
        $compania->especialidades()->sync($request->especialidades ?? []);

        return redirect()->route('companias.index')->with('success', 'Compañía actualizada.');
    }

    public function destroy(Compania $compania)
    {
        $compania->delete();
        return redirect()->route('companias.index')->with('success', 'Compañía eliminada.');
    }
}