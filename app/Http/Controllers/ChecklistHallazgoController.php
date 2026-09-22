<?php

namespace App\Http\Controllers;

use App\Models\ChecklistHallazgo;
use App\Models\ChecklistHallazgoFoto;
use App\Models\ChecklistConfiguracion;
use App\Models\User;
use App\Mail\HallazgoAsignadoMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ChecklistHallazgoController extends Controller
{
    /**
     * Listado de hallazgos con filtros.
     */
    public function index(Request $request)
    {
        $query = ChecklistHallazgo::with([
            'item.seccion',
            'inspeccion.unidad',
            'inspeccion.cuartelero',
            'asignado',
        ]);

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        } else {
            $query->abiertos();
        }

        // Filtro por severidad
        if ($request->filled('severidad')) {
            $query->where('severidad', $request->severidad);
        }

        // Filtro por unidad
        if ($request->filled('unidad_id')) {
            $query->deUnidad($request->unidad_id);
        }

        $hallazgos = $query->orderByRaw("FIELD(severidad, 'critico', 'atencion', 'info')")
                           ->orderByDesc('created_at')
                           ->paginate(20);

        return view('hallazgos.index', compact('hallazgos'));
    }

    /**
     * Detalle de un hallazgo con su historial completo.
     */
    public function show(ChecklistHallazgo $hallazgo)
    {
        $hallazgo->load([
            'item.seccion',
            'inspeccion.unidad',
            'inspeccion.cuartelero',
            'asignado',
            'resolutorPor',
            'fotos',
            'comentarios.usuario',
        ]);

        // Obtener roles configurados para gestión de hallazgos
        $rolesPermitidos = $this->rolesGestionHallazgos();

        $usuariosAsignables = User::whereIn('rol', $rolesPermitidos)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('hallazgos.show', compact('hallazgo', 'usuariosAsignables'));
    }

    /**
     * Cambiar el estado de un hallazgo.
     */
    public function cambiarEstado(Request $request, ChecklistHallazgo $hallazgo)
    {
        $request->validate([
            'estado'     => 'required|in:abierto,en_revision,en_reparacion,resuelto,verificado',
            'comentario' => 'nullable|string|max:2000',
        ]);

        $hallazgo->cambiarEstado(
            $request->estado,
            Auth::user(),
            $request->comentario
        );

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    public function asignar(Request $request, ChecklistHallazgo $hallazgo)
    {
        $request->validate([
            'asignado_a'          => 'required|exists:users,id',
            'email_notificacion'  => 'nullable|email|max:255',
        ]);

        $usuarioAsignado = User::findOrFail($request->asignado_a);
        $usuarioAnterior = $hallazgo->asignado_a;

        $hallazgo->update(['asignado_a' => $request->asignado_a]);

        // Enviar correo y obtener el email usado
        $emailEnviado = null;
        if ($usuarioAnterior !== $request->asignado_a) {
            $emailDestino = $request->email_notificacion ?: $usuarioAsignado->email;
            $emailEnviado = $this->notificarAsignacion($hallazgo, $usuarioAsignado, $emailDestino);
        }

        // Registrar en comentarios con info del correo
        $comentario = "Hallazgo asignado a {$usuarioAsignado->nombre}.";
        if ($emailEnviado) {
            $comentario = "Hallazgo asignado a {$usuarioAsignado->nombre} y notificado al correo {$emailEnviado}.";
        }

        $hallazgo->comentarios()->create([
            'user_id'    => Auth::id(),
            'comentario' => $comentario,
        ]);

        return back()->with('success', $comentario);
    }

    /**
     * Agregar un comentario al hallazgo.
     */
    public function comentar(Request $request, ChecklistHallazgo $hallazgo)
    {
        $request->validate([
            'comentario' => 'required|string|max:2000',
        ]);

        $hallazgo->comentarios()->create([
            'user_id'    => Auth::id(),
            'comentario' => $request->comentario,
        ]);

        return back()->with('success', 'Comentario agregado.');
    }

    /**
     * Subir foto de resolución a un hallazgo.
     */
    public function subirFoto(Request $request, ChecklistHallazgo $hallazgo)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,jpg,png,webp|max:10240',
            'tipo' => 'nullable|in:problema,resolucion',
        ]);

        $ruta = $request->file('foto')->store(
            'checklist/hallazgos/' . now()->format('Y/m'),
            'public'
        );

        ChecklistHallazgoFoto::create([
            'hallazgo_id'     => $hallazgo->id,
            'ruta'            => $ruta,
            'nombre_original' => $request->file('foto')->getClientOriginalName(),
            'tipo'            => $request->tipo ?? 'resolucion',
        ]);

        return back()->with('success', 'Foto adjuntada al hallazgo.');
    }

    // ═════════════════════════════════════════════════════════════════
    // MÉTODOS PRIVADOS
    // ═════════════════════════════════════════════════════════════════

    /**
     * Obtiene los roles configurados para gestión de hallazgos.
     */
    private function rolesGestionHallazgos(): array
    {
        $rolesRaw = ChecklistConfiguracion::obtener('roles_gestion_hallazgos', 'admin,comandante,capitan_cia,operador');
        return array_filter(array_map('trim', explode(',', $rolesRaw)));
    }

    /**
     * Envía correo al usuario cuando se le asigna un hallazgo.
     */
    private function notificarAsignacion(ChecklistHallazgo $hallazgo, User $usuarioAsignado, ?string $emailDestino = null): ?string
    {
        $notificar = ChecklistConfiguracion::obtener('notificar_asignacion_hallazgo', 'no') === 'si';

        if (!$notificar) return null;

        $email = $emailDestino ?: $usuarioAsignado->email;

        if (empty($email)) return null;

        $hallazgo->load(['item.seccion', 'inspeccion.unidad', 'inspeccion.cuartelero']);

        try {
            Mail::to($email)
                ->send(new HallazgoAsignadoMail($hallazgo, Auth::user()));
            return $email;
        } catch (\Exception $e) {
            \Log::error("Error enviando correo de asignación de hallazgo: " . $e->getMessage());
            return null;
        }
    }
}