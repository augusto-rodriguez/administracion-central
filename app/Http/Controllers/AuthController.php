<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string|max:255',
            'password' => 'required',
        ], [], [
            'login' => 'email o RUT',
        ]);

        $identificador = trim($request->input('login'));
        $remember      = $request->boolean('remember');

        // ✅ Buscar usuario por email o por RUT (voluntarios / cuarteleros)
        $user = $this->buscarUsuario($identificador);

        if ($user && Auth::attempt([
            'email'    => $user->email,
            'password' => $request->input('password'),
            'activo'   => true,
        ], $remember)) {
            $request->session()->regenerate();

            // ✅ Registrar login exitoso
            $this->registrarLog($request, 'login', true, Auth::user());

            return redirect()->intended(route('dashboard'));
        }

        // ✅ Registrar intento fallido con motivo
        $motivo = ($user && ! $user->activo)
            ? 'cuenta_inactiva'
            : 'credenciales_invalidas';

        $this->registrarLog($request, 'failed', false, $user, $motivo);

        return back()->withErrors([
            'login' => 'Credenciales incorrectas o cuenta desactivada.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        // ✅ Registrar logout ANTES de cerrar sesión
        if ($user) {
            $this->registrarLog($request, 'logout', true, $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // ─── Búsqueda de usuario por email o RUT ─────────────────────────

    private function buscarUsuario(string $identificador): ?User
    {
        // Si trae @ se trata como email
        if (str_contains($identificador, '@')) {
            return User::where('email', $identificador)->first();
        }

        // Si no, se trata como RUT
        $rut = $this->limpiarRut($identificador);
        if (strlen($rut) < 2) return null;

        // Se compara el RUT limpio contra el RUT guardado sin puntos ni guion,
        // así funciona sin importar si en la BD está como 12.345.678-9 o 12345678-9
        $rutSql = "UPPER(REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '')) = ?";

        return User::where(function ($q) use ($rut, $rutSql) {
            $q->whereIn('voluntario_id', function ($sub) use ($rut, $rutSql) {
                $sub->select('id')->from('voluntarios')->whereRaw($rutSql, [$rut]);
            })->orWhereIn('cuartelero_id', function ($sub) use ($rut, $rutSql) {
                $sub->select('id')->from('cuarteleros')->whereRaw($rutSql, [$rut]);
            });
        })->first();
    }

    /**
     * Deja el RUT solo con números y K: "12.345.678-k" → "12345678K"
     */
    private function limpiarRut(string $rut): string
    {
        return strtoupper(preg_replace('/[^0-9kK]/', '', $rut));
    }

    // ─── Método privado para registrar el log ────────────────────────

    private function registrarLog(
        Request $request,
        string  $evento,
        bool    $exitoso,
        ?User   $user = null,
        ?string $motivoFallo = null,
    ): void {
        $ua = $request->userAgent() ?? '';

        LoginLog::create([
            'user_id'      => $user?->id,
            'evento'       => $evento,
            // Si se encontró el usuario se guarda su email; si no, lo que escribió (email o RUT)
            'email'        => $user?->email ?? $request->input('login'),
            'ip'           => $request->ip(),
            'user_agent'   => mb_substr($ua, 0, 512),
            'navegador'    => $this->parsearNavegador($ua),
            'plataforma'   => $this->parsearPlataforma($ua),
            'dispositivo'  => $this->parsearDispositivo($ua),
            'session_id'   => $request->session()->getId(),
            'exitoso'      => $exitoso,
            'motivo_fallo' => $motivoFallo,
            'created_at'   => now(),
        ]);
    }

    // ─── Parseo de User-Agent ────────────────────────────────────────

    private function parsearNavegador(string $ua): ?string
    {
        $navegadores = [
            'Edg'     => 'Edge',
            'OPR'     => 'Opera',
            'Opera'   => 'Opera',
            'Chrome'  => 'Chrome',
            'Safari'  => 'Safari',
            'Firefox' => 'Firefox',
            'MSIE'    => 'Internet Explorer',
            'Trident' => 'Internet Explorer',
        ];

        foreach ($navegadores as $clave => $nombre) {
            if (str_contains($ua, $clave)) return $nombre;
        }

        return null;
    }

    private function parsearPlataforma(string $ua): ?string
    {
        $plataformas = [
            'Windows'   => 'Windows',
            'Macintosh' => 'macOS',
            'Mac OS'    => 'macOS',
            'Linux'     => 'Linux',
            'Android'   => 'Android',
            'iPhone'    => 'iOS',
            'iPad'      => 'iPadOS',
        ];

        foreach ($plataformas as $clave => $nombre) {
            if (str_contains($ua, $clave)) return $nombre;
        }

        return null;
    }

    private function parsearDispositivo(string $ua): ?string
    {
        if (preg_match('/Mobile|Android.*Mobile|iPhone/i', $ua)) return 'mobile';
        if (preg_match('/iPad|Android(?!.*Mobile)|Tablet/i', $ua)) return 'tablet';

        return 'desktop';
    }
}