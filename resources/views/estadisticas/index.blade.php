@extends('layouts.app')

@section('title', 'Estadísticas')

@section('content')

@php $esCapitan = auth()->user()->esCapitanCia(); @endphp

<div class="mb-4">
    <h4 class="mb-0">
        <i class="bi bi-trophy me-2"></i>Estadísticas de Maquinistas
        @if($esCapitan)
            <span class="text-muted fs-6 fw-normal ms-2">
                — {{ auth()->user()->voluntario?->compania->nombre }}
            </span>
        @endif
    </h4>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-funnel me-2"></i>Filtros
    </div>
    <div class="card-body py-3">
        <form method="GET" action="{{ route('estadisticas.index') }}">
            @if($esCapitan)
                <input type="hidden" name="compania_id" value="{{ $companiaIdCapitan }}">
            @endif
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
                @if(!$esCapitan)
                <div class="col-8 col-md-4">
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
                @endif
                <div class="col-4 col-md-2">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-danger btn-sm flex-grow-1">
                            <i class="bi bi-search me-1"></i>Buscar
                        </button>
                        @if(!$esCapitan && request()->hasAny(['mes', 'compania_id']))
                            <a href="{{ route('estadisticas.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="mb-3 text-muted small">
    Mostrando:
    <strong>{{ $mes ? $meses[$mes] : 'Todo el año' }} {{ $anio }}</strong>
    @if($companiaId)
        — <strong>{{ $companias->find($companiaId)->nombre }}</strong>
    @elseif(!$esCapitan)
        — <strong>Todas las compañías</strong>
    @endif
</div>

{{-- Tarjetas resumen --}}
<div class="row g-2 g-md-3 mb-4">
    <div class="col-4 col-md-4">
        <div class="card text-center h-100">
            <div class="card-body p-2 p-md-3">
                <div class="text-muted" style="font-size:0.7rem">Horas en servicio</div>
                <div class="fw-bold fs-4 fs-md-2 text-danger">{{ $totalHoras }}h</div>
            </div>
        </div>
    </div>
    <div class="col-4 col-md-4">
        <div class="card text-center h-100">
            <div class="card-body p-2 p-md-3">
                <div class="text-muted" style="font-size:0.7rem">Turnos completados</div>
                <div class="fw-bold fs-4 fs-md-2 text-primary">{{ $totalTurnos }}</div>
            </div>
        </div>
    </div>
    <div class="col-4 col-md-4">
        <div class="card text-center h-100">
            <div class="card-body p-2 p-md-3">
                <div class="text-muted" style="font-size:0.7rem">Maquinistas activos</div>
                <div class="fw-bold fs-4 fs-md-2 text-success">{{ $totalVoluntarios }}</div>
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

        {{-- Tabla desktop --}}
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 d-none d-md-table" style="font-size:0.85rem;">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Maquinista</th>
                        @if(!$esCapitan)<th>Compañía</th>@endif
                        <th>Turnos</th>
                        <th>Total Horas</th>
                        <th>Prom/turno</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rankingGlobal as $i => $item)
                    @php
                        $h      = intdiv($item['total_minutos'], 60);
                        $m      = $item['total_minutos'] % 60;
                        $promMin = $item['total_turnos'] > 0 ? intdiv($item['total_minutos'], $item['total_turnos']) : 0;
                        $promH   = intdiv($promMin, 60);
                        $promM   = $promMin % 60;
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
                        @if(!$esCapitan)<td>{{ $item['compania'] }}</td>@endif
                        <td><span class="badge bg-primary">{{ $item['total_turnos'] }}</span></td>
                        <td><span class="badge bg-danger">{{ $h }}h {{ $m }}min</span></td>
                        <td><span class="badge bg-secondary">{{ $promH }}h {{ $promM }}min</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Cards móvil --}}
        <div class="d-md-none">
            @foreach($rankingGlobal as $i => $item)
            @php
                $h      = intdiv($item['total_minutos'], 60);
                $m      = $item['total_minutos'] % 60;
                $promMin = $item['total_turnos'] > 0 ? intdiv($item['total_minutos'], $item['total_turnos']) : 0;
                $promH   = intdiv($promMin, 60);
                $promM   = $promMin % 60;
            @endphp
                <div class="border-bottom px-3 py-2">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="me-1">
                                @if($i === 0) 🥇
                                @elseif($i === 1) 🥈
                                @elseif($i === 2) 🥉
                                @else <span class="text-muted">{{ $i + 1 }}.</span>
                                @endif
                            </span>
                            <span class="fw-bold">{{ $item['nombre'] }}</span>
                            @if(!$esCapitan)
                                <div class="text-muted small ms-4">{{ $item['compania'] }}</div>
                            @endif
                        </div>
                        <span class="badge bg-danger flex-shrink-0">{{ $h }}h {{ $m }}min</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 small text-muted ms-4">
                        <span class="badge bg-primary">{{ $item['total_turnos'] }} turnos</span>
                        <span class="badge bg-secondary">prom: {{ $promH }}h {{ $promM }}min</span>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>
@endif

{{-- Ranking por compañía --}}
@if(!$esCapitan && $rankingPorCompania->isNotEmpty() && !$companiaId)
<h5 class="mb-3 fw-bold"><i class="bi bi-building me-2"></i>Mejores por Compañía</h5>
<div class="row g-3 g-md-4">
    @foreach($rankingPorCompania as $item)
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-building text-danger me-2"></i>{{ $item['compania']->nombre }}
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="font-size:0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Maquinista</th>
                                <th>Turnos</th>
                                <th>Horas</th>
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
    </div>
    @endforeach
</div>
@endif

@if($rankingGlobal->isEmpty())
<div class="alert alert-info mt-3">
    <i class="bi bi-info-circle me-2"></i>No hay datos para mostrar en este período.
</div>
@endif

@endsection