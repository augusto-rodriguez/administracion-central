<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Boletin;
use App\Models\Citacion;
use App\Models\RegistroTurno;
use App\Models\RegistroTurnoCuartelero;
use App\Models\BoletinMaquinista;
use App\Models\GuardiaComandante;  
use App\Models\VoluntarioRol;     
use Carbon\Carbon;                 

class BoletinController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // INDEX — Historial de boletines
    // ─────────────────────────────────────────────────────────────────────
    public function index()
    {
        $boletines = Boletin::with(['cuarteleros', 'maquinistas.voluntario', 'maquinistas.unidad'])
            ->orderByDesc('fecha')
            ->orderByDesc('tipo')
            ->paginate(20);

        // Verificar cuántos boletines hay hoy
        $boletinesHoy = Boletin::whereDate('fecha', today())->count();
        $limiteDiario = $boletinesHoy >= 2;

        return view('boletines.index', compact('boletines', 'limiteDiario'));
    }
    // ─────────────────────────────────────────────────────────────────────
    // CREATE — Formulario de generación
    // ─────────────────────────────────────────────────────────────────────
    public function create()
    {
        $cuarteleros = RegistroTurnoCuartelero::with('cuartelero.compania')
            ->whereNull('salida_at')
            ->get();

        $maquinistas = RegistroTurno::with(['voluntario', 'unidades'])
            ->whereNull('salida_at')
            ->get();

        $citaciones = Citacion::with('compania')
            ->where(function($q) {
                $q->whereNull('fecha_citacion')
                ->orWhere('fecha_citacion', '>=', now());
            })
            ->orderBy('compania_id')
            ->get();

        // ── Detectar si es domingo PM ────────────────────────────────
        // $esDomingoPM  = now('America/Santiago')->dayOfWeek === Carbon::SUNDAY;
        $esDomingoPM  = true;
        $comandantes  = collect();
        $guardiaActual = null;
        $proximoComandante = null;

        if ($esDomingoPM) {
            $guardiaActual = GuardiaComandante::activa();

            $comandantes = VoluntarioRol::where('rol', 'comandante')
                ->where('activo', true)
                ->with('voluntario')
                ->orderBy('rango')
                ->get();

            // Calcular quién sigue según correlativo
            $rangoActual = $guardiaActual?->voluntario
                ?->roles->firstWhere('rol', 'comandante')?->rango ?? 0;

            $siguienteRango = ($rangoActual % 3) + 1;
            $proximoComandante = $comandantes->firstWhere('rango', $siguienteRango);
        }

        return view('boletines.create', compact(
            'cuarteleros', 'maquinistas', 'citaciones',
            'esDomingoPM', 'comandantes', 'guardiaActual', 'proximoComandante'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // STORE — Guarda y genera el boletín
    // ─────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'tipo'  => 'required|in:am,pm',
        ]);

        $boletin = Boletin::firstOrCreate([
            'fecha' => $request->fecha,
            'tipo'  => $request->tipo,
        ]);

        // Guardar cuarteleros
        if ($request->has('cuarteleros')) {
            $ids = collect($request->cuarteleros)
                ->pluck('cuartelero_id')
                ->filter()
                ->toArray();
            $boletin->cuarteleros()->sync($ids);
        }

        // Guardar maquinistas
        $boletin->maquinistas()->delete();
        if ($request->has('maquinistas')) {
            foreach ($request->maquinistas as $m) {
                $registro = RegistroTurno::with('unidades')
                    ->where('voluntario_id', $m['voluntario_id'])
                    ->whereNull('salida_at')
                    ->first();

                $unidadId      = $registro?->unidades->first()?->id;
                $unidadesTexto = $registro?->unidades->pluck('nombre')->implode(' ') ?? '';

                if ($unidadId) {
                    BoletinMaquinista::create([
                        'boletin_id'     => $boletin->id,
                        'voluntario_id'  => $m['voluntario_id'],
                        'unidad_id'      => $unidadId,
                        'unidades_texto' => $unidadesTexto,
                        'estado'         => '6-20',
                    ]);
                }
            }
        }

        // ── Cambio de guardia domingo PM ─────────────────────────────
        $textoGuardia = null;

        if ($request->boolean('cambiar_guardia') && $request->filled('nuevo_comandante_id')) {
            $ahora         = Carbon::now('America/Santiago');
            $domingoInicio = $ahora->copy()->startOfWeek(Carbon::SUNDAY);
            $fechaInicio   = $domingoInicio->toDateString();
            $fechaFin      = $domingoInicio->copy()->addDays(7)->toDateString();

            GuardiaComandante::updateOrCreate(
                ['fecha_inicio' => $fechaInicio],
                [
                    'voluntario_id' => $request->nuevo_comandante_id,
                    'fecha_fin'     => $fechaFin,
                ]
            );

            // Construir texto para el boletín
            $rolComandante = VoluntarioRol::where('voluntario_id', $request->nuevo_comandante_id)
                ->where('rol', 'comandante')
                ->first();

            $ordinal = ['1' => '1ER', '2' => '2DO', '3' => '3ER'][$rolComandante->rango] ?? '';
            $nombre  = strtoupper($rolComandante->voluntario->nombre ?? '');

            Carbon::setLocale('es');
            $desdeTexto = Carbon::parse($fechaInicio)->translatedFormat('l d \\d\\e F');
            $hastaTexto = Carbon::parse($fechaFin)->translatedFormat('l d \\d\\e F');

            $textoGuardia = strtoupper(
                "ASUME GUARDIA DE COMANDANCIA DESDE HOY {$desdeTexto} AL {$hastaTexto}, " .
                "{$ordinal} COMANDANTE SR. {$nombre}."
            );

            // Guardar en el boletín
            $boletin->update(['texto_guardia' => $textoGuardia]);
        }

        // Generar texto del boletín
        $boletin->load(['cuarteleros.compania', 'maquinistas.voluntario', 'maquinistas.unidad']);
        $textoBoletin = $boletin->generarTexto();

        return redirect()->route('boletines.show', $boletin)
            ->with('textoBoletin', $textoBoletin)
            ->with('boletinGenerado', true);
    }

    // ─────────────────────────────────────────────────────────────────────
    // SHOW — Ver boletín generado (también abre modal si viene de store)
    // ─────────────────────────────────────────────────────────────────────
    public function show(Boletin $boletin)
    {
        $boletin->load(['cuarteleros.compania', 'maquinistas.voluntario', 'maquinistas.unidad']);

        // Si no viene de store, generar el texto igual para poder releerlo
        $textoBoletin = session('textoBoletin') ?? $boletin->generarTexto();
        $boletinGenerado = session('boletinGenerado', false);

        return view('boletines.show', compact('boletin', 'textoBoletin', 'boletinGenerado'));
    }

    public function destroy(Boletin $boletin)
    {
        $boletin->maquinistas()->delete();
        $boletin->cuarteleros()->detach();
        $boletin->delete();

        return redirect()->route('boletines.index')
            ->with('success', 'Boletín eliminado. Puedes volver a generarlo.');
    }
}