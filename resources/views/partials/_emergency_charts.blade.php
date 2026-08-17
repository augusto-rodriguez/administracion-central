{{-- ═══════════════════════════════════════════════════════════════
     INDICADORES GRÁFICOS DE EMERGENCIAS
     (Para Admin, Comandante y Capitán de Compañía)
═══════════════════════════════════════════════════════════════ --}}
<div class="mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-graph-up text-danger me-2"></i>Indicadores de Emergencias
        </h5>
        <div class="d-flex align-items-center gap-2">
            <label for="mesEmergencias" class="text-muted small mb-0 text-nowrap">
                <i class="bi bi-calendar3 me-1"></i>Período:
            </label>
            <select id="mesEmergencias" class="form-select form-select-sm" style="width: auto; min-width: 170px;">
                @foreach($chartData['mesesDisponibles'] as $mes)
                    <option value="{{ $mes['valor'] }}"
                        {{ $mes['valor'] === $chartData['mesSeleccionado'] ? 'selected' : '' }}>
                        {{ $mes['nombre'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Fila 1: Indicador mensual + Evolución semanal --}}
    <div class="row g-3 g-md-4">

        {{-- Porcentaje mensual --}}
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold small py-2">
                    <i class="bi bi-percent text-danger me-1"></i>Emergencias del mes
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <div class="position-relative d-inline-flex align-items-center justify-content-center"
                         style="width: 160px; height: 160px;">
                        <canvas id="chartMensual"></canvas>
                        <div class="position-absolute text-center">
                            <div class="fs-3 fw-bold text-danger">{{ $chartData['mesActual']['porcentaje'] }}%</div>
                            <div class="text-muted" style="font-size: 0.7rem;">emergencias</div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">
                            {{ $chartData['mesActual']['emergencias'] }} emergencias
                        </span>
                        <span class="text-muted small ms-2">
                            de {{ $chartData['mesActual']['total'] }} salidas
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Evolución semanal --}}
        <div class="col-12 col-md-8">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold small py-2">
                    <i class="bi bi-bar-chart text-primary me-1"></i>Emergencias por semana — {{ $chartData['mesActual']['nombreMes'] }}
                </div>
                <div class="card-body">
                    <canvas id="chartSemanal" style="max-height: 260px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Fila 2: Distribución por clave --}}
    <div class="row g-3 g-md-4 mt-1">
        <div class="col-12 col-md-5">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold small py-2 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-pie-chart text-warning me-1"></i>Distribución por clave</span>
                    <span class="badge bg-light text-muted fw-normal">{{ $chartData['mesActual']['nombreMes'] }}</span>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    @if(count($chartData['claves']) > 0)
                        <canvas id="chartClaves" style="max-height: 280px;"></canvas>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-pie-chart fs-3"></i>
                            <p class="mt-2 mb-0">Sin emergencias registradas este mes</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-md-7">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold small py-2 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-ol text-secondary me-1"></i>Detalle por clave de emergencia</span>
                    <span class="badge bg-light text-muted fw-normal">{{ $chartData['mesActual']['nombreMes'] }}</span>
                </div>
                <div class="card-body p-0">
                    @if(count($chartData['claves']) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Clave</th>
                                        <th>Descripción</th>
                                        <th class="text-center">Cant.</th>
                                        <th class="text-end pe-3" style="min-width:140px;">Porcentaje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($chartData['claves'] as $clave)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge bg-danger bg-opacity-75">{{ $clave['codigo'] }}</span>
                                        </td>
                                        <td class="small">{{ $clave['descripcion'] }}</td>
                                        <td class="text-center fw-bold">{{ $clave['total'] }}</td>
                                        <td class="pe-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar bg-danger"
                                                         style="width: {{ $clave['porcentaje'] }}%"></div>
                                                </div>
                                                <span class="text-muted small" style="min-width:40px;text-align:right;">
                                                    {{ $clave['porcentaje'] }}%
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-3"></i>
                            <p class="mt-2 mb-0">Sin datos para mostrar</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const chartData = @json($chartData);

    // ── Colores ──
    const colores = [
        '#dc3545', '#fd7e14', '#ffc107', '#198754', '#0d6efd',
        '#6610f2', '#d63384', '#20c997', '#0dcaf0', '#6c757d',
        '#e74c3c', '#3498db', '#2ecc71', '#9b59b6', '#f39c12',
        '#1abc9c', '#e67e22', '#e91e63', '#00bcd4', '#8bc34a'
    ];

    // ── 1) Gráfico de dona mensual ──
    const ctxMensual = document.getElementById('chartMensual');
    if (ctxMensual) {
        new Chart(ctxMensual, {
            type: 'doughnut',
            data: {
                labels: ['Emergencias', 'Otras salidas'],
                datasets: [{
                    data: [
                        chartData.mesActual.emergencias,
                        Math.max(0, chartData.mesActual.total - chartData.mesActual.emergencias)
                    ],
                    backgroundColor: ['#dc3545', '#e9ecef'],
                    borderWidth: 0,
                    cutout: '78%',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.label}: ${ctx.raw}`
                        }
                    }
                },
            }
        });
    }

    // ── 2) Gráfico de barras semanal ──
    const ctxSemanal = document.getElementById('chartSemanal');
    if (ctxSemanal) {
        new Chart(ctxSemanal, {
            type: 'bar',
            data: {
                labels: chartData.semanas.map(s => 'Sem. ' + s.label),
                datasets: [
                    {
                        label: 'Emergencias',
                        data: chartData.semanas.map(s => s.emergencias),
                        backgroundColor: '#dc354599',
                        borderColor: '#dc3545',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Otras salidas',
                        data: chartData.semanas.map(s => Math.max(0, s.total - s.emergencias)),
                        backgroundColor: '#0d6efd33',
                        borderColor: '#0d6efd',
                        borderWidth: 1,
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 11 },
                            precision: 0
                        },
                        grid: { color: '#f0f0f0' }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 11 }, usePointStyle: true, pointStyle: 'rectRounded' }
                    },
                    tooltip: {
                        callbacks: {
                            afterBody: function (ctx) {
                                const idx = ctx[0].dataIndex;
                                const sem = chartData.semanas[idx];
                                return `Total: ${sem.total} · ${sem.porcentaje}% emergencias`;
                            }
                        }
                    }
                }
            }
        });
    }

    // ── 3) Gráfico de torta por clave ──
    const ctxClaves = document.getElementById('chartClaves');
    if (ctxClaves && chartData.claves.length > 0) {
        new Chart(ctxClaves, {
            type: 'pie',
            data: {
                labels: chartData.claves.map(c => c.codigo + ' — ' + c.descripcion),
                datasets: [{
                    data: chartData.claves.map(c => c.total),
                    backgroundColor: colores.slice(0, chartData.claves.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 10 },
                            padding: 8,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            generateLabels: function (chart) {
                                const data = chart.data;
                                return data.labels.map((label, i) => ({
                                    text: label.split(' — ')[0],
                                    fillStyle: data.datasets[0].backgroundColor[i],
                                    strokeStyle: '#fff',
                                    lineWidth: 1,
                                    hidden: false,
                                    index: i,
                                    pointStyle: 'circle',
                                }));
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const clave = chartData.claves[ctx.dataIndex];
                                return ` ${clave.codigo}: ${clave.total} (${clave.porcentaje}%)`;
                            },
                            afterLabel: function (ctx) {
                                return chartData.claves[ctx.dataIndex].descripcion;
                            }
                        }
                    }
                }
            }
        });
    }
})();

// ── Selector de mes ──
document.getElementById('mesEmergencias')?.addEventListener('change', function () {
    const url = new URL(window.location.href);
    url.searchParams.set('mes_emergencias', this.value);
    window.location.href = url.toString();
});
</script>
@endpush