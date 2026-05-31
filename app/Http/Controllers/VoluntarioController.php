<?php

namespace App\Http\Controllers;

use App\Models\Voluntario;
use App\Models\VoluntarioRol;
use App\Models\VoluntarioCargo;
use App\Models\Cargo;
use App\Models\Compania;
use App\Models\Unidad;
use Illuminate\Http\Request;

class VoluntarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Voluntario::with(['compania', 'roles', 'cargosActivos.cargo', 'unidadesAutorizadas']);

        if ($request->filled('compania_id')) {
            $query->where('compania_id', $request->compania_id);
        }
        if ($request->filled('rol')) {
            $query->whereHas('roles', fn($q) => $q->where('rol', $request->rol)->where('activo', true));
        }

        $voluntarios = $query->orderBy('nombre')->get();
        $companias   = Compania::where('activa', true)->orderBy('numero')->get();

        return view('voluntarios.index', compact('voluntarios', 'companias'));
    }

    public function create()
    {
        $companias        = Compania::where('activa', true)->orderBy('numero')->get();
        $rolesDisponibles = ['maquinista', 'oficial'];

        $cargosCompania  = Cargo::where('tipo', 'compania')->where('activo', true)->orderBy('orden')->get();
        $cargosGenerales = Cargo::where('tipo', 'general')->where('activo', true)->orderBy('orden')->get();

        $cargosGeneralesOcupados = VoluntarioCargo::whereHas('cargo', fn($q) => $q->where('tipo', 'general'))
            ->whereNull('compania_id')
            ->where('activo', true)
            ->pluck('cargo_id')
            ->toArray();

        $cargosCompaniaOcupados = VoluntarioCargo::whereHas('cargo', fn($q) => $q->where('tipo', 'compania'))
            ->where('activo', true)
            ->get()
            ->groupBy('cargo_id')
            ->map(fn($grupo) => $grupo->pluck('compania_id')->toArray());

        return view('voluntarios.create', compact(
            'companias', 'rolesDisponibles',
            'cargosCompania', 'cargosGenerales',
            'cargosGeneralesOcupados', 'cargosCompaniaOcupados'
        ));
    }

    public function store(Request $request)
    {
        // Unificar los dos selects de cargo en uno solo
        $request->merge([
            'cargo_id' => $request->cargo_compania_id ?: $request->cargo_general_id ?: null,
        ]);

        $request->validate([
            'compania_id'       => 'required|exists:companias,id',
            'nombre'            => 'required|string|max:255',
            'rut'               => 'nullable|string|unique:voluntarios,rut',
            'telefono'          => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'roles'             => 'nullable|array',
            'cargo_compania_id' => 'nullable|exists:cargos,id',
            'cargo_general_id'  => 'nullable|exists:cargos,id',
            'cargo_id'          => 'nullable|exists:cargos,id',
        ]);

        // Validar ocupación antes de crear
        if ($request->filled('cargo_id')) {
            $cargo = Cargo::findOrFail($request->cargo_id);

            if ($cargo->esDeCompania()) {
                $ocupado = VoluntarioCargo::where('cargo_id', $cargo->id)
                    ->where('compania_id', $request->compania_id)
                    ->where('activo', true)
                    ->exists();

                if ($ocupado) {
                    return back()->withInput()->withErrors([
                        'cargo_id' => "El cargo '{$cargo->nombre}' ya tiene un titular activo en esa compañía.",
                    ]);
                }
            }

            if ($cargo->esGeneral()) {
                $ocupado = VoluntarioCargo::where('cargo_id', $cargo->id)
                    ->whereNull('compania_id')
                    ->where('activo', true)
                    ->exists();

                if ($ocupado) {
                    return back()->withInput()->withErrors([
                        'cargo_id' => "El cargo '{$cargo->nombre}' ya tiene un titular activo en el Cuerpo.",
                    ]);
                }
            }
        }

        $voluntario = Voluntario::create($request->only(
            'compania_id', 'nombre', 'rut', 'telefono', 'email'
        ));

        // Si tiene cargo, oficial se agrega automáticamente
        $roles = $request->roles ?? [];
        if ($request->filled('cargo_id') && !in_array('oficial', $roles)) {
            $roles[] = 'oficial';
        }

        foreach ($roles as $rol) {
            VoluntarioRol::create([
                'voluntario_id'           => $voluntario->id,
                'rol'                     => $rol,
                'rango'                   => null,
                'activo'                  => true,
                'puede_autorizar_salidas' => false,
            ]);
        }

        if ($request->filled('cargo_id')) {
            $cargo = Cargo::find($request->cargo_id);
            VoluntarioCargo::create([
                'voluntario_id' => $voluntario->id,
                'cargo_id'      => $cargo->id,
                'compania_id'   => $cargo->esDeCompania() ? $voluntario->compania_id : null,
                'fecha_inicio'  => now()->toDateString(),
                'fecha_fin'     => null,
                'activo'        => true,
            ]);
        }

        return redirect()->route('voluntarios.index')
                         ->with('success', 'Voluntario registrado exitosamente.');
    }

    public function show(Voluntario $voluntario)
    {
        $voluntario->load([
            'compania', 'roles', 'cargosActivos.cargo',
            'cargosActivos.compania', 'unidadesAutorizadas.compania', 'turnos.unidades'
        ]);
        $unidades = Unidad::with('compania')->where('activa', true)->get();
        return view('voluntarios.show', compact('voluntario', 'unidades'));
    }

    public function edit(Voluntario $voluntario)
    {
        $companias        = Compania::where('activa', true)->orderBy('numero')->get();
        $rolesDisponibles = ['maquinista', 'oficial'];
        $voluntario->load(['roles', 'cargosActivos.cargo']);

        $cargoActivo = $voluntario->cargosActivos->first();

        $cargosCompania  = Cargo::where('tipo', 'compania')->where('activo', true)->orderBy('orden')->get();
        $cargosGenerales = Cargo::where('tipo', 'general')->where('activo', true)->orderBy('orden')->get();

        $cargosGeneralesOcupados = VoluntarioCargo::whereHas('cargo', fn($q) => $q->where('tipo', 'general'))
            ->whereNull('compania_id')
            ->where('activo', true)
            ->where('voluntario_id', '!=', $voluntario->id)
            ->pluck('cargo_id')
            ->toArray();

        $cargosCompaniaOcupados = VoluntarioCargo::whereHas('cargo', fn($q) => $q->where('tipo', 'compania'))
            ->where('activo', true)
            ->where('voluntario_id', '!=', $voluntario->id)
            ->get()
            ->groupBy('cargo_id')
            ->map(fn($grupo) => $grupo->pluck('compania_id')->toArray());

        return view('voluntarios.edit', compact(
            'voluntario', 'companias', 'rolesDisponibles',
            'cargosCompania', 'cargosGenerales', 'cargoActivo',
            'cargosGeneralesOcupados', 'cargosCompaniaOcupados'
        ));
    }

    public function update(Request $request, Voluntario $voluntario)
    {
        // Unificar los dos selects de cargo en uno solo
        $request->merge([
            'cargo_id' => $request->cargo_compania_id ?: $request->cargo_general_id ?: null,
        ]);

        $request->validate([
            'compania_id'       => 'required|exists:companias,id',
            'nombre'            => 'required|string|max:255',
            'rut'               => 'nullable|string|unique:voluntarios,rut,' . $voluntario->id,
            'telefono'          => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'roles'             => 'nullable|array',
            'activo'            => 'boolean',
            'cargo_compania_id' => 'nullable|exists:cargos,id',
            'cargo_general_id'  => 'nullable|exists:cargos,id',
            'cargo_id'          => 'nullable|exists:cargos,id',
        ]);

        // Validar ocupación excluyendo al voluntario actual
        if ($request->filled('cargo_id')) {
            $cargo = Cargo::findOrFail($request->cargo_id);

            if ($cargo->esDeCompania()) {
                $ocupado = VoluntarioCargo::where('cargo_id', $cargo->id)
                    ->where('compania_id', $request->compania_id)
                    ->where('activo', true)
                    ->where('voluntario_id', '!=', $voluntario->id)
                    ->exists();

                if ($ocupado) {
                    return back()->withInput()->withErrors([
                        'cargo_id' => "El cargo '{$cargo->nombre}' ya tiene un titular activo en esa compañía.",
                    ]);
                }
            }

            if ($cargo->esGeneral()) {
                $ocupado = VoluntarioCargo::where('cargo_id', $cargo->id)
                    ->whereNull('compania_id')
                    ->where('activo', true)
                    ->where('voluntario_id', '!=', $voluntario->id)
                    ->exists();

                if ($ocupado) {
                    return back()->withInput()->withErrors([
                        'cargo_id' => "El cargo '{$cargo->nombre}' ya tiene un titular activo en el Cuerpo.",
                    ]);
                }
            }
        }

        $voluntario->update($request->only(
            'compania_id', 'nombre', 'rut', 'telefono', 'email', 'activo'
        ));

        $puedeAutorizarAntes = $voluntario->roles()
            ->where('rol', 'oficial')
            ->value('puede_autorizar_salidas') ?? false;

        $voluntario->roles()->delete();

        // Si tiene cargo, oficial se agrega automáticamente
        $roles = $request->roles ?? [];
        if ($request->filled('cargo_id') && !in_array('oficial', $roles)) {
            $roles[] = 'oficial';
        }

        foreach ($roles as $rol) {
            VoluntarioRol::create([
                'voluntario_id'           => $voluntario->id,
                'rol'                     => $rol,
                'rango'                   => null,
                'activo'                  => true,
                'puede_autorizar_salidas' => $rol === 'oficial' ? $puedeAutorizarAntes : false,
            ]);
        }

        // Cerrar cargo activo anterior
        $voluntario->cargosActivos()->update([
            'activo'    => false,
            'fecha_fin' => now()->toDateString(),
        ]);

        // Asignar nuevo cargo si corresponde
        if ($request->filled('cargo_id')) {
            $cargo = Cargo::find($request->cargo_id);
            VoluntarioCargo::create([
                'voluntario_id' => $voluntario->id,
                'cargo_id'      => $cargo->id,
                'compania_id'   => $cargo->esDeCompania() ? $voluntario->compania_id : null,
                'fecha_inicio'  => now()->toDateString(),
                'fecha_fin'     => null,
                'activo'        => true,
            ]);
        }

        return redirect()->route('voluntarios.index')
                         ->with('success', 'Voluntario actualizado exitosamente.');
    }

    public function autorizarUnidad(Request $request, Voluntario $voluntario)
    {
        $request->validate([
            'unidad_id'          => 'required|exists:unidades,id',
            'autorizado_por'     => 'nullable|string|max:255',
            'fecha_autorizacion' => 'nullable|date',
        ]);

        $voluntario->unidadesAutorizadas()->syncWithoutDetaching([
            $request->unidad_id => [
                'autorizado_por'     => $request->autorizado_por,
                'fecha_autorizacion' => $request->fecha_autorizacion,
            ]
        ]);

        return redirect()->back()->with('success', 'Unidad autorizada correctamente.');
    }

    public function revocarUnidad(Request $request, Voluntario $voluntario)
    {
        $voluntario->unidadesAutorizadas()->detach($request->unidad_id);
        return redirect()->back()->with('success', 'Autorización revocada.');
    }

    public function toggleAutorizante(Voluntario $voluntario)
    {
        $rol = $voluntario->roles()->where('rol', 'oficial')->first();

        if (!$rol) {
            return back()->with('error', 'El voluntario no tiene rol de oficial.');
        }

        $voluntario->roles()->where('rol', 'oficial')->update([
            'puede_autorizar_salidas' => !$rol->puede_autorizar_salidas,
        ]);

        $mensaje = $rol->puede_autorizar_salidas
            ? 'Oficial removido como autorizante de salidas.'
            : 'Oficial marcado como autorizante de salidas.';

        return back()->with('success', $mensaje);
    }
}