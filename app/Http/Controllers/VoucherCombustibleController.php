<?php

namespace App\Http\Controllers;

use App\Models\VoucherCombustible;
use App\Models\Unidad;
use App\Models\Voluntario;
use App\Models\Cuartelero;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VouchersCombustibleExport;

class VoucherCombustibleController extends Controller
{
    public function index(Request $request)
    {
        $query = VoucherCombustible::with(['unidad.compania', 'registradoPor'])
            ->orderBy('fecha_carga', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->compania_id) {
            $query->whereHas('unidad', fn($q) => $q->where('compania_id', $request->compania_id));
        }

        if ($request->unidad_id) {
            $query->where('unidad_id', $request->unidad_id);
        }

        if ($request->mes) {
            $query->whereMonth('fecha_carga', $request->mes);
        }

        if ($request->anio) {
            $query->whereYear('fecha_carga', $request->anio);
        }

        $vouchers  = $query->paginate(20)->withQueryString();
        $unidades  = Unidad::with('compania')->where('activa', true)->orderBy('nombre')->get();
        $companias = \App\Models\Compania::orderBy('nombre')->get();
        $conductores = $this->getConductores();

        return view('vouchers-combustible.index', compact('vouchers', 'unidades', 'companias', 'conductores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_carga'      => 'required|date',
            'unidad_id'        => 'required|exists:unidades,id',
            'km_carga'         => 'required|integer|min:0',
            'conductor_nombre' => 'required|string|max:255',
            'numero_voucher'   => 'required|string|max:100|unique:vouchers_combustible,numero_voucher',
            'litros'           => 'required|numeric|min:0.001|max:9999.999',
            'valor_unitario'   => 'required|integer|min:1',
            'observaciones'    => 'nullable|string|max:500',
        ]);

        $total = (int) round($request->litros * $request->valor_unitario);

        VoucherCombustible::create([
            'fecha_carga'      => $request->fecha_carga,
            'unidad_id'        => $request->unidad_id,
            'km_carga'         => $request->km_carga,
            'conductor_nombre' => $request->conductor_nombre,
            'numero_voucher'   => $request->numero_voucher,
            'litros'           => $request->litros,
            'valor_unitario'   => $request->valor_unitario,
            'total'            => $total,
            'observaciones'    => $request->observaciones,
            'registrado_por'   => auth()->id(),
        ]);

        return redirect()->route('vouchers-combustible.index')
            ->with('success', 'Voucher registrado exitosamente.');
    }

    public function edit(VoucherCombustible $voucher)
    {
        $voucherCombustible = $voucher;
        $unidades    = Unidad::with('compania')->where('activa', true)->orderBy('nombre')->get();
        $conductores = $this->getConductores();
        return view('vouchers-combustible.edit', compact('voucherCombustible', 'unidades', 'conductores'));
    }

    public function update(Request $request, VoucherCombustible $voucher)
    {
        $request->validate([
            'fecha_carga'      => 'required|date',
            'unidad_id'        => 'required|exists:unidades,id',
            'km_carga'         => 'required|integer|min:0',
            'conductor_nombre' => 'required|string|max:255',
            'numero_voucher'   => 'required|string|max:100|unique:vouchers_combustible,numero_voucher,' . $voucher->id,
            'litros'           => 'required|numeric|min:0.001|max:9999.999',
            'valor_unitario'   => 'required|integer|min:1',
            'observaciones'    => 'nullable|string|max:500',
        ]);

        $total = (int) round($request->litros * $request->valor_unitario);

        $voucher->update([
            'fecha_carga'      => $request->fecha_carga,
            'unidad_id'        => $request->unidad_id,
            'km_carga'         => $request->km_carga,
            'conductor_nombre' => $request->conductor_nombre,
            'numero_voucher'   => $request->numero_voucher,
            'litros'           => $request->litros,
            'valor_unitario'   => $request->valor_unitario,
            'total'            => $total,
            'observaciones'    => $request->observaciones,
        ]);

        return redirect()->route('vouchers-combustible.index')
            ->with('success', 'Voucher actualizado exitosamente.');
    }

    public function exportar(Request $request)
    {
        $mes       = $request->mes;
        $anio      = $request->anio ?? now()->year;
        $companiaId = $request->compania_id;
        $unidadId  = $request->unidad_id;

        $nombre = "vouchers_combustible_{$anio}" . ($mes ? "_{$mes}" : '') . ".xlsx";

        return Excel::download(new VouchersCombustibleExport($mes, $anio, $companiaId, $unidadId), $nombre);
    }

    private function getConductores(): array
    {
        $conductores = [];

        $maquinistas = Voluntario::whereHas('roles', fn($q) => $q->where('rol', 'maquinista')->where('activo', true))
            ->where('activo', true)       // voluntarios → activo ✓
            ->with('compania')
            ->orderBy('nombre')
            ->get();

        foreach ($maquinistas as $v) {
            $conductores[] = [
                'nombre' => $v->nombre . ' — ' . $v->compania->nombre . ' (Maquinista)',
            ];
        }

        $cuarteleros = Cuartelero::where('activo', true)  // cuarteleros → activo ✓
            ->with('compania')
            ->orderBy('nombre')
            ->get();

        foreach ($cuarteleros as $c) {
            $conductores[] = [
                'nombre' => $c->nombre . ' — ' . $c->compania->nombre . ' (Cuartelero)',
            ];
        }

        return $conductores;
    }
}