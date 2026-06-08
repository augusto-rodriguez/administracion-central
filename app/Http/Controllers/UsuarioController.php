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
            ->with('compania')
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
            'rol'           => 'required|in:admin,comandante,capitan_cia,operador',
            'voluntario_id' => 'nullable|exists:voluntarios,id',
        ]);

        // Validar unicidad de capitan_cia por compañía
        if ($request->rol === 'capitan_cia' && $request->voluntario_id) {
            $error = $this->validarCapitanUnico($request->voluntario_id);
            if ($error) {
                return back()->withErrors(['rol' => $error])->withInput();
            }
        }

        User::create([
            'nombre'        => $request->nombre,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'rol'           => $request->rol,
            'voluntario_id' => $request->voluntario_id,
            'activo'        => true,
        ]);

        if ($request->voluntario_id && in_array($request->rol, ['admin', 'comandante', 'capitan_cia'])) {
            $this->asignarAutorizacionSalidas($request->voluntario_id, true);
        }

        return redirect()->route('usuarios.index')
                         ->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $usuario)
    {
        $voluntarios = Voluntario::where('activo', true)
            ->with('compania')
            ->orderBy('nombre')
            ->get();

        return view('usuarios.edit', compact('usuario', 'voluntarios'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'nombre'        => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $usuario->id,
            'password'      => 'nullable|min:6|confirmed',
            'rol'           => 'required|in:admin,comandante,capitan_cia,operador',
            'voluntario_id' => 'nullable|exists:voluntarios,id',
            'activo'        => 'boolean',
        ]);

        // Validar unicidad de capitan_cia por compañía (excluyendo el usuario actual)
        if ($request->rol === 'capitan_cia' && $request->voluntario_id) {
            $error = $this->validarCapitanUnico($request->voluntario_id, $usuario->id);
            if ($error) {
                return back()->withErrors(['rol' => $error])->withInput();
            }
        }

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
            if (in_array($nuevoRol, ['admin', 'comandante', 'capitan_cia'])) {
                $this->asignarAutorizacionSalidas($voluntarioId, true);
            } elseif (in_array($rolAnterior, ['admin', 'comandante', 'capitan_cia']) && $nuevoRol === 'operador') {
                $this->asignarAutorizacionSalidas($voluntarioId, false);
            }
        }

        // Si se desvinculó el voluntario anterior y era un rol con autorización, revocar
        if ($voluntarioAnterior && $voluntarioAnterior !== $voluntarioId
            && in_array($rolAnterior, ['admin', 'comandante', 'capitan_cia'])) {
            $this->asignarAutorizacionSalidas($voluntarioAnterior, false);
        }

        return redirect()->route('usuarios.index')
                         ->with('success', 'Usuario actualizado.');
    }

    /**
     * Verifica que no exista otro usuario con rol capitan_cia en la misma compañía.
     * Retorna el mensaje de error o null si todo está OK.
     */
    private function validarCapitanUnico(int $voluntarioId, ?int $excluirUserId = null): ?string
    {
        $voluntario = Voluntario::with('compania')->find($voluntarioId);

        if (!$voluntario || !$voluntario->compania_id) {
            return null; // Sin compañía, no aplica la restricción
        }

        $query = User::where('rol', 'capitan_cia')
            ->whereHas('voluntario', fn($q) =>
                $q->where('compania_id', $voluntario->compania_id)
            );

        if ($excluirUserId) {
            $query->where('id', '!=', $excluirUserId);
        }

        $capitan = $query->with('voluntario')->first();

        if ($capitan) {
            return "Ya existe un Capitán de Cía para la compañía \"{$voluntario->compania->nombre}\": 
                    {$capitan->voluntario->nombre}. Solo puede haber un Capitán por compañía.";
        }

        return null;
    }

    /**
     * Activa o desactiva puede_autorizar_salidas en el rol oficial del voluntario.
     */
    private function asignarAutorizacionSalidas(int $voluntarioId, bool $valor): void
    {
        $voluntario = Voluntario::find($voluntarioId);

        if (!$voluntario) return;

        $rolOficial = $voluntario->roles()->where('rol', 'oficial')->first();

        if (!$rolOficial && $valor) {
            $voluntario->roles()->create([
                'voluntario_id'           => $voluntarioId,
                'rol'                     => 'oficial',
                'activo'                  => true,
                'puede_autorizar_salidas' => true,
            ]);
            return;
        }

        if ($rolOficial) {
            $voluntario->roles()
                ->where('rol', 'oficial')
                ->update(['puede_autorizar_salidas' => $valor]);
        }
    }

    public function destroy(User $usuario)
    {
        // No permitir eliminar al propio usuario logueado
        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index')
                             ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        // No permitir eliminar al admin principal (rol admin)
        if ($usuario->rol === 'admin') {
            return redirect()->route('usuarios.index')
                             ->with('error', 'No se puede eliminar un usuario Administrador.');
        }

        $nombre = $usuario->nombre;

        // Solo se elimina el usuario — el voluntario vinculado NO se toca
        $usuario->delete();

        return redirect()->route('usuarios.index')
                         ->with('success', "Usuario \"{$nombre}\" eliminado. Su registro de voluntario (si tenía) se mantiene intacto.");
    }

    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required',
            'password_nuevo'  => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password_actual, $user->password)) {
            return back()
                ->with('password_error', 'La contraseña actual no es correcta.')
                ->with('abrir_modal_usuario', true);
        }

        $user->update([
            'password' => Hash::make($request->password_nuevo),
        ]);

        return back()
            ->with('password_success', 'Contraseña actualizada correctamente.')
            ->with('abrir_modal_usuario', true);
    }
}