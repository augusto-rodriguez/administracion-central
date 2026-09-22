<?php

namespace App\Http\Controllers;

use App\Models\ChecklistConfiguracion;
use Illuminate\Http\Request;

class ChecklistConfigController extends Controller
{
    public function index()
    {
        $configuraciones = ChecklistConfiguracion::all();
        return view('checklist-config.index', compact('configuraciones'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'config'   => 'required|array',
            'config.*' => 'nullable|string|max:1000',
        ]);

        foreach ($request->config as $clave => $valor) {
            ChecklistConfiguracion::where('clave', $clave)->update(['valor' => $valor ?? '']);
        }

        return back()->with('success', 'Configuración actualizada.');
    }
}