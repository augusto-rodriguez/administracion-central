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
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (Auth::attempt(array_merge($credentials, ['activo' => true]), $remember)) {
            $request->session()->regenerate();

            // ✅ Registrar login exitoso
            $this->registrarLog($request, 'login', true, Auth::user());

            return redirect()->intended(route('dashboard'));
        }

        // ✅ Registrar intento fallido con motivo
        $motivo = User::where('email', $request->email)
            ->where('activo', false)
            ->exists()
                ? 'cuenta_inactiva'
                : 'credenciales_invalidas';

        $user = User::where('email', $request->email)->first();
        $this->registrarLog($request, 'failed', false, $user, $motivo);

        return back()->withErrors([
            'email' => 'Credenciales incorrectas o cuenta desactivada.',
        ])->onlyInput('email');
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
            'email'        => $request->input('email'),
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