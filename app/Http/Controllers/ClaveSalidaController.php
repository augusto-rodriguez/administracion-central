<?php

namespace App\Http\Controllers;

use App\Models\ClaveSalida;
use Illuminate\Http\Request;

class ClaveSalidaController extends Controller
{
    public function index()
    {
        $emergencias     = ClaveSalida::where('tipo', 'emergencia')->orderBy('codigo')->get();
        $administrativas = ClaveSalida::where('tipo', 'administrativa')->orderBy('codigo')->get();
        return view('claves-salida.index', compact('emergencias', 'administrativas'));
    }

    public function create()
    {
        return view('claves-salida.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo'      => 'required|string|unique:claves_salida',
            'descripcion' => 'required|string|max:255',
            'tipo'        => 'required|in:emergencia,administrativa',
        ]);

        ClaveSalida::create($request->all());
        return redirect()->route('claves-salida.index')->with('success', 'Clave creada exitosamente.');
    }

    public function edit(ClaveSalida $clavesSalida)
    {
        return view('claves-salida.edit', ['clave' => $clavesSalida]);
    }

    public function update(Request $request, ClaveSalida $clavesSalida)
    {
        $request->validate([
            'codigo'      => 'required|string|unique:claves_salida,codigo,' . $clavesSalida->id,
            'descripcion' => 'required|string|max:255',
            'tipo'        => 'required|in:emergencia,administrativa',
        ]);

        $clavesSalida->update($request->all());
        return redirect()->route('claves-salida.index')->with('success', 'Clave actualizada.');
    }
}