<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Boletin;
use App\Models\Citacion;
use App\Models\RegistroTurno;
use App\Models\RegistroTurnoCuartelero;
use App\Models\BoletinMaquinista;
use App\Models\GuardiaComandante;
use App\Models\Cargo;
use App\Models\VoluntarioCargo;
use Carbon\Carbon;

class BoletinController extends Controller
{
    public function index()
    {
        $boletines = Boletin::with(['cuarteleros', 'maquinistas.voluntario', 'maquinistas.unidad'])
            ->orderByDesc('fecha')
            ->orderByDesc('tipo')
            ->paginate(20);

        $boletinesHoy = Boletin::whereDate('fecha', today())->count();
        $limiteDiario = $boletinesHoy >= 2;

        return view('boletines.index', compact('boletines', 'limiteDiario'));
    }

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

        $esDomingoPM  = now('America/Santiago')->dayOfWeek === Carbon::SUNDAY;
        $comandantes  = collect();
        $guardiaActual = null;
        $proximoComandante = null;

        if ($esDomingoPM) {
            $guardiaActual = GuardiaComandante::activa();

            // Buscar cargos de comandancia (generales)
            $cargosComandante = Cargo::where('tipo', 'general')
                ->where('activo', true)
                ->whereIn('nombre', [
                    'Comandante',
                    '1er Comandante',
                    '2do Comandante',
                    '3er Comandante',
                    'Segundo Comandante',
                    'Tercer Comandante',
                ])
                ->orderBy('orden')
                ->get();

            $comandantes = VoluntarioCargo::whereIn('cargo_id', $cargosComandante->pluck('id'))
                ->whereNull('compania_id')
                ->where('activo', true)
                ->with(['voluntario', 'cargo'])
                ->orderBy('cargo_id')
                ->get();

            // Calcular quién sigue según correlativo
            $cargoActualId = $guardiaActual?->voluntario
                ?->cargosActivos
                ->whereNull('compania_id')
                ->first()?->cargo_id ?? null;

            $idsOrdenados      = $cargosComandante->pluck('id')->values();
            $indexActual       = $idsOrdenados->search($cargoActualId);
            $siguienteId       = $idsOrdenados[($indexActual + 1) % $idsOrdenados->count()] ?? null;
            $proximoComandante = $comandantes->firstWhere('cargo_id', $siguienteId);
        }

        return view('boletines.create', compact(
            'cuarteleros', 'maquinistas', 'citaciones',
            'esDomingoPM', 'comandantes', 'guardiaActual', 'proximoComandante'
        ));
    }

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

            // Buscar cargo del nuevo comandante
            $cargoComandante = VoluntarioCargo::where('voluntario_id', $request->nuevo_comandante_id)
                ->whereNull('compania_id')
                ->where('activo', true)
                ->with(['cargo', 'voluntario'])
                ->first();

            $nombreCargo = strtoupper($cargoComandante?->cargo->nombre ?? 'COMANDANTE');
            $nombre      = strtoupper($cargoComandante?->voluntario->nombre ?? '');

            Carbon::setLocale('es');
            $desdeTexto = Carbon::parse($fechaInicio)->translatedFormat('l d \\d\\e F');
            $hastaTexto = Carbon::parse($fechaFin)->translatedFormat('l d \\d\\e F');

            $textoGuardia = strtoupper(
                "ASUME GUARDIA DE COMANDANCIA DESDE HOY {$desdeTexto} AL {$hastaTexto}, " .
                "{$nombreCargo} SR. {$nombre}."
            );

            $boletin->update(['texto_guardia' => $textoGuardia]);
        }

        $boletin->load(['cuarteleros.compania', 'maquinistas.voluntario', 'maquinistas.unidad']);
        $textoBoletin = $boletin->generarTexto();

        return redirect()->route('boletines.show', $boletin)
            ->with('textoBoletin', $textoBoletin)
            ->with('boletinGenerado', true);
    }

    public function show(Boletin $boletin)
    {
        $boletin->load(['cuarteleros.compania', 'maquinistas.voluntario', 'maquinistas.unidad']);

        $textoBoletin    = session('textoBoletin') ?? $boletin->generarTexto();
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