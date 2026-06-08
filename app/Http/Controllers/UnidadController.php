<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use App\Models\Compania;
use Illuminate\Http\Request;

class UnidadController extends Controller
{
    public function index(Request $request)
    {
        $usuario   = auth()->user();
        $companias = Compania::where('activa', true)->orderBy('numero')->get();

        $query = Unidad::with('compania');

        if ($usuario->esCapitanCia()) {
            // Capitán: solo ve las unidades de su compañía, sin filtro manual
            $query->where('compania_id', $usuario->voluntario?->compania_id);
        } else {
            $query->when($request->compania_id, fn($q) => $q->where('compania_id', $request->compania_id));
        }

        $unidades = $query->get();

        return view('unidades.index', compact('unidades', 'companias'));
    }

    public function create()
    {
        $usuario   = auth()->user();
        $companias = $usuario->esCapitanCia()
            ? Compania::where('id', $usuario->voluntario?->compania_id)->get()
            : Compania::where('activa', true)->get();

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
        $usuario = auth()->user();

        // Capitán: solo puede editar unidades de su compañía
        if ($usuario->esCapitanCia() && $unidad->compania_id !== $usuario->voluntario?->compania_id) {
            abort(403);
        }

        $companias = $usuario->esCapitanCia()
            ? Compania::where('id', $usuario->voluntario?->compania_id)->get()
            : Compania::where('activa', true)->get();

        return view('unidades.edit', compact('unidad', 'companias'));
    }

    public function update(Request $request, Unidad $unidad)
    {
        $usuario = auth()->user();

        // Capitán: no puede mover una unidad a otra compañía
        if ($usuario->esCapitanCia() && $unidad->compania_id !== $usuario->voluntario?->compania_id) {
            abort(403);
        }

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