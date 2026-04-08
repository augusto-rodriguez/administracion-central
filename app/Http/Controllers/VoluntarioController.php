<?php

namespace App\Http\Controllers;

use App\Models\Voluntario;
use App\Models\VoluntarioRol;
use App\Models\Compania;
use App\Models\Unidad;
use Illuminate\Http\Request;

class VoluntarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Voluntario::with(['compania', 'roles', 'unidadesAutorizadas']);

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

        // Solo ofrecer comandante si hay rangos disponibles
        $rangosOcupados = VoluntarioRol::where('rol', 'comandante')
            ->where('activo', true)
            ->pluck('rango')
            ->toArray();

        if (count($rangosOcupados) < 3) {
            $rolesDisponibles[] = 'comandante';
        }

        return view('voluntarios.create', compact('companias', 'rolesDisponibles', 'rangosOcupados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'compania_id' => 'required|exists:companias,id',
            'nombre'      => 'required|string|max:255',
            'rut'         => 'nullable|string|unique:voluntarios,rut',
            'telefono'    => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'roles'       => 'nullable|array',
        ]);

        $voluntario = Voluntario::create($request->only(
            'compania_id', 'nombre', 'rut', 'telefono', 'email'
        ));

        // Validar que si es comandante, tenga rango
        if (in_array('comandante', $request->roles ?? []) && !$request->filled('rango_comandante')) {
            return back()->withInput()
                ->withErrors(['rango_comandante' => 'Debes seleccionar el rango del comandante.']);
        }

        // Validar que el rango no esté ocupado
        if (in_array('comandante', $request->roles ?? []) && $request->filled('rango_comandante')) {
            $rangoOcupado = VoluntarioRol::where('rol', 'comandante')
                ->where('rango', $request->rango_comandante)
                ->where('activo', true)
                ->exists();

            if ($rangoOcupado) {
                $voluntario->delete();
                $ordinal = ['1' => '1er', '2' => '2do', '3' => '3er'][$request->rango_comandante];
                return back()->withInput()
                    ->withErrors(['rango_comandante' => "Ya existe un {$ordinal} Comandante registrado."]);
            }
        }

       foreach ($request->roles ?? [] as $rol) {
            VoluntarioRol::create([
                'voluntario_id'           => $voluntario->id,
                'rol'                     => $rol,
                'rango'                   => $rol === 'comandante' ? $request->input('rango_comandante') : null,
                'activo'                  => true,
                'puede_autorizar_salidas' => false,
            ]);
        }

        return redirect()->route('voluntarios.index')
                         ->with('success', 'Voluntario registrado exitosamente.');
    }

    public function show(Voluntario $voluntario)
    {
        $voluntario->load(['compania', 'roles', 'unidadesAutorizadas.compania', 'turnos.unidades']);
        $unidades = Unidad::with('compania')->where('activa', true)->get();
        return view('voluntarios.show', compact('voluntario', 'unidades'));
    }

    public function edit(Voluntario $voluntario)
    {
        $companias        = Compania::where('activa', true)->orderBy('numero')->get();
        $rolesDisponibles = ['maquinista', 'oficial'];
        $voluntario->load('roles');

        $esComandante = $voluntario->roles->where('activo', true)->where('rol', 'comandante')->isNotEmpty();

        $rangosOcupados = VoluntarioRol::where('rol', 'comandante')
            ->where('activo', true)
            ->whereHas('voluntario', fn($q) => $q->where('id', '!=', $voluntario->id))
            ->pluck('rango')
            ->toArray();

        // Mostrar comandante si: ya es comandante, o si hay rangos libres
        if ($esComandante || count($rangosOcupados) < 3) {
            $rolesDisponibles[] = 'comandante';
        }

        return view('voluntarios.edit', compact('voluntario', 'companias', 'rolesDisponibles', 'rangosOcupados'));
    }

    public function update(Request $request, Voluntario $voluntario)
    {
        $request->validate([
            'compania_id' => 'required|exists:companias,id',
            'nombre'      => 'required|string|max:255',
            'rut'         => 'nullable|string|unique:voluntarios,rut,' . $voluntario->id,
            'telefono'    => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
            'roles'       => 'nullable|array',
            'activo'      => 'boolean',
        ]);

        $voluntario->update($request->only(
            'compania_id', 'nombre', 'rut', 'telefono', 'email', 'activo'
        ));

        // Preservar puede_autorizar_salidas antes de borrar roles
        $puedeAutorizarAntes = $voluntario->roles()
            ->where('rol', 'oficial')
            ->value('puede_autorizar_salidas') ?? false;

        $voluntario->roles()->delete();

        // Validar que si es comandante, tenga rango
        if (in_array('comandante', $request->roles ?? []) && !$request->filled('rango_comandante')) {
            return back()->withInput()
                ->withErrors(['rango_comandante' => 'Debes seleccionar el rango del comandante.']);
        }

        // Validar que el rango no esté ocupado por otro
        if (in_array('comandante', $request->roles ?? []) && $request->filled('rango_comandante')) {
            $rangoOcupado = VoluntarioRol::where('rol', 'comandante')
                ->where('rango', $request->rango_comandante)
                ->where('activo', true)
                ->whereHas('voluntario', fn($q) => $q->where('id', '!=', $voluntario->id))
                ->exists();

            if ($rangoOcupado) {
                $ordinal = ['1' => '1er', '2' => '2do', '3' => '3er'][$request->rango_comandante];
                return back()->withInput()
                    ->withErrors(['rango_comandante' => "Ya existe un {$ordinal} Comandante registrado."]);
            }
        }

        foreach ($request->roles ?? [] as $rol) {
            VoluntarioRol::create([
                'voluntario_id'           => $voluntario->id,
                'rol'                     => $rol,
                'rango'                   => $rol === 'comandante' ? $request->input('rango_comandante') : null,
                'activo'                  => true,
                'puede_autorizar_salidas' => $rol === 'oficial' ? $puedeAutorizarAntes : false,
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