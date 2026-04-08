<?php

namespace App\Http\Controllers;

use App\Models\Maquinista;
use App\Models\Compania;
use App\Models\Unidad;
use Illuminate\Http\Request;

class MaquinistaController extends Controller
{
    public function index(Request $request)
    {
        $companias = Compania::where('activa', true)->orderBy('numero')->get();

        $maquinistas = Maquinista::with(['compania', 'turnoActivo'])
            ->when($request->compania_id, fn($q) => $q->where('compania_id', $request->compania_id))
            ->get();

        return view('maquinistas.index', compact('maquinistas', 'companias'));
    }

    public function create()
    {
        $companias = Compania::where('activa', true)->get();
        return view('maquinistas.create', compact('companias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'compania_id' => 'required|exists:companias,id',
            'nombre'      => 'required|string|max:255',
            'rut'         => 'required|string|unique:maquinistas',
            'telefono'    => 'nullable|string|max:20',
            'cargo'       => 'nullable|string|max:100',
        ]);

        Maquinista::create($request->all());
        return redirect()->route('maquinistas.index')->with('success', 'Maquinista creado exitosamente.');
    }

    public function show(Maquinista $maquinista)
    {
        $maquinista->load(['compania', 'unidadesAutorizadas.compania', 'turnos.unidades']);
        $unidadesDisponibles = Unidad::with('compania')
            ->whereNotIn('id', $maquinista->unidadesAutorizadas->pluck('id'))
            ->where('activa', true)
            ->get();
        return view('maquinistas.show', compact('maquinista', 'unidadesDisponibles'));
    }

    public function edit(Maquinista $maquinista)
    {
        $companias = Compania::where('activa', true)->get();
        return view('maquinistas.edit', compact('maquinista', 'companias'));
    }

    public function update(Request $request, Maquinista $maquinista)
    {
        $request->validate([
            'compania_id' => 'required|exists:companias,id',
            'nombre'      => 'required|string|max:255',
            'rut'         => 'required|string|unique:maquinistas,rut,' . $maquinista->id,
            'telefono'    => 'nullable|string|max:20',
            'cargo'       => 'nullable|string|max:100',
        ]);

        $maquinista->update($request->all());
        return redirect()->route('maquinistas.index')->with('success', 'Maquinista actualizado.');
    }

    public function destroy(Maquinista $maquinista)
    {
        $maquinista->delete();
        return redirect()->route('maquinistas.index')->with('success', 'Maquinista eliminado.');
    }

    public function autorizarUnidad(Request $request, Maquinista $maquinista)
    {
        $request->validate([
            'unidad_id'          => 'required|exists:unidades,id',
            'autorizado_por'     => 'nullable|string|max:255',
            'fecha_autorizacion' => 'nullable|date',
        ]);

        $maquinista->unidadesAutorizadas()->attach($request->unidad_id, [
            'autorizado_por'     => $request->autorizado_por,
            'fecha_autorizacion' => $request->fecha_autorizacion,
        ]);

        return redirect()->back()->with('success', 'Unidad autorizada correctamente.');
    }

    public function revocarUnidad(Maquinista $maquinista, Unidad $unidad)
    {
        $maquinista->unidadesAutorizadas()->detach($unidad->id);
        return redirect()->back()->with('success', 'Autorización revocada.');
    }
}