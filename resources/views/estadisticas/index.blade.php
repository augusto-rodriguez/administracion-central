@extends('layouts.app')

@section('title', 'Estadísticas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-trophy me-2"></i>Estadísticas de Maquinistas</h4>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-funnel me-2"></i>Filtros
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('estadisticas.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Año</label>
                    <select name="anio" class="form-select">
                        @foreach($anios as $a)
                            <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Mes <span class="text-muted small">(opcional)</span></label>
                    <select name="mes" class="form-select">
                        <option value="">Todos los meses</option>
                        @foreach($meses as $num => $nombre)
                            <option value="{{ $num }}" {{ $mes == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Compañía <span class="text-muted small">(opcional)</span></label>
                    <select name="compania_id" class="form-select">
                        <option value="">Todas las compañías</option>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}" {{ $companiaId == $compania->id ? 'selected' : '' }}>
                                {{ $compania->numero }} - {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-danger flex-grow-1">
                        <i class="bi bi-search me-1"></i>Buscar
                    </button>
                    @if(request()->hasAny(['mes', 'compania_id']))
                    <a href="{{ route('estadisticas.index') }}" class="btn btn-outline-secondary"
                       title="Limpiar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Periodo activo --}}
<div class="mb-3 text-muted small">
    Mostrando:
    <strong>{{ $mes ? $meses[$mes] : 'Todo el año' }} {{ $anio }}</strong>
    @if($companiaId)
        — <strong>{{ $companias->find($companiaId)->nombre }}</strong>
    @else
        — <strong>Todas las compañías</strong>
    @endif
</div>

{{-- Tarjetas resumen --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">Total horas en servicio</div>
                <div class="fw-bold fs-2 text-danger">{{ $totalHoras }}h</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">Total turnos completados</div>
                <div class="fw-bold fs-2 text-primary">{{ $totalTurnos }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">Maquinistas activos</div>
                <div class="fw-bold fs-2 text-success">{{ $totalVoluntarios }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Ranking global --}}
@if($rankingGlobal->isNotEmpty())
<div class="card mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-trophy-fill text-warning me-2"></i>
        Top 10 Maquinistas
        <span class="text-muted small ms-2">
            {{ $mes ? $meses[$mes] : 'Año' }} {{ $anio }}
        </span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th width="50">#</th>
                    <th>Maquinista</th>
                    <th>Compañía</th>
                    <th>Turnos</th>
                    <th>Total Horas</th>
                    <th>Promedio por turno</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rankingGlobal as $i => $item)
                @php
                    $h = intdiv($item['total_minutos'], 60);
                    $m = $item['total_minutos'] % 60;
                    $promMin = $item['total_turnos'] > 0 ? intdiv($item['total_minutos'], $item['total_turnos']) : 0;
                    $promH = intdiv($promMin, 60);
                    $promM = $promMin % 60;
                @endphp
                <tr>
                    <td>
                        @if($i === 0) <span class="fs-5">🥇</span>
                        @elseif($i === 1) <span class="fs-5">🥈</span>
                        @elseif($i === 2) <span class="fs-5">🥉</span>
                        @else <span class="text-muted fw-bold">{{ $i + 1 }}</span>
                        @endif
                    </td>
                    <td class="fw-bold">{{ $item['nombre'] }}</td>
                    <td>{{ $item['compania'] }}</td>
                    <td><span class="badge bg-primary">{{ $item['total_turnos'] }}</span></td>
                    <td><span class="badge bg-danger fs-6">{{ $h }}h {{ $m }}min</span></td>
                    <td><span class="badge bg-secondary">{{ $promH }}h {{ $promM }}min</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Ranking por compañía --}}
{{-- Ranking por compañía (solo si no se filtró por una compañía específica) --}}
@if($rankingPorCompania->isNotEmpty() && !$companiaId)
<h5 class="mb-3 fw-bold"><i class="bi bi-building me-2"></i>Mejores por Compañía</h5>
<div class="row g-4">
    @foreach($rankingPorCompania as $item)
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-building text-danger me-2"></i>{{ $item['compania']->nombre }}
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Maquinista</th>
                            <th>Turnos</th>
                            <th>Total Horas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item['mejores'] as $j => $maq)
                        @php
                            $h = intdiv($maq['total_minutos'], 60);
                            $m = $maq['total_minutos'] % 60;
                        @endphp
                        <tr>
                            <td>
                                @if($j === 0) 🥇
                                @elseif($j === 1) 🥈
                                @elseif($j === 2) 🥉
                                @else <span class="text-muted">{{ $j + 1 }}</span>
                                @endif
                            </td>
                            <td class="fw-bold">{{ $maq['nombre'] }}</td>
                            <td><span class="badge bg-primary">{{ $maq['total_turnos'] }}</span></td>
                            <td><span class="badge bg-danger">{{ $h }}h {{ $m }}min</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@if($rankingGlobal->isEmpty() && $rankingPorCompania->isEmpty())
<div class="alert alert-info mt-3">
    <i class="bi bi-info-circle me-2"></i>No hay datos para mostrar en este período.
</div>
@endif

@endsection