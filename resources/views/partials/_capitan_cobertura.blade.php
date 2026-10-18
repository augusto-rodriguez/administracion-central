{{-- ══════════════════════════════════════════════════════════════════
     COBERTURA DE CONDUCTORES — Capitán de compañía
     Requiere: $cobertura (CoberturaConductoresService::calcular)
═══════════════════════════════════════════════════════════════════ --}}
@php
    $diasCortos = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];
    $diasLargos = \App\Services\CoberturaConductoresService::DIAS_SEMANA;
    $claseCelda = function ($pct) {
        if ($pct === null) return 'cob-na';
        return match (true) {
            $pct < 20 => 'cob-0',
            $pct < 40 => 'cob-1',
            $pct < 60 => 'cob-2',
            $pct < 80 => 'cob-3',
            default   => 'cob-4',
        };
    };
    $fmtHoras = fn($h) => number_format($h, $h < 10 ? 1 : 0, ',', '.') . ' h';
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <div class="text-muted small">
        Del {{ $cobertura['desde']->format('d/m/Y') }} al {{ $cobertura['hasta']->format('d/m/Y H:i') }}
        @unless($cobertura['sinUnidades'])
            · {{ $cobertura['nUnidades'] }} unidad(es) activa(s)
        @endunless
    </div>
    @include('partials._capitan_periodo', ['tab' => 'cobertura'])
</div>

@if($cobertura['sinUnidades'])
    <div class="alert alert-info mb-0">
        <i class="bi bi-info-circle me-1"></i>
        Tu compañía no tiene unidades activas registradas, por lo que no hay cobertura que calcular.
    </div>
@else

{{-- ── KPIs ─────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="text-muted small d-flex align-items-center gap-1">
                    Cobertura promedio
                    @include('partials._ayuda', ['texto' => 'Porcentaje del tiempo del período en que las unidades activas de tu compañía tuvieron un maquinista o cuartelero puesto en servicio con ellas. Se promedia entre todas las unidades.'])
                </div>
                <div class="fs-3 fw-bold {{ $cobertura['kpi']['coberturaPromedio'] < $cobertura['umbral'] ? 'text-danger' : '' }}">
                    {{ number_format($cobertura['kpi']['coberturaPromedio'], 1, ',', '.') }}%
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="text-muted small d-flex align-items-center gap-1">
                    Sin ningún conductor
                    @include('partials._ayuda', ['texto' => 'Tiempo total del período en que ninguna unidad de tu compañía tenía conductor en servicio, es decir, la compañía no podía despachar ningún carro con conductor registrado.'])
                </div>
                <div class="fs-3 fw-bold text-danger">{{ $fmtHoras($cobertura['kpi']['horasSinNinguna']) }}</div>
                <div class="text-muted" style="font-size:.75rem">
                    {{ number_format($cobertura['kpi']['pctSinNinguna'], 1, ',', '.') }}% del período
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="text-muted small">Franja más débil</div>
                @if($cobertura['kpi']['peorFranja'])
                    <div class="fw-bold" style="font-size:1.05rem">{{ $cobertura['kpi']['peorFranja']['texto'] }}</div>
                    <div class="text-muted" style="font-size:.75rem">
                        {{ $cobertura['kpi']['peorFranja']['pct'] }}% de cobertura
                    </div>
                @else
                    <div class="fw-bold">—</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body p-3">
                <div class="text-muted small">Conductores activos</div>
                <div class="fs-3 fw-bold">{{ $cobertura['kpi']['conductores'] }}</div>
                <div class="text-muted" style="font-size:.75rem">con al menos un turno en el período</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- ── Mapa de calor ─────────────────────────────────────────── --}}
    <div class="col-12 col-xl-7">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-grid-3x3-gap text-danger"></i>
                Cobertura por día y hora
                @include('partials._ayuda', ['texto' => 'Se calcula con los registros de puesta en servicio de maquinistas y cuarteleros (hora de entrada, hora de salida y unidades asignadas). Para cada día de la semana y cada hora se suma el tiempo en que cada unidad de tu compañía tuvo conductor, y se divide por el tiempo total de esa franja en el período. Ej.: 25% el lunes a las 04:00 significa que, en promedio, las unidades tuvieron conductor solo un cuarto de ese tiempo. Solo se consideran las unidades activas hoy; un turno sin salida registrada se cuenta como en servicio.'])
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-2 mapa-cobertura">
                        <thead>
                            <tr>
                                <th class="text-muted small fw-normal">Hora</th>
                                @foreach($diasCortos as $d => $nombre)
                                    <th class="text-center small">{{ $nombre }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cobertura['mapa'] as $hora => $fila)
                                <tr>
                                    <td class="text-muted small text-nowrap pe-2">{{ sprintf('%02d:00', $hora) }}</td>
                                    @foreach($fila as $d => $celda)
                                        <td class="text-center {{ $claseCelda($celda['pct']) }}"
                                            title="{{ $diasLargos[$d] }} {{ sprintf('%02d:00–%02d:00', $hora, ($hora + 1) % 24) }} · Unidades con conductor: {{ $celda['pct'] ?? '—' }}% · Al menos una unidad: {{ $celda['pctAlguna'] ?? '—' }}%">
                                            {{ $celda['pct'] !== null ? $celda['pct'] : '·' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2 small text-muted">
                    <span>% de cobertura:</span>
                    <span class="leyenda cob-0">0–19</span>
                    <span class="leyenda cob-1">20–39</span>
                    <span class="leyenda cob-2">40–59</span>
                    <span class="leyenda cob-3">60–79</span>
                    <span class="leyenda cob-4">80–100</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5 d-flex flex-column gap-4">
        {{-- ── Franjas críticas ────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header bg-white fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle text-warning"></i>
                Horarios con falta de conductores
                @include('partials._ayuda', ['texto' => 'Agrupa las horas consecutivas de un mismo día en que la cobertura promedio de tus unidades estuvo bajo ' . $cobertura['umbral'] . '%. Están ordenadas de la más débil a la menos débil. Son los horarios donde más conviene reforzar con maquinistas o cuarteleros.'])
            </div>
            <div class="card-body p-0">
                @if(count($cobertura['franjas']))
                    <ul class="list-group list-group-flush">
                        @foreach($cobertura['franjas'] as $f)
                            <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                <div>
                                    <span class="fw-bold">{{ $f['dia'] }}</span>
                                    <span class="ms-1">{{ $f['desde'] }} – {{ $f['hasta'] }}</span>
                                    <span class="text-muted small ms-1">({{ $f['horas'] }} h)</span>
                                </div>
                                <span class="badge {{ $f['pct'] < 15 ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $f['pct'] }}%</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center text-muted py-4 px-3">
                        <i class="bi bi-check-circle text-success fs-4"></i>
                        <p class="mb-0 mt-2 small">
                            No hay horarios bajo {{ $cobertura['umbral'] }}% de cobertura en el período.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Cobertura por unidad ────────────────────────────────── --}}
        <div class="card">
            <div class="card-header bg-white fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-truck-front text-primary"></i>
                Cobertura por unidad
                @include('partials._ayuda', ['texto' => 'Para cada unidad: porcentaje del período en que tuvo al menos un conductor registrado, y cuántas horas aportaron los maquinistas y los cuarteleros. Si ambos estuvieron a la vez, el tiempo cuenta una sola vez en la cobertura, por eso las horas de cada tipo pueden sumar más que el total.'])
            </div>
            <div class="card-body">
                @foreach($cobertura['unidades'] as $u)
                    <div class="{{ !$loop->last ? 'mb-3' : '' }}">
                        <div class="d-flex justify-content-between align-items-baseline small mb-1">
                            <span>
                                <span class="badge bg-primary">{{ $u['nombre'] }}</span>
                                @if($u['patente'])<span class="text-muted ms-1">{{ $u['patente'] }}</span>@endif
                            </span>
                            <span class="fw-bold">{{ number_format($u['pct'], 1, ',', '.') }}%</span>
                        </div>
                        <div class="progress" style="height:8px" role="progressbar"
                             aria-valuenow="{{ $u['pct'] }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar {{ $u['pct'] < $cobertura['umbral'] ? 'bg-danger' : ($u['pct'] < 60 ? 'bg-warning' : 'bg-success') }}"
                                 style="width: {{ min(100, $u['pct']) }}%"></div>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.72rem">
                            Maquinistas {{ $fmtHoras($u['horasMaquinista']) }}
                            · Cuarteleros {{ $fmtHoras($u['horasCuartelero']) }}
                            · Sin conductor {{ $fmtHoras($u['horasSin']) }}
                            · Último registro:
                            {{ $u['ultimo'] ? $u['ultimo']->format('d/m H:i') : 'sin registros' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif