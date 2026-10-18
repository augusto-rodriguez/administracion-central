<?php

namespace App\Services;

use App\Models\RegistroTurno;
use App\Models\RegistroTurnoCuartelero;
use App\Models\Unidad;
use Carbon\Carbon;

/**
 * Calcula la cobertura de conductores (maquinistas + cuarteleros) de las
 * unidades de una compañía, a partir de los registros de puesta en servicio.
 *
 * Una unidad está "cubierta" en un instante si existe al menos un registro de
 * turno (maquinista o cuartelero) abierto en ese instante que tenga asignada
 * esa unidad.
 */
class CoberturaConductoresService
{
    public const TZ = 'America/Santiago';

    /** % bajo el cual una franja horaria se considera crítica */
    public const UMBRAL_CRITICO = 30;

    /** % desde el cual una franja se considera "cubierta" en la vista simple */
    public const UMBRAL_CUBIERTO = 70;

    /** Bloques del día para la vista simple: clave => [nombre, hora inicio, hora fin] */
    public const BLOQUES = [
        'madrugada' => ['Madrugada', 0, 6],
        'manana'    => ['Mañana',    6, 12],
        'tarde'     => ['Tarde',     12, 18],
        'noche'     => ['Noche',     18, 24],
    ];

    /** Días en plural / minúscula para las frases de resumen */
    private const DIAS_PLURAL = [
        1 => 'lunes', 2 => 'martes', 3 => 'miércoles', 4 => 'jueves',
        5 => 'viernes', 6 => 'sábados', 7 => 'domingos',
    ];

    public const DIAS_SEMANA = [
        1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves',
        5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo',
    ];

    public function calcular(int $companiaId, int $dias = 30): array
    {
        $ahora  = Carbon::now(self::TZ);
        $fin    = $ahora->copy()->startOfHour();               // se excluye la hora en curso
        $inicio = $ahora->copy()->startOfDay()->subDays($dias);

        $inicioTs = $inicio->timestamp;
        $finTs    = $fin->timestamp;

        $unidades = Unidad::where('compania_id', $companiaId)
            ->where('activa', true)
            ->orderBy('nombre')
            ->get();

        $resultado = [
            'dias'       => $dias,
            'desde'      => $inicio,
            'hasta'      => $fin,
            'umbral'     => self::UMBRAL_CRITICO,
            'sinUnidades'=> $unidades->isEmpty(),
        ];

        if ($unidades->isEmpty()) {
            return $resultado;
        }

        $idsUnidades = $unidades->pluck('id')->all();
        $colUnidadId = (new Unidad)->getTable() . '.id';

        // ── Registros que se cruzan con el período y usan unidades de la compañía ──
        $filtro = function ($q) use ($inicio, $fin, $idsUnidades, $colUnidadId) {
            $q->where('entrada_at', '<', $fin->format('Y-m-d H:i:s'))
              ->where(fn($q2) => $q2->whereNull('salida_at')
                                    ->orWhere('salida_at', '>', $inicio->format('Y-m-d H:i:s')))
              ->whereHas('unidades', fn($q3) => $q3->whereIn($colUnidadId, $idsUnidades));
        };

        $fuentes = [
            'maquinista' => [RegistroTurno::class,           'voluntario',  'voluntario_id'],
            'cuartelero' => [RegistroTurnoCuartelero::class, 'cuartelero',  'cuartelero_id'],
        ];

        $intervalosUnidad  = [];   // [unidad_id][tipo][] = [a, b]
        $personas          = [];   // conductores distintos con turnos en el período
        $ultimoUnidad      = [];   // [unidad_id] = timestamp último fin de turno

        foreach ($fuentes as $tipo => [$modelo, $relacion, $fk]) {
            $registros = $modelo::where($filtro)->with('unidades')->get();

            foreach ($registros as $r) {
                $entrada = $this->ts($r->getRawOriginal('entrada_at'));
                $salRaw  = $r->getRawOriginal('salida_at');
                $salida  = $salRaw ? $this->ts($salRaw) : $ahora->timestamp;

                $a = max($entrada, $inicioTs);
                $b = min($salida, $finTs);

                $unidadesCia = $r->unidades->whereIn('id', $idsUnidades);

                foreach ($unidadesCia as $u) {
                    $ultimoUnidad[$u->id] = max($ultimoUnidad[$u->id] ?? 0, min($salida, $ahora->timestamp));
                    if ($b > $a) {
                        $intervalosUnidad[$u->id][$tipo][] = [$a, $b];
                    }
                }

                if ($b <= $a || $unidadesCia->isEmpty()) {
                    continue;
                }

                $personas[$tipo . '-' . $r->{$fk}] = true;
            }
        }

        // ── Minutos base de cada franja (día de semana × hora) en el período ──
        $base = $this->grillaVacia();
        $this->distribuir([[$inicioTs, $finTs]], $base);
        $minutosPeriodo = ($finTs - $inicioTs) / 60;

        // ── Cobertura por unidad y grillas ──
        $grillaUnidades = $this->grillaVacia();   // minutos-unidad cubiertos
        $grillaAlguna   = $this->grillaVacia();   // minutos con al menos una unidad cubierta
        $todos          = [];
        $detalleUnidades = [];

        foreach ($unidades as $u) {
            $porTipo = $intervalosUnidad[$u->id] ?? [];
            $maq  = $this->fusionar($porTipo['maquinista'] ?? []);
            $cua  = $this->fusionar($porTipo['cuartelero'] ?? []);
            $union = $this->fusionar(array_merge($maq, $cua));

            $this->distribuir($union, $grillaUnidades);
            $todos = array_merge($todos, $union);

            $minCubiertos = $this->minutos($union);

            $detalleUnidades[] = [
                'nombre'          => $u->nombre,
                'patente'         => $u->patente ?? null,
                'pct'             => $minutosPeriodo > 0 ? round($minCubiertos / $minutosPeriodo * 100, 1) : 0,
                'horasCubiertas'  => $minCubiertos / 60,
                'horasMaquinista' => $this->minutos($maq) / 60,
                'horasCuartelero' => $this->minutos($cua) / 60,
                'horasSin'        => max(0, $minutosPeriodo - $minCubiertos) / 60,
                'ultimo'          => isset($ultimoUnidad[$u->id])
                                        ? Carbon::createFromTimestamp($ultimoUnidad[$u->id], self::TZ)
                                        : null,
            ];
        }

        $unionTodas = $this->fusionar($todos);
        $this->distribuir($unionTodas, $grillaAlguna);

        $nUnidades = $unidades->count();

        // ── Mapa de calor ──
        $mapa = [];
        $celdas = [];
        foreach (range(0, 23) as $h) {
            foreach (array_keys(self::DIAS_SEMANA) as $d) {
                $den = $base[$d][$h];
                $pct = $den > 0 ? round($grillaUnidades[$d][$h] / ($den * $nUnidades) * 100) : null;
                $pctAlguna = $den > 0 ? round($grillaAlguna[$d][$h] / $den * 100) : null;
                $mapa[$h][$d] = ['pct' => $pct, 'pctAlguna' => $pctAlguna];
                if ($pct !== null) {
                    $celdas[] = ['dia' => $d, 'hora' => $h, 'pct' => $pct];
                }
            }
        }

        // ── Franjas críticas: horas consecutivas bajo el umbral, por día ──
        $franjas = [];
        foreach (array_keys(self::DIAS_SEMANA) as $d) {
            $run = [];
            foreach (range(0, 24) as $h) {
                $pct = $h < 24 ? $mapa[$h][$d]['pct'] : null;
                if ($pct !== null && $pct < self::UMBRAL_CRITICO) {
                    $run[] = $pct;
                    continue;
                }
                if ($run) {
                    $desde = $h - count($run);
                    $franjas[] = [
                        'dia'   => self::DIAS_SEMANA[$d],
                        'diaNum'=> $d,
                        'desde' => sprintf('%02d:00', $desde),
                        'hasta' => sprintf('%02d:00', $h),
                        'horas' => count($run),
                        'pct'   => round(array_sum($run) / count($run)),
                    ];
                    $run = [];
                }
            }
        }
        usort($franjas, fn($x, $y) => [$x['pct'], -$x['horas']] <=> [$y['pct'], -$y['horas']]);

        // Franja más crítica (celda individual)
        usort($celdas, fn($x, $y) => $x['pct'] <=> $y['pct']);
        $peor = $celdas[0] ?? null;

        $minUnidadCubiertos = array_sum(array_map(fn($du) => $du['horasCubiertas'] * 60, $detalleUnidades));
        $minSinNinguna = max(0, $minutosPeriodo - $this->minutos($unionTodas));

        // ── Vista simple: bloques del día × día de semana ──
        $bloques = [];
        $promedioBloque = [];
        foreach (self::BLOQUES as $clave => [$nombre, $hIni, $hFin]) {
            $cubTotal = 0; $denTotal = 0;
            foreach (array_keys(self::DIAS_SEMANA) as $d) {
                $cub = 0; $den = 0;
                for ($h = $hIni; $h < $hFin; $h++) {
                    $cub += $grillaUnidades[$d][$h];
                    $den += $base[$d][$h] * $nUnidades;
                }
                $pct = $den > 0 ? round($cub / $den * 100) : null;
                $bloques[$clave][$d] = ['pct' => $pct, 'nivel' => $this->nivel($pct)];
                $cubTotal += $cub; $denTotal += $den;
            }
            $promedioBloque[$clave] = $denTotal > 0 ? $cubTotal / $denTotal * 100 : null;
        }

        $kpiCobertura = $minutosPeriodo > 0
            ? round($minUnidadCubiertos / ($minutosPeriodo * $nUnidades) * 100, 1) : 0;

        return $resultado + [
            'nUnidades'        => $nUnidades,
            'mapa'             => $mapa,
            'franjas'          => array_slice($franjas, 0, 6),
            'bloques'          => $bloques,
            'resumen'          => $this->resumen($franjas, $promedioBloque, $kpiCobertura),
            'unidades'         => $detalleUnidades,
            'kpi' => [
                'coberturaPromedio' => $kpiCobertura,
                'horasSinNinguna'   => $minSinNinguna / 60,
                'pctSinNinguna'     => $minutosPeriodo > 0 ? round($minSinNinguna / $minutosPeriodo * 100, 1) : 0,
                'peorFranja'        => $peor ? [
                    'texto' => self::DIAS_SEMANA[$peor['dia']] . ' ' . sprintf('%02d:00–%02d:00', $peor['hora'], ($peor['hora'] + 1) % 24),
                    'pct'   => $peor['pct'],
                ] : null,
                'conductores'       => count($personas),
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────

    /** Nivel de la vista simple según el % de cobertura */
    private function nivel(?float $pct): string
    {
        if ($pct === null) return 'na';
        if ($pct < self::UMBRAL_CRITICO) return 'sin';
        if ($pct < self::UMBRAL_CUBIERTO) return 'parcial';
        return 'cubierto';
    }

    /**
     * Frases de resumen en lenguaje simple.
     * Devuelve una lista de frases (pueden contener <strong>, ya escapadas).
     */
    private function resumen(array $franjas, array $promedioBloque, float $cobertura): array
    {
        $e = fn($t) => e($t);
        $frases = [];

        $frases[] = 'En promedio, tus unidades tuvieron conductor el <strong>'
            . $e(number_format($cobertura, 0, ',', '.')) . '% del tiempo</strong>.';

        // Peores horarios: agrupar franjas con el mismo horario en distintos días
        if ($franjas) {
            $grupos = [];
            foreach ($franjas as $f) {
                $k = $f['desde'] . '-' . $f['hasta'];
                $grupos[$k] ??= ['desde' => $f['desde'], 'hasta' => $f['hasta'], 'dias' => [], 'pcts' => []];
                $grupos[$k]['dias'][$f['diaNum']] = self::DIAS_PLURAL[$f['diaNum']];
                $grupos[$k]['pcts'][] = $f['pct'];
            }
            foreach ($grupos as &$g) {
                ksort($g['dias']);
                $g['pct'] = array_sum($g['pcts']) / count($g['pcts']);
                $g['peso'] = count($g['dias']);
            }
            unset($g);
            // Primero los que se repiten en más días, luego los de menor cobertura
            usort($grupos, fn($x, $y) => [-$x['peso'], $x['pct']] <=> [-$y['peso'], $y['pct']]);

            $partes = [];
            foreach (array_slice($grupos, 0, 2) as $g) {
                $partes[] = '<strong>los ' . $e($this->unirLista(array_values($g['dias'])))
                    . ' de ' . $e($g['desde']) . ' a ' . $e($g['hasta']) . '</strong>';
            }
            $frases[] = 'Tus carros suelen quedar sin conductor ' . $this->unirLista($partes) . '.';
        } else {
            $frases[] = 'No hay horarios críticos: en todas las franjas la cobertura se mantiene sobre el '
                . self::UMBRAL_CRITICO . '%.';
        }

        // Mejor bloque del día
        $validos = array_filter($promedioBloque, fn($p) => $p !== null);
        if ($validos) {
            arsort($validos);
            $mejor = array_key_first($validos);
            [$nombre, $hIni, $hFin] = self::BLOQUES[$mejor];
            $frases[] = 'El momento con mejor cobertura es la <strong>' . $e(mb_strtolower($nombre))
                . ' (' . sprintf('%02d:00 a %02d:00', $hIni, $hFin) . ')</strong>, con un '
                . round($validos[$mejor]) . '% en promedio.';
        }

        return $frases;
    }

    /** ["a","b","c"] → "a, b y c" */
    private function unirLista(array $items): string
    {
        if (count($items) <= 1) return implode('', $items);
        $ultimo = array_pop($items);
        return implode(', ', $items) . ' y ' . $ultimo;
    }

    /** Fecha guardada en la BD (hora local Chile) → timestamp */
    private function ts(string $valor): int
    {
        return Carbon::parse($valor, self::TZ)->timestamp;
    }

    private function grillaVacia(): array
    {
        return array_fill(1, 7, array_fill(0, 24, 0.0));
    }

    /** Une intervalos superpuestos o contiguos */
    private function fusionar(array $intervalos): array
    {
        if (!$intervalos) return [];
        usort($intervalos, fn($x, $y) => $x[0] <=> $y[0]);

        $res = [$intervalos[0]];
        foreach (array_slice($intervalos, 1) as [$a, $b]) {
            $ultimo = &$res[count($res) - 1];
            if ($a <= $ultimo[1]) {
                $ultimo[1] = max($ultimo[1], $b);
            } else {
                $res[] = [$a, $b];
            }
            unset($ultimo);
        }
        return $res;
    }

    private function minutos(array $intervalos): float
    {
        return array_sum(array_map(fn($i) => ($i[1] - $i[0]) / 60, $intervalos));
    }

    /** Reparte los minutos de cada intervalo en la grilla día de semana × hora */
    private function distribuir(array $intervalos, array &$grilla): void
    {
        foreach ($intervalos as [$a, $b]) {
            $t = $a;
            while ($t < $b) {
                $c     = Carbon::createFromTimestamp($t, self::TZ);
                $sig   = $c->copy()->startOfHour()->addHour()->timestamp;
                $hasta = min($sig, $b);
                $grilla[$c->dayOfWeekIso][$c->hour] += ($hasta - $t) / 60;
                $t = $hasta;
            }
        }
    }
}