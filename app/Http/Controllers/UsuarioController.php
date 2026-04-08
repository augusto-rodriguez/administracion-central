<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with('voluntario.compania')->orderBy('nombre')->get();
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        // Excluir voluntarios que ya tienen usuario vinculado
        $voluntariosConUsuario = User::whereNotNull('voluntario_id')->pluck('voluntario_id');

        $voluntarios = Voluntario::where('activo', true)
            ->whereNotIn('id', $voluntariosConUsuario)
            ->orderBy('nombre')
            ->get();

        return view('usuarios.create', compact('voluntarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'        => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:6|confirmed',
            'rol'           => 'required|in:admin,comandante,operador',
            'voluntario_id' => 'nullable|exists:voluntarios,id',
        ]);

        User::create([
            'nombre'        => $request->nombre,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'rol'           => $request->rol,
            'voluntario_id' => $request->voluntario_id,
            'activo'        => true,
        ]);

        // Si se vincula a un voluntario y el rol es comandante o admin,
        // asignar automáticamente puede_autorizar_salidas en su rol oficial
        if ($request->voluntario_id && in_array($request->rol, ['admin', 'comandante'])) {
            $this->asignarAutorizacionSalidas($request->voluntario_id, true);
        }

        return redirect()->route('usuarios.index')
                         ->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $usuario)
    {
        $voluntarios = Voluntario::where('activo', true)->orderBy('nombre')->get();
        return view('usuarios.edit', compact('usuario', 'voluntarios'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'nombre'        => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $usuario->id,
            'password'      => 'nullable|min:6|confirmed',
            'rol'           => 'required|in:admin,comandante,operador',
            'voluntario_id' => 'nullable|exists:voluntarios,id',
            'activo'        => 'boolean',
        ]);

        $rolAnterior        = $usuario->rol;
        $voluntarioAnterior = $usuario->voluntario_id;

        $data = $request->only('nombre', 'email', 'rol', 'voluntario_id', 'activo');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        $voluntarioId = $request->voluntario_id;
        $nuevoRol     = $request->rol;

        if ($voluntarioId) {
            if (in_array($nuevoRol, ['admin', 'comandante'])) {
                // Ascendió a comandante/admin → activar autorización
                $this->asignarAutorizacionSalidas($voluntarioId, true);
            } elseif (in_array($rolAnterior, ['admin', 'comandante']) && $nuevoRol === 'operador') {
                // Bajó a operador → revocar autorización
                $this->asignarAutorizacionSalidas($voluntarioId, false);
            }
        }

        // Si se desvinculó el voluntario anterior y era comandante/admin, revocar
        if ($voluntarioAnterior && $voluntarioAnterior !== $voluntarioId
            && in_array($rolAnterior, ['admin', 'comandante'])) {
            $this->asignarAutorizacionSalidas($voluntarioAnterior, false);
        }

        return redirect()->route('usuarios.index')
                         ->with('success', 'Usuario actualizado.');
    }

    /**
     * Activa o desactiva puede_autorizar_salidas en el rol oficial del voluntario.
     * Si no tiene rol oficial, no hace nada.
     */
    private function asignarAutorizacionSalidas(int $voluntarioId, bool $valor): void
    {
        $voluntario = Voluntario::find($voluntarioId);

        if (!$voluntario) return;

        $rolOficial = $voluntario->roles()->where('rol', 'oficial')->first();

        // Si no tiene rol oficial y estamos activando, crearlo automáticamente
        if (!$rolOficial && $valor) {
            $voluntario->roles()->create([
                'voluntario_id'          => $voluntarioId,
                'rol'                    => 'oficial',
                'activo'                 => true,
                'puede_autorizar_salidas' => true,
            ]);
            return;
        }

        // Si ya tiene rol oficial, solo actualizar el permiso
        if ($rolOficial) {
            $voluntario->roles()
                ->where('rol', 'oficial')
                ->update(['puede_autorizar_salidas' => $valor]);
        }
    }
}