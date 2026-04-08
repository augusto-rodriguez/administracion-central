<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use App\Models\Voluntario;
use Illuminate\Http\Request;

class ConsultaController extends Controller
{
    public function index(Request $request)
    {
        $unidades   = Unidad::with('compania')->where('activa', true)->orderBy('nombre')->get();
        $voluntarios = Voluntario::with('compania')
            ->whereHas('roles', fn($q) => $q->where('rol', 'maquinista')->where('activo', true))
            ->where('activo', true)->orderBy('nombre')->get();

        $resultados = collect();
        $tipo = $request->tipo ?? 'unidades';

        if ($request->filled('unidades_ids')) {
            $resultados = Voluntario::with(['compania', 'unidadesAutorizadas.compania', 'turnoActivo'])
                ->whereHas('unidadesAutorizadas', fn($q) =>
                    $q->whereIn('unidades.id', $request->unidades_ids)
                )
                ->get()
                ->map(function($voluntario) use ($request) {
                    $voluntario->unidades_match = $voluntario->unidadesAutorizadas
                        ->whereIn('id', $request->unidades_ids);
                    return $voluntario;
                });
            $tipo = 'unidades';
        }

        if ($request->filled('voluntario_id')) {
            $resultados = Voluntario::with(['compania', 'unidadesAutorizadas.compania', 'turnoActivo'])
                ->where('id', $request->voluntario_id)
                ->get();
            $tipo = 'voluntario';
        }

        return view('consultas.index', compact('unidades', 'voluntarios', 'resultados', 'tipo'));
    }
}