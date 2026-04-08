<?php

namespace App\Http\Controllers;

use App\Models\Oficial;
use App\Models\Compania;
use Illuminate\Http\Request;

class OficialController extends Controller
{
    public function index()
    {
        $oficiales = Oficial::with('compania')->get();
        return view('oficiales.index', compact('oficiales'));
    }

    public function create()
    {
        $companias = Compania::where('activa', true)->orderBy('numero')->get();
        return view('oficiales.create', compact('companias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'compania_id' => 'required|exists:companias,id',
            'nombre'      => 'required|string|max:255',
            'cargo'       => 'nullable|string|max:100',
            'telefono'    => 'nullable|string|max:20',
        ]);

        Oficial::create($request->all());
        return redirect()->route('oficiales.index')->with('success', 'Oficial creado exitosamente.');
    }

    public function edit(Oficial $oficial)
    {
        $companias = Compania::where('activa', true)->orderBy('numero')->get();
        return view('oficiales.edit', compact('oficial', 'companias'));
    }

    public function update(Request $request, Oficial $oficial)
    {
        $request->validate([
            'compania_id' => 'required|exists:companias,id',
            'nombre'      => 'required|string|max:255',
            'cargo'       => 'nullable|string|max:100',
            'telefono'    => 'nullable|string|max:20',
        ]);

        $oficial->update($request->all());
        return redirect()->route('oficiales.index')->with('success', 'Oficial actualizado.');
    }

    public function destroy(Oficial $oficial)
    {
        $oficial->delete();
        return redirect()->route('oficiales.index')->with('success', 'Oficial eliminado.');
    }
}