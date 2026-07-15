@extends('layouts.app')
@section('title', 'Reportes — Guardias Nocturnas')
@section('content')

<div class="mb-4">
    <h4 class="mb-0"><i class="bi bi-moon-stars me-2"></i>Reportes — Guardias Nocturnas</h4>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-4" id="tabsGuardia">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabEstadisticas">
            <i class="bi bi-graph-up me-1"></i><span class="d-none d-sm-inline">Estadísticas</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabHistorial">
            <i class="bi bi-clock-history me-1"></i><span class="d-none d-sm-inline">Historial</span>
        </button>
    </li>
</ul>

<div class="tab-content">

    {{-- ══ TAB ESTADÍSTICAS ══════════════════════════════════════════ --}}
    <div class="tab-pane fade show active" id="tabEstadisticas">

        {{-- Filtros --}}
        <div class="card mb-4">
            <div class="card-header bg-light fw-bold">
                <i class="bi bi-funnel me-2"></i>Filtros
            </div>
            <div class="card-body py-3">
                <form method="GET" action="{{ route('reportes.guardias-nocturnas') }}">
                    <input type="hidden" name="tab" value="estadisticas">
                    <div class="row g-2 align-items-end">
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold mb-1 small">Año</label>
                            <select name="anio" class="form-select form-select-sm">
                                @foreach($anios as $a)
                                    <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold mb-1 small">Mes</label>
                            <select name="mes" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                @foreach($meses as $num => $nombre)
                                    <option value="{{ $num }}" {{ $mes == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-8 col-md-4">
                            <label class="form-label fw-bold mb-1 small">Compañía</label>
                            <select name="compania_id" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                @foreach($companias as $c)
                                    <option value="{{ $c->id }}" {{ $companiaId == $c->id ? 'selected' : '' }}>
                                        {{ $c->numero }}ª — {{ $c->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4 col-md-2">
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="bi bi-search me-1"></i>Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Resumen por compañía --}}
        <div class="card mb-4">
            <div class="card-header bg-light fw-bold">
                <i class="bi bi-building me-2"></i>Resumen por Compañía
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 d-none d-md-table">
                        <thead class="table-light">
                            <tr>
                                <th>Compañía</th>
                                <th class="text-center">Total guardias</th>
                                <th class="text-center">Con reporte</th>
                                <th class="text-center">Sin reporte</th>
                                <th class="text-center">Prom. voluntarios</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resumenCompanias as $r)
                            <tr>
                                <td class="fw-bold text-nowrap">{{ $r['numero'] }}ª — {{ $r['compania'] }}</td>
                                <td class="text-center">{{ $r['total_guardias'] }}</td>
                                <td class="text-center"><span class="badge bg-success">{{ $r['con_reporte'] }}</span></td>
                                <td class="text-center">
                                    @if($r['sin_reporte'] > 0)
                                        <span class="badge bg-danger">{{ $r['sin_reporte'] }}</span>
                                    @else
                                        <span class="badge bg-secondary">0</span>
                                    @endif
                                </td>
                                <td class="text-center"><span class="badge bg-primary">{{ $r['promedio_vol'] }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Sin datos para el período seleccionado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-md-none">
                    @forelse($resumenCompanias as $r)
                        <div class="border-bottom px-3 py-2">
                            <div class="fw-bold mb-1">{{ $r['numero'] }}ª — {{ $r['compania'] }}</div>
                            <div class="d-flex flex-wrap gap-2 small">
                                <span>Total: <strong>{{ $r['total_guardias'] }}</strong></span>
                                <span class="badge bg-success">{{ $r['con_reporte'] }} con rep.</span>
                                @if($r['sin_reporte'] > 0)
                                    <span class="badge bg-danger">{{ $r['sin_reporte'] }} sin rep.</span>
                                @endif
                                <span class="badge bg-primary">{{ $r['promedio_vol'] }} prom.</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">Sin datos.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="row g-3 g-md-4 mb-4">
            {{-- Ranking asistencia --}}
            <div class="col-12 col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-trophy me-2 text-warning"></i>Ranking Asistencia
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0" style="font-size:0.85rem;">
                                <thead class="table-light">
                                    <tr><th>#</th><th>Voluntario</th><th>Compañía</th><th class="text-center">Guardias</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($rankingAsistencia as $i => $r)
                                    <tr>
                                        <td>
                                            @if($i === 0) <i class="bi bi-trophy-fill text-warning"></i>
                                            @elseif($i === 1) <i class="bi bi-trophy-fill text-secondary"></i>
                                            @elseif($i === 2) <i class="bi bi-trophy-fill" style="color:#cd7f32"></i>
                                            @else <span class="text-muted small">{{ $i + 1 }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold">{{ $r['nombre'] }}</td>
                                        <td class="text-muted small">{{ $r['compania'] }}</td>
                                        <td class="text-center"><span class="badge bg-success">{{ $r['total'] }}</span></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">Sin datos.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Oficiales más frecuentes --}}
            <div class="col-12 col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light fw-bold">
                        <i class="bi bi-shield me-2 text-primary"></i>Oficiales a Cargo
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0" style="font-size:0.85rem;">
                                <thead class="table-light">
                                    <tr><th>#</th><th>Oficial</th><th>Compañía</th><th class="text-center">Veces</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($rankingOficiales as $i => $r)
                                    <tr>
                                        <td>
                                            @if($i === 0) <i class="bi bi-trophy-fill text-warning"></i>
                                            @elseif($i === 1) <i class="bi bi-trophy-fill text-secondary"></i>
                                            @elseif($i === 2) <i class="bi bi-trophy-fill" style="color:#cd7f32"></i>
                                            @else <span class="text-muted small">{{ $i + 1 }}</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold">{{ $r['nombre'] }}</td>
                                        <td class="text-muted small">{{ $r['compania'] }}</td>
                                        <td class="text-center"><span class="badge bg-primary">{{ $r['total'] }}</span></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">Sin datos.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Maquinistas más frecuentes --}}
        <div class="card mb-4">
            <div class="card-header bg-light fw-bold">
                <i class="bi bi-truck-front me-2 text-danger"></i>Maquinistas en Unidades
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 d-none d-md-table" style="font-size:0.85rem;">
                        <thead class="table-light">
                            <tr><th>#</th><th>Maquinista</th><th>Compañía</th><th>Unidades</th><th class="text-center">Noches</th></tr>
                        </thead>
                        <tbody>
                            @forelse($rankingMaquinistas as $i => $r)
                            <tr>
                                <td>
                                    @if($i === 0) <i class="bi bi-trophy-fill text-warning"></i>
                                    @elseif($i === 1) <i class="bi bi-trophy-fill text-secondary"></i>
                                    @elseif($i === 2) <i class="bi bi-trophy-fill" style="color:#cd7f32"></i>
                                    @else <span class="text-muted small">{{ $i + 1 }}</span>
                                    @endif
                                </td>
                                <td class="fw-bold">{{ $r['nombre'] }}</td>
                                <td class="text-muted small">{{ $r['compania'] }}</td>
                                <td class="text-muted small">{{ $r['unidades'] }}</td>
                                <td class="text-center"><span class="badge bg-danger">{{ $r['total'] }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Sin datos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-md-none">
                    @forelse($rankingMaquinistas as $i => $r)
                        <div class="border-bottom px-3 py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="text-muted small me-1">
                                        @if($i === 0) <i class="bi bi-trophy-fill text-warning"></i>
                                        @elseif($i === 1) <i class="bi bi-trophy-fill text-secondary"></i>
                                        @elseif($i === 2) <i class="bi bi-trophy-fill" style="color:#cd7f32"></i>
                                        @else {{ $i + 1 }}.
                                        @endif
                                    </span>
                                    <span class="fw-bold">{{ $r['nombre'] }}</span>
                                    <div class="text-muted small">{{ $r['compania'] }} · {{ $r['unidades'] }}</div>
                                </div>
                                <span class="badge bg-danger flex-shrink-0">{{ $r['total'] }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">Sin datos.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Evolución mensual --}}
        <div class="card mb-4">
            <div class="card-header bg-light fw-bold">
                <i class="bi bi-graph-up me-2 text-success"></i>
                Promedio voluntarios — {{ $anio }}
            </div>
            <div class="card-body">
                <canvas id="graficoEvolucion" height="80"></canvas>
            </div>
        </div>

    </div>{{-- fin tab estadísticas --}}

    {{-- ══ TAB HISTORIAL ═════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tabHistorial">
        {{-- Filtro por fecha --}}
        <div class="card mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('reportes.guardias-nocturnas') }}">
                    <input type="hidden" name="tab" value="historial">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold mb-1 small">Fecha exacta</label>
                            <input type="date" name="hist_fecha" class="form-control form-control-sm"
                                value="{{ request('hist_fecha') }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold mb-1 small">Desde</label>
                            <input type="date" name="hist_desde" class="form-control form-control-sm"
                                value="{{ request('hist_desde') }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold mb-1 small">Hasta</label>
                            <input type="date" name="hist_hasta" class="form-control form-control-sm"
                                value="{{ request('hist_hasta') }}">
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-danger btn-sm flex-grow-1">
                                    <i class="bi bi-search me-1"></i>Filtrar
                                </button>
                                <a href="{{ route('reportes.guardias-nocturnas') }}?tab=historial"
                                class="btn btn-outline-secondary btn-sm {{ request()->hasAny(['hist_desde', 'hist_hasta', 'hist_fecha']) ? '' : 'invisible' }}">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">

                {{-- Tabla desktop --}}
                <div class="table-responsive">
                    <table class="table table-hover mb-0 d-none d-md-table">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th class="text-center">Con reporte</th>
                                <th class="text-center">Sin reporte</th>
                                <th>Cerrado por</th>
                                <th>Cerrado a las</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historial as $guardia)
                            <tr>
                                <td class="fw-bold text-nowrap">{{ $guardia->fecha->format('d/m/Y') }}</td>
                                <td>
                                    @if($guardia->estado === 'abierta')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-unlock me-1"></i>Abierta</span>
                                    @else
                                        <span class="badge bg-success"><i class="bi bi-lock me-1"></i>Cerrada</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $guardia->companias_count - $guardia->sin_reporte_count }}</span>
                                </td>
                                <td class="text-center">
                                    @if($guardia->sin_reporte_count > 0)
                                        <span class="badge bg-danger">{{ $guardia->sin_reporte_count }}</span>
                                    @else
                                        <span class="badge bg-secondary">0</span>
                                    @endif
                                </td>
                                <td class="text-muted small text-nowrap">{{ $guardia->cerradoPor->nombre ?? '—' }}</td>
                                <td class="text-muted small">{{ $guardia->cerrado_at ? $guardia->cerrado_at->format('H:i') : '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('guardias-nocturnas.show', $guardia) }}"
                                       class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No hay guardias nocturnas registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Cards móvil --}}
                <div class="d-md-none">
                    @forelse($historial as $guardia)
                        <div class="border-bottom px-3 py-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <span class="fw-bold">{{ $guardia->fecha->format('d/m/Y') }}</span>
                                    @if($guardia->estado === 'abierta')
                                        <span class="badge bg-warning text-dark ms-1"><i class="bi bi-unlock me-1"></i>Abierta</span>
                                    @else
                                        <span class="badge bg-success ms-1"><i class="bi bi-lock me-1"></i>Cerrada</span>
                                    @endif
                                </div>
                                <a href="{{ route('guardias-nocturnas.show', $guardia) }}"
                                   class="btn btn-sm btn-outline-primary flex-shrink-0"><i class="bi bi-eye"></i></a>
                            </div>
                            <div class="d-flex flex-wrap gap-2 small text-muted">
                                <span class="badge bg-success">{{ $guardia->companias_count - $guardia->sin_reporte_count }} con rep.</span>
                                @if($guardia->sin_reporte_count > 0)
                                    <span class="badge bg-danger">{{ $guardia->sin_reporte_count }} sin rep.</span>
                                @endif
                                @if($guardia->cerradoPor)
                                    <span><i class="bi bi-person me-1"></i>{{ $guardia->cerradoPor->nombre }}</span>
                                @endif
                                @if($guardia->cerrado_at)
                                    <span><i class="bi bi-clock me-1"></i>{{ $guardia->cerrado_at->format('H:i') }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">No hay guardias nocturnas registradas.</div>
                    @endforelse
                </div>

            </div>
        </div>
        <div class="mt-3">{{ $historial->links() }}</div>
    </div>{{-- fin tab historial --}}

</div>{{-- fin tab-content --}}

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const meses = @json(array_values($meses));
const datos  = @json($evolucionMensual->values());

new Chart(document.getElementById('graficoEvolucion'), {
    type: 'line',
    data: {
        labels: meses,
        datasets: [{
            label: 'Promedio voluntarios en guardia',
            data: datos.map(d => d.promedio_vol),
            borderColor: 'rgba(13, 110, 253, 1)',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            borderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
            fill: true,
            tension: 0.3,
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Promedio voluntarios' } }
        },
        plugins: {
            legend: { position: 'top' },
            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} voluntarios promedio` } }
        }
    }
});

const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('tab') === 'historial') {
    const tabHistorial = document.querySelector('[data-bs-target="#tabHistorial"]');
    bootstrap.Tab.getOrCreateInstance(tabHistorial).show();
}
</script>
@endpush

@endsection