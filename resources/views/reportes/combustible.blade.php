@extends('layouts.app')
@section('title', 'Reporte de Combustible')
@section('content')

<div class="mb-4">
    <h4 class="mb-0"><i class="bi bi-fuel-pump me-2"></i>Reporte de Combustible</h4>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-funnel me-2"></i>Filtros
    </div>
    <div class="card-body py-3">
        <form method="GET" action="{{ route('reportes.combustible') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label fw-bold mb-1 small">Compañía</label>
                <select name="compania_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($companias as $compania)
                        <option value="{{ $compania->id }}" {{ $companiaId == $compania->id ? 'selected' : '' }}>
                            {{ $compania->numero }} - {{ $compania->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label fw-bold mb-1 small">Año <span class="text-danger">*</span></label>
                <select name="anio" class="form-select form-select-sm" required>
                    @foreach($anios as $a)
                        <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label fw-bold mb-1 small">Mes</label>
                <select name="mes" class="form-select form-select-sm">
                    <option value="">Año completo</option>
                    @foreach($meses as $num => $nombre)
                        <option value="{{ $num }}" {{ $mes == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-danger btn-sm w-100">
                    <i class="bi bi-search me-1"></i>Generar Reporte
                </button>
            </div>
        </form>
    </div>
</div>

@if($vouchers->isNotEmpty())

{{-- Resumen ejecutivo --}}
<div class="row g-2 g-md-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center p-2 p-md-3">
                <div class="text-muted small mb-1"><i class="bi bi-currency-dollar me-1"></i>Gasto Total</div>
                <div class="fw-bold fs-5 fs-md-4 text-danger">${{ number_format($totalGasto, 0, ',', '.') }}</div>
                <div class="text-muted" style="font-size:0.7rem">{{ $mes ? $meses[$mes] . ' ' . $anio : 'Año ' . $anio }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center p-2 p-md-3">
                <div class="text-muted small mb-1"><i class="bi bi-droplet me-1"></i>Total Litros</div>
                <div class="fw-bold fs-5 fs-md-4 text-primary">{{ number_format($totalLitros, 1, ',', '.') }} L</div>
                <div class="text-muted" style="font-size:0.7rem">{{ $totalVouchers }} voucher(s)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center p-2 p-md-3">
                <div class="text-muted small mb-1"><i class="bi bi-tag me-1"></i>Precio Prom.</div>
                <div class="fw-bold fs-5 fs-md-4 text-warning">${{ number_format($precioPromedio, 0, ',', '.') }}/L</div>
                <div class="text-muted" style="font-size:0.7rem">Prom. ponderado</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center p-2 p-md-3">
                <div class="text-muted small mb-1"><i class="bi bi-truck-front me-1"></i>Unidades</div>
                <div class="fw-bold fs-5 fs-md-4 text-success">{{ $vouchers->pluck('unidad_id')->unique()->count() }}</div>
                <div class="text-muted" style="font-size:0.7rem">abastecidas</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 g-md-4 mb-4">

    {{-- Gasto por compañía --}}
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-building me-2"></i>Gasto por Compañía
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0" style="font-size:0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Compañía</th>
                                <th class="text-end">Litros</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gastoPorCompania as $nombre => $datos)
                            <tr>
                                <td class="fw-bold">{{ $nombre }}</td>
                                <td class="text-end text-nowrap">{{ number_format($datos['litros'], 1, ',', '.') }} L</td>
                                <td class="text-end fw-bold text-success text-nowrap">${{ number_format($datos['total'], 0, ',', '.') }}</td>
                                <td class="text-end">
                                    @php $pct = $totalGasto > 0 ? round($datos['total'] / $totalGasto * 100, 1) : 0 @endphp
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <div class="progress flex-fill d-none d-md-flex" style="height:6px; min-width:40px">
                                            <div class="progress-bar bg-danger" style="width:{{ $pct }}%"></div>
                                        </div>
                                        <span class="small">{{ $pct }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold">Total</td>
                                <td class="text-end fw-bold text-nowrap">{{ number_format($totalLitros, 1, ',', '.') }} L</td>
                                <td class="text-end fw-bold text-danger text-nowrap">${{ number_format($totalGasto, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Evolución mensual --}}
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-graph-up me-2"></i>Evolución — {{ $anio }}
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0" style="font-size:0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Mes</th>
                                <th class="text-end">Litros</th>
                                <th class="text-end">Gasto</th>
                                <th class="text-end">$/L</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($meses as $num => $nombre)
                            @if(isset($gastoPorMes[$num]))
                            @php $dm = $gastoPorMes[$num]; @endphp
                            <tr {{ $mes == $num ? 'class=table-warning' : '' }}>
                                <td>{{ $nombre }}</td>
                                <td class="text-end text-nowrap">{{ number_format($dm['litros'], 1, ',', '.') }} L</td>
                                <td class="text-end fw-bold text-success text-nowrap">${{ number_format($dm['total'], 0, ',', '.') }}</td>
                                <td class="text-end text-muted small text-nowrap">
                                    ${{ $dm['litros'] > 0 ? number_format(round($dm['total'] / $dm['litros']), 0, ',', '.') : '—' }}/L
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Ranking unidades --}}
<div class="card mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-trophy me-2"></i>Ranking — Mayor Gasto
    </div>
    <div class="card-body p-0">

        {{-- Tabla desktop --}}
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 d-none d-md-table" style="font-size:0.85rem;">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Unidad</th>
                        <th>Compañía</th>
                        <th class="text-end">Cargas</th>
                        <th class="text-end">Litros</th>
                        <th class="text-end">Gasto</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    @php $posicion = 0; @endphp
                    @foreach($rankingUnidades as $datos)
                    <tr>
                        <td>
                            @if($posicion === 0) <span class="badge bg-warning text-dark">🥇</span>
                            @elseif($posicion === 1) <span class="badge bg-secondary">🥈</span>
                            @elseif($posicion === 2) <span class="badge bg-danger">🥉</span>
                            @else <span class="text-muted">{{ $posicion + 1 }}°</span>
                            @endif
                        </td>
                        <td class="fw-bold text-nowrap">{{ $datos['unidad'] }}</td>
                        <td class="text-nowrap">{{ $datos['compania'] }}</td>
                        <td class="text-end">{{ $datos['count'] }}</td>
                        <td class="text-end text-nowrap">{{ number_format($datos['litros'], 1, ',', '.') }} L</td>
                        <td class="text-end fw-bold text-success text-nowrap">${{ number_format($datos['total'], 0, ',', '.') }}</td>
                        <td style="min-width:100px">
                            @php $pct = $totalGasto > 0 ? round($datos['total'] / $totalGasto * 100, 1) : 0 @endphp
                            <div class="d-flex align-items-center gap-1">
                                <div class="progress flex-fill" style="height:6px">
                                    <div class="progress-bar bg-danger" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="small fw-bold">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @php $posicion++; @endphp
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Cards móvil --}}
        <div class="d-md-none">
            @php $posicion = 0; @endphp
            @foreach($rankingUnidades as $datos)
                <div class="border-bottom px-3 py-2">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="me-1">
                                @if($posicion === 0) 🥇
                                @elseif($posicion === 1) 🥈
                                @elseif($posicion === 2) 🥉
                                @else <span class="text-muted">{{ $posicion + 1 }}°</span>
                                @endif
                            </span>
                            <span class="fw-bold">{{ $datos['unidad'] }}</span>
                            <span class="text-muted small ms-1">{{ $datos['compania'] }}</span>
                        </div>
                        <span class="fw-bold text-success flex-shrink-0">${{ number_format($datos['total'], 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 small text-muted">
                        <span>{{ $datos['count'] }} cargas</span>
                        <span>{{ number_format($datos['litros'], 1, ',', '.') }} L</span>
                        @php $pct = $totalGasto > 0 ? round($datos['total'] / $totalGasto * 100, 1) : 0 @endphp
                        <span class="fw-bold">{{ $pct }}%</span>
                    </div>
                </div>
            @php $posicion++; @endphp
            @endforeach
        </div>

    </div>
</div>

{{-- Detalle vouchers --}}
<div class="card">
    <div class="card-header bg-white fw-bold d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <span><i class="bi bi-list-ul me-2"></i>Detalle de Vouchers</span>
        <a href="{{ route('vouchers-combustible.exportar', request()->query()) }}"
           class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i>Exportar
        </a>
    </div>
    <div class="card-body p-0">

        {{-- Tabla desktop --}}
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 d-none d-md-table" style="font-size:0.85rem;">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>N° Voucher</th>
                        <th>Unidad</th>
                        <th>Compañía</th>
                        <th>Conductor</th>
                        <th class="text-end">Litros</th>
                        <th class="text-end">$/L</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vouchers as $v)
                    <tr>
                        <td class="text-nowrap">{{ $v->fecha_carga->format('d/m/Y') }}</td>
                        <td><span class="badge bg-secondary">{{ $v->numero_voucher }}</span></td>
                        <td class="fw-bold text-nowrap">{{ $v->unidad->nombre }}</td>
                        <td class="text-nowrap">{{ $v->unidad->compania->nombre }}</td>
                        <td>{{ $v->conductor_nombre }}</td>
                        <td class="text-end">{{ $v->litros_formateados }}</td>
                        <td class="text-end">{{ $v->valor_unitario_formateado }}</td>
                        <td class="text-end fw-bold text-success">{{ $v->total_formateado }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="5" class="fw-bold text-end">Totales:</td>
                        <td class="text-end fw-bold">{{ number_format($totalLitros, 1, ',', '.') }} L</td>
                        <td class="text-end fw-bold text-muted">${{ number_format($precioPromedio, 0, ',', '.') }}/L</td>
                        <td class="text-end fw-bold text-danger">${{ number_format($totalGasto, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Cards móvil --}}
        <div class="d-md-none">
            @foreach($vouchers as $v)
                <div class="border-bottom px-3 py-2">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="fw-bold">{{ $v->unidad->nombre }}</span>
                            <span class="text-muted small ms-1">{{ $v->unidad->compania->nombre }}</span>
                            <span class="badge bg-secondary ms-1">{{ $v->numero_voucher }}</span>
                        </div>
                        <span class="fw-bold text-success flex-shrink-0">{{ $v->total_formateado }}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 small text-muted">
                        <span><i class="bi bi-calendar me-1"></i>{{ $v->fecha_carga->format('d/m/Y') }}</span>
                        <span>{{ $v->litros_formateados }} × {{ $v->valor_unitario_formateado }}</span>
                        <span><i class="bi bi-person me-1"></i>{{ $v->conductor_nombre }}</span>
                    </div>
                </div>
            @endforeach

            <div class="px-3 py-2 bg-light fw-bold small d-flex justify-content-between">
                <span>Total: {{ number_format($totalLitros, 1, ',', '.') }} L</span>
                <span class="text-danger">${{ number_format($totalGasto, 0, ',', '.') }}</span>
            </div>
        </div>

    </div>
</div>

@else
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Selecciona un año y opcionalmente una compañía o mes para generar el reporte.
</div>
@endif

@endsection