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
        $cuarteleros = Cuartelero::with(['compania', 'unidadesAutorizadas', 'turnoActivo'])
            ->orderBy('nombre')->get();
        return view('cuarteleros.index', compact('cuarteleros'));
    }

    public function create()
    {
        $companias = Compania::where('activa', true)->orderBy('numero')->get();
        return view('cuarteleros.create', compact('companias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'compania_id' => 'required|exists:companias,id',
            'nombre'      => 'required|string|max:255',
            'rut'         => 'nullable|string|unique:cuarteleros,rut',
            'telefono'    => 'nullable|string|max:20',
        ]);

        $cuartelero = Cuartelero::create($request->only(
            'compania_id', 'nombre', 'rut', 'telefono'
        ));

        // Autorizar todas las unidades de su compañía por defecto
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
        return view('cuarteleros.show', compact('cuartelero', 'unidades'));
    }

    public function edit(Cuartelero $cuartelero)
    {
        $companias = Compania::where('activa', true)->orderBy('numero')->get();
        return view('cuarteleros.edit', compact('cuartelero', 'companias'));
    }

    public function update(Request $request, Cuartelero $cuartelero)
    {
        $request->validate([
            'compania_id' => 'required|exists:companias,id',
            'nombre'      => 'required|string|max:255',
            'rut'         => 'nullable|string|unique:cuarteleros,rut,' . $cuartelero->id,
            'telefono'    => 'nullable|string|max:20',
            'activo'      => 'boolean',
        ]);

        $cuartelero->update($request->only(
            'compania_id', 'nombre', 'rut', 'telefono', 'activo'
        ));

        return redirect()->route('cuarteleros.show', $cuartelero)
                         ->with('success', 'Cuartelero actualizado.');
    }

    public function autorizarUnidad(Request $request, Cuartelero $cuartelero)
    {
        $request->validate([
            'unidad_id' => 'required|exists:unidades,id',
        ]);

        $cuartelero->unidadesAutorizadas()->syncWithoutDetaching([$request->unidad_id]);

        return redirect()->back()->with('success', 'Unidad autorizada correctamente.');
    }

    public function revocarUnidad(Request $request, Cuartelero $cuartelero)
    {
        $cuartelero->unidadesAutorizadas()->detach($request->unidad_id);
        return redirect()->back()->with('success', 'Autorización revocada.');
    }
}