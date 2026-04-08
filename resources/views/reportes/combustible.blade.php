@extends('layouts.app')
@section('title', 'Reporte de Combustible')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-fuel-pump me-2"></i>Reporte de Combustible</h4>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-funnel me-2"></i>Filtros
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reportes.combustible') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Compañía</label>
                <select name="compania_id" class="form-select">
                    <option value="">Todas las compañías</option>
                    @foreach($companias as $compania)
                        <option value="{{ $compania->id }}"
                                {{ $companiaId == $compania->id ? 'selected' : '' }}>
                            {{ $compania->numero }} - {{ $compania->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Año <span class="text-danger">*</span></label>
                <select name="anio" class="form-select" required>
                    @foreach($anios as $a)
                        <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Mes <span class="text-muted small">(opcional)</span></label>
                <select name="mes" class="form-select">
                    <option value="">Año completo</option>
                    @foreach($meses as $num => $nombre)
                        <option value="{{ $num }}" {{ $mes == $num ? 'selected' : '' }}>
                            {{ $nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-danger w-100">
                    <i class="bi bi-search me-1"></i>Generar Reporte
                </button>
            </div>
        </form>
    </div>
</div>

@if($vouchers->isNotEmpty())

{{-- ── RESUMEN EJECUTIVO ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">
                    <i class="bi bi-currency-dollar me-1"></i>Gasto Total
                </div>
                <div class="fw-bold fs-4 text-danger">
                    ${{ number_format($totalGasto, 0, ',', '.') }}
                </div>
                <div class="text-muted small">
                    {{ $mes ? $meses[$mes] . ' ' . $anio : 'Año ' . $anio }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">
                    <i class="bi bi-droplet me-1"></i>Total Litros
                </div>
                <div class="fw-bold fs-4 text-primary">
                    {{ number_format($totalLitros, 1, ',', '.') }} L
                </div>
                <div class="text-muted small">{{ $totalVouchers }} voucher(s)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">
                    <i class="bi bi-tag me-1"></i>Precio Promedio
                </div>
                <div class="fw-bold fs-4 text-warning">
                    ${{ number_format($precioPromedio, 0, ',', '.') }}/L
                </div>
                <div class="text-muted small">Promedio ponderado</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-muted small mb-1">
                    <i class="bi bi-truck-front me-1"></i>Unidades Abastecidas
                </div>
                <div class="fw-bold fs-4 text-success">
                    {{ $vouchers->pluck('unidad_id')->unique()->count() }}
                </div>
                <div class="text-muted small">unidades distintas</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">

    {{-- Gasto por compañía --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-building me-2"></i>Gasto por Compañía
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
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
                            <td class="text-end">{{ number_format($datos['litros'], 1, ',', '.') }} L</td>
                            <td class="text-end fw-bold text-success">
                                ${{ number_format($datos['total'], 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                @php $pct = $totalGasto > 0 ? round($datos['total'] / $totalGasto * 100, 1) : 0 @endphp
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <div class="progress flex-fill" style="height:6px; min-width:50px">
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
                            <td class="text-end fw-bold">{{ number_format($totalLitros, 1, ',', '.') }} L</td>
                            <td class="text-end fw-bold text-danger">
                                ${{ number_format($totalGasto, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Evolución mensual --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-graph-up me-2"></i>Evolución Mensual — {{ $anio }}
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mes</th>
                            <th class="text-end">Litros</th>
                            <th class="text-end">Gasto</th>
                            <th class="text-end">$/L prom.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($meses as $num => $nombre)
                        @if(isset($gastoPorMes[$num]))
                        @php $dm = $gastoPorMes[$num]; @endphp
                        <tr {{ $mes == $num ? 'class=table-warning' : '' }}>
                            <td>{{ $nombre }}</td>
                            <td class="text-end">{{ number_format($dm['litros'], 1, ',', '.') }} L</td>
                            <td class="text-end fw-bold text-success">
                                ${{ number_format($dm['total'], 0, ',', '.') }}
                            </td>
                            <td class="text-end text-muted small">
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

{{-- Ranking unidades --}}
<div class="card mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-trophy me-2"></i>Ranking — Unidades con Mayor Gasto
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Unidad</th>
                    <th>Compañía</th>
                    <th class="text-end">N° Cargas</th>
                    <th class="text-end">Litros</th>
                    <th class="text-end">Gasto Total</th>
                    <th>Participación</th>
                </tr>
            </thead>
            <tbody>
                @php $posicion = 0; @endphp
                @foreach($rankingUnidades as $datos)
                <tr>
                    <td>
                        @if($posicion === 0)
                            <span class="badge bg-warning text-dark">🥇 1°</span>
                        @elseif($posicion === 1)
                            <span class="badge bg-secondary">🥈 2°</span>
                        @elseif($posicion === 2)
                            <span class="badge bg-danger">🥉 3°</span>
                        @else
                            <span class="text-muted">{{ $posicion + 1 }}°</span>
                        @endif
                    </td>
                    <td class="fw-bold">{{ $datos['unidad'] }}</td>
                    <td>{{ $datos['compania'] }}</td>
                    <td class="text-end">{{ $datos['count'] }}</td>
                    <td class="text-end">{{ number_format($datos['litros'], 1, ',', '.') }} L</td>
                    <td class="text-end fw-bold text-success">
                        ${{ number_format($datos['total'], 0, ',', '.') }}
                    </td>
                    <td style="min-width:120px">
                        @php $pct = $totalGasto > 0 ? round($datos['total'] / $totalGasto * 100, 1) : 0 @endphp
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-fill" style="height:8px">
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
</div>

{{-- Detalle completo --}}
<div class="card">
    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-ul me-2"></i>Detalle de Vouchers</span>
        <a href="{{ route('vouchers-combustible.exportar', request()->query()) }}"
           class="btn btn-success btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i>Exportar Excel
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
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
                    <td>{{ $v->fecha_carga->format('d/m/Y') }}</td>
                    <td><span class="badge bg-secondary">{{ $v->numero_voucher }}</span></td>
                    <td class="fw-bold">{{ $v->unidad->nombre }}</td>
                    <td>{{ $v->unidad->compania->nombre }}</td>
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
                    <td class="text-end fw-bold text-muted">
                        ${{ number_format($precioPromedio, 0, ',', '.') }}/L prom.
                    </td>
                    <td class="text-end fw-bold text-danger">
                        ${{ number_format($totalGasto, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@else
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Selecciona un año y opcionalmente una compañía o mes para generar el reporte.
</div>
@endif

@endsection