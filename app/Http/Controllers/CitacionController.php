<?php

namespace App\Http\Controllers;

use App\Models\Citacion;
use App\Models\Compania;
use App\Models\MedioRecepcionCitacion;
use Illuminate\Http\Request;

class CitacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Citacion::with(['compania', 'medioRecepcion']);

        // Filtros
        if ($request->filled('compania_id')) {
            $query->where('compania_id', $request->compania_id);
        }

        if ($request->filled('medio_recepcion_id')) {
            $query->where('medio_recepcion_id', $request->medio_recepcion_id);
        }

        $citaciones = $query->latest()->get();

        $companias = Compania::where('activa', true)->orderBy('numero')->get();
        $medios    = MedioRecepcionCitacion::where('activo', true)->orderBy('nombre')->get();

        return view('citaciones.index', compact('citaciones', 'companias', 'medios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'compania_id'          => 'required|exists:companias,id',
            'medio_recepcion_id'   => 'required|exists:medios_recepcion_citaciones,id',
            'mensaje'              => 'required|string',
            'fecha_citacion'       => 'nullable|date',
        ]);

        Citacion::create($request->only(
            'compania_id',
            'medio_recepcion_id',
            'mensaje',
            'fecha_citacion'
        ));

        return redirect()->route('citaciones.index')
                         ->with('success', 'Citación registrada correctamente.');
    }
}