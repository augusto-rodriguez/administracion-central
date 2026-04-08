@extends('layouts.app')

@section('title', 'Libro de Novedades — ' . $libro->turno_label . ' ' . $libro->fecha->format('d/m/Y'))

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-journal-check me-2"></i>
            Libro de Novedades —
            @if($libro->turno === 'dia')
                <span class="badge bg-warning text-dark fs-6"><i class="bi bi-sun me-1"></i>Turno Día</span>
            @else
                <span class="badge bg-dark fs-6"><i class="bi bi-moon-stars me-1"></i>Turno Noche</span>
            @endif
        </h4>
        <small class="text-muted">{{ $libro->fecha->format('d/m/Y') }} — Cerrado por {{ $libro->cerradoPor->nombre ?? '—' }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('libro-novedades.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-printer me-1"></i>Imprimir
        </button>
    </div>
</div>

{{-- ── BLOQUE 1: IDENTIFICACIÓN DEL TURNO ────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header bg-light fw-bold"><i class="bi bi-info-circle me-2"></i>Identificación del turno</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-sm-3"><span class="text-muted d-block small">Fecha</span><strong>{{ $libro->fecha->format('d/m/Y') }}</strong></div>
            <div class="col-sm-3"><span class="text-muted d-block small">Turno</span><strong>{{ $libro->turno_label }}</strong></div>
            <div class="col-sm-3"><span class="text-muted d-block small">Horario</span><strong>{{ $libro->horario }}</strong></div>
            <div class="col-sm-3"><span class="text-muted d-block small">Operador en turno</span><strong>{{ $libro->operador->nombre ?? '—' }}</strong></div>
            <div class="col-sm-6"><span class="text-muted d-block small">Operador turno anterior</span><strong>{{ $libro->operador_turno_anterior ?? '—' }}</strong></div>
        </div>
    </div>
</div>

{{-- ── BLOQUE 2: AL RECIBIR EL TURNO ──────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header bg-light fw-bold"><i class="bi bi-box-arrow-in-right me-2"></i>Al recibir el turno</div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <h6 class="fw-bold">Maquinistas en servicio</h6>
                @php $m = $libro->maquinistas_al_recibir ?? []; @endphp
                @if(count($m))
                    <ul class="list-group list-group-flush">
                        @foreach($m as $item)
                        <li class="list-group-item px-0 py-1">
                            {{ $item['nombre'] }}
                            @if(!empty($item['unidades'])) — {{ collect($item['unidades'])->pluck('nombre')->implode(', ') }} @endif
                        </li>
                        @endforeach
                    </ul>
                @else <p class="text-muted small">—</p> @endif
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold">Cuarteleros en servicio</h6>
                @php $c = $libro->cuarteleros_al_recibir ?? []; @endphp
                @if(count($c))
                    <ul class="list-group list-group-flush">
                        @foreach($c as $item)
                        <li class="list-group-item px-0 py-1">
                            {{ $item['nombre'] }}
                            @if(!empty($item['unidades'])) — {{ collect($item['unidades'])->pluck('nombre')->implode(', ') }} @endif
                        </li>
                        @endforeach
                    </ul>
                @else <p class="text-muted small">—</p> @endif
            </div>
            <div class="col-12">
                <h6 class="fw-bold text-danger">Unidades fuera de servicio al recibir</h6>
                @php $fs = $libro->unidades_fuera_servicio_al_recibir ?? []; @endphp
                @if(count($fs))
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($fs as $u)
                            <span class="badge bg-danger">{{ $u['nombre'] }}@if($u['patente']) ({{ $u['patente'] }})@endif</span>
                        @endforeach
                    </div>
                @else <p class="text-muted small">Ninguna unidad fuera de servicio.</p> @endif
            </div>
        </div>
    </div>
</div>

{{-- ── BLOQUE 3: DURANTE EL TURNO ─────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header bg-light fw-bold"><i class="bi bi-activity me-2"></i>Durante el turno</div>
    <div class="card-body">
        <div class="row g-4">

            {{-- Salidas de emergencia --}}
            <div class="col-12">
                <h6 class="fw-bold text-danger"><i class="bi bi-exclamation-octagon me-1"></i>Salidas de emergencia</h6>
                @if($salidasEmergencia->count())
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light"><tr><th>Unidad</th><th>Clave</th><th>Dirección</th><th>Salida</th><th>Llegada</th></tr></thead>
                        <tbody>
                            @foreach($salidasEmergencia as $s)
                            <tr>
                                <td>{{ $s->unidad->nombre ?? '—' }}</td>
                                <td><span class="badge bg-danger">{{ $s->claveSalida->codigo ?? '—' }}</span></td>
                                <td>{{ $s->direccion }}</td>
                                <td>{{ \Carbon\Carbon::parse($s->salida_at)->format('H:i') }}</td>
                                <td>{{ $s->llegada_at ? \Carbon\Carbon::parse($s->llegada_at)->format('H:i') : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else <p class="text-muted small">Sin salidas de emergencia en el turno.</p> @endif
            </div>

            {{-- Salidas administrativas --}}
            <div class="col-12">
                <h6 class="fw-bold"><i class="bi bi-clipboard me-1"></i>Salidas administrativas</h6>
                @if($salidasAdmin->count())
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light"><tr><th>Unidad</th><th>Clave</th><th>Dirección</th><th>Autoriza</th><th>Salida</th><th>Llegada</th></tr></thead>
                        <tbody>
                            @foreach($salidasAdmin as $s)
                            <tr>
                                <td>{{ $s->unidad->nombre ?? '—' }}</td>
                                <td><span class="badge bg-secondary">{{ $s->claveSalida->codigo ?? '—' }}</span></td>
                                <td>{{ $s->direccion }}</td>
                                <td>{{ $s->oficial->nombre ?? '—' }}</td>
                                <td>{{ \Carbon\Carbon::parse($s->salida_at)->format('H:i') }}</td>
                                <td>{{ $s->llegada_at ? \Carbon\Carbon::parse($s->llegada_at)->format('H:i') : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else <p class="text-muted small">Sin salidas administrativas en el turno.</p> @endif
            </div>

            {{-- Puestas en servicio --}}
            <div class="col-12">
                <h6 class="fw-bold text-success">
                    <i class="bi bi-person-check me-1"></i>Puestas en servicio durante el turno
                </h6>
                @php $puestas = $libro->puestas_en_servicio ?? []; @endphp
                @if(count($puestas))
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo</th>
                                <th>Nombre</th>
                                <th>Unidad(es)</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Tiempo en servicio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($puestas as $p)
                            <tr>
                                <td>
                                    @if($p['tipo'] === 'maquinista')
                                        <span class="badge bg-primary">Maquinista</span>
                                    @else
                                        <span class="badge bg-secondary">Cuartelero</span>
                                    @endif
                                </td>
                                <td class="fw-bold">{{ $p['nombre'] }}</td>
                                <td>{{ $p['unidades'] ?: '—' }}</td>
                                <td>{{ \Carbon\Carbon::parse($p['entrada_at'])->format('H:i') }}</td>
                                <td>{{ $p['salida_at'] ? \Carbon\Carbon::parse($p['salida_at'])->format('H:i') : 'En servicio' }}</td>
                                <td>
                                    @if($p['total_minutos'])
                                        @php
                                            $h = intdiv($p['total_minutos'], 60);
                                            $m = $p['total_minutos'] % 60;
                                        @endphp
                                        {{ $h > 0 ? $h . 'h ' : '' }}{{ $m }}min
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted small">Sin puestas en servicio registradas en el turno.</p>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- ── BLOQUE 4: AL ENTREGAR EL TURNO ─────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header bg-light fw-bold"><i class="bi bi-box-arrow-right me-2"></i>Al entregar el turno</div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <h6 class="fw-bold">Maquinistas en servicio</h6>
                @php $me = $libro->maquinistas_al_entregar ?? []; @endphp
                @if(count($me))
                    <ul class="list-group list-group-flush">
                        @foreach($me as $item)
                        <li class="list-group-item px-0 py-1">
                            {{ $item['nombre'] }}
                            @if(!empty($item['unidades'])) — {{ collect($item['unidades'])->pluck('nombre')->implode(', ') }} @endif
                        </li>
                        @endforeach
                    </ul>
                @else <p class="text-muted small">—</p> @endif
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold">Cuarteleros en servicio</h6>
                @php $ce = $libro->cuarteleros_al_entregar ?? []; @endphp
                @if(count($ce))
                    <ul class="list-group list-group-flush">
                        @foreach($ce as $item)
                        <li class="list-group-item px-0 py-1">
                            {{ $item['nombre'] }}
                            @if(!empty($item['unidades'])) — {{ collect($item['unidades'])->pluck('nombre')->implode(', ') }} @endif
                        </li>
                        @endforeach
                    </ul>
                @else <p class="text-muted small">—</p> @endif
            </div>
            <div class="col-12">
                <h6 class="fw-bold text-danger">Unidades fuera de servicio al entregar</h6>
                @php $fse = $libro->unidades_fuera_servicio_al_entregar ?? []; @endphp
                @if(count($fse))
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($fse as $u)
                            <span class="badge bg-danger">{{ $u['nombre'] }}@if($u['patente']) ({{ $u['patente'] }})@endif</span>
                        @endforeach
                    </div>
                @else <p class="text-muted small">Ninguna unidad fuera de servicio.</p> @endif
            </div>
        </div>
    </div>
</div>

{{-- ── BLOQUE 5: NOVEDADES DEL TURNO ──────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header bg-light fw-bold"><i class="bi bi-pencil-square me-2"></i>Novedades del turno</div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-12">
                <h6 class="fw-bold">Cronológico de novedades</h6>
                <p class="mb-0" style="white-space: pre-wrap">{{ $libro->novedades_cronologicas ?? '—' }}</p>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold">Observaciones en telecomunicaciones</h6>
                <p class="mb-0" style="white-space: pre-wrap">{{ $libro->observaciones_telecomunicaciones ?? '—' }}</p>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold">Novedades del VIPER</h6>
                <p class="mb-0" style="white-space: pre-wrap">{{ $libro->novedades_viper ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>

@endsection