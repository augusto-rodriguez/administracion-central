<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;

class LoginLogController extends Controller
{
    public function index(Request $request)
    {
        $query = LoginLog::with('user')->latest('created_at');

        if ($request->filled('evento')) {
            $query->evento($request->evento);
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('ip_filter')) {
            $query->where('ip', $request->ip_filter);
        }

        if ($request->filled('desde')) {
            $query->desde($request->desde . ' 00:00:00');
        }

        if ($request->filled('hasta')) {
            $query->hasta($request->hasta . ' 23:59:59');
        }

        if ($request->filled('exitoso') && $request->exitoso !== '') {
            $query->where('exitoso', $request->boolean('exitoso'));
        }

        if ($request->filled('user_id')) {
            $query->deUsuario($request->user_id);
        }

        $logs = $query->paginate(25)->withQueryString();

        // Estadísticas rápidas (últimas 24h)
        $stats = [
            'logins_hoy'      => LoginLog::evento('login')->desde(now()->startOfDay())->count(),
            'fallidos_hoy'    => LoginLog::evento('failed')->desde(now()->startOfDay())->count(),
            'logouts_hoy'     => LoginLog::evento('logout')->desde(now()->startOfDay())->count(),
            'usuarios_unicos' => LoginLog::evento('login')->desde(now()->startOfDay())->distinct('user_id')->count('user_id'),
            'ips_sospechosas' => LoginLog::ipSospechosa(5)->desde(now()->subDay())->count(),
        ];

        // Lista de usuarios para el filtro
        $usuarios = User::orderBy('nombre')->get(['id', 'nombre']);

        return view('admin.login-logs.index', compact('logs', 'stats', 'usuarios'));
    }

    public function exportar(Request $request)
    {
        $query = LoginLog::with('user')->latest('created_at');

        if ($request->filled('evento')) {
            $query->evento($request->evento);
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('desde')) {
            $query->desde($request->desde . ' 00:00:00');
        }
        if ($request->filled('hasta')) {
            $query->hasta($request->hasta . ' 23:59:59');
        }
        if ($request->filled('user_id')) {
            $query->deUsuario($request->user_id);
        }

        $logs = $query->limit(10000)->get();

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            // BOM UTF-8 para que Excel lea bien los acentos
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, [
                'Fecha', 'Hora', 'Usuario', 'Email', 'Evento',
                'Resultado', 'IP', 'Navegador', 'Plataforma',
                'Dispositivo', 'Motivo fallo',
            ], ';');

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('d/m/Y'),
                    $log->created_at->format('H:i:s'),
                    $log->user?->name ?? '—',
                    $log->email,
                    $log->descripcion_evento,
                    $log->exitoso ? 'Exitoso' : 'Fallido',
                    $log->ip,
                    $log->navegador,
                    $log->plataforma,
                    $log->dispositivo,
                    $log->motivo_fallo,
                ], ';');
            }
            fclose($file);
        };

        $filename = 'registro_accesos_' . now()->format('Y-m-d_His') . '.csv';

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}