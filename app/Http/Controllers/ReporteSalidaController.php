<?php

namespace App\Http\Controllers;

use App\Models\Compania;
use App\Models\Unidad;
use App\Models\ClaveSalida;
use App\Models\Voluntario;
use App\Models\SalidaUnidad;
use App\Exports\SalidasExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ReporteSalidaController extends Controller
{
    public function index(Request $request)
    {
        $companias  = Compania::where('activa', true)->orderBy('numero')->get();
        $unidades   = Unidad::with('compania')->where('activa', true)->orderBy('nombre')->get();
        $claves     = ClaveSalida::where('activa', true)->orderBy('tipo')->orderBy('codigo')->get();

        $oficiales = Voluntario::with('compania')
            ->whereHas('roles', fn($q) => $q->where('rol', 'oficial')
                ->where('activo', true)
                ->where('puede_autorizar_salidas', true))
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $maquinistas = Voluntario::with('compania')
            ->whereHas('roles', fn($q) => $q->where('rol', 'maquinista')->where('activo', true))
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $cuarteleros = \App\Models\Cuartelero::with('compania')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        // Voluntarios al mando
        $voluntariosAlMando = Voluntario::with('compania')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $salidas     = collect();
        $totalKm     = 0;
        $totalTiempo = 0;
        $buscando    = $request->hasAny([
            'desde', 'hasta', 'unidad_id', 'clave_salida_id',
            'oficial_id', 'compania_id', 'conductor_id', 'al_mando_id',
        ]);

        if ($buscando) {
            $query = SalidaUnidad::with(['unidad.compania', 'claveSalida', 'oficial', 'voluntario', 'alMando'])
                ->whereNotNull('llegada_at');

            if ($request->filled('desde')) {
                $query->whereDate('salida_at', '>=', $request->desde);
            }
            if ($request->filled('hasta')) {
                $query->whereDate('salida_at', '<=', $request->hasta);
            }
            if ($request->filled('compania_id')) {
                $query->whereHas('unidad', fn($q) => $q->where('compania_id', $request->compania_id));
            }
            if ($request->filled('unidad_id')) {
                $query->where('unidad_id', $request->unidad_id);
            }
            if ($request->filled('clave_salida_id')) {
                $query->where('clave_salida_id', $request->clave_salida_id);
            }
            if ($request->filled('oficial_id')) {
                $query->where('oficial_id', $request->oficial_id);
            }
            if ($request->filled('al_mando_id')) {
                $query->where('al_mando_id', $request->al_mando_id);
            }
            if ($request->filled('conductor_id')) {
                $partes = explode('_', $request->conductor_id, 2);
                if ($partes[0] === 'v') {
                    $query->where('voluntario_id', $partes[1]);
                } elseif ($partes[0] === 'c') {
                    $cuartelero = \App\Models\Cuartelero::find($partes[1]);
                    if ($cuartelero) {
                        $query->where('conductor_libre', '[Cuartelero] ' . $cuartelero->nombre);
                    }
                }
            }

            $salidas     = $query->orderBy('salida_at', 'desc')->get();
            $totalKm     = $salidas->sum('km_recorrido');
            $totalTiempo = $salidas->sum(function ($s) {
                return $s->salida_at && $s->llegada_at
                    ? $s->salida_at->diffInMinutes($s->llegada_at)
                    : 0;
            });
        }

        return view('reportes.salidas', compact(
            'companias', 'unidades', 'claves', 'oficiales',
            'maquinistas', 'cuarteleros', 'voluntariosAlMando',
            'salidas', 'totalKm', 'totalTiempo', 'buscando'
        ));
    }

    public function exportar(Request $request)
    {
        $filename = 'salidas_' . ($request->desde ?? 'inicio') . '_al_' . ($request->hasta ?? 'hoy') . '.xlsx';

        return Excel::download(
            new SalidasExport($request->all()),
            $filename
        );
    }
}