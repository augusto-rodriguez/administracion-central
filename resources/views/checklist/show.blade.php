@extends('layouts.app')

@section('title', 'Inspección #' . $inspeccion->id)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h5 class="mb-1">
            <i class="bi bi-clipboard-check me-2"></i>Inspección — {{ $inspeccion->unidad->nombre }}
        </h5>
        <small class="text-muted">
            {{ $inspeccion->fecha->format('d/m/Y') }} · {{ $inspeccion->cuartelero->nombre ?? '—' }}
            · Completada a las {{ $inspeccion->completado_at?->format('H:i') }}
        </small>
    </div>
    <a href="{{ route('checklist.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

{{-- Estado general --}}
@php
    $totalHallazgos = $inspeccion->hallazgos->count();
    $criticos = $inspeccion->hallazgos->where('severidad', 'critico')->count();
@endphp

@if($totalHallazgos > 0)
    <div class="alert alert-{{ $criticos > 0 ? 'danger' : 'warning' }} d-flex align-items-center mb-3">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>
            Se detectaron <strong>{{ $totalHallazgos }} hallazgo(s)</strong>
            @if($criticos > 0)
                (<strong>{{ $criticos }} crítico(s)</strong>)
            @endif
            en esta inspección.
            <a href="{{ route('hallazgos.index', ['inspeccion_id' => $inspeccion->id]) }}" class="alert-link">Ver hallazgos →</a>
        </div>
    </div>
@else
    <div class="alert alert-success d-flex align-items-center mb-3">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <div>Inspección sin hallazgos. Todos los ítems en orden.</div>
    </div>
@endif

    {{-- Datos operativos --}}
    <div class="card mb-3">
        <div class="card-header bg-dark text-white py-2">
            <i class="bi bi-speedometer2 me-1"></i> Datos operativos
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <small class="text-muted d-block">Kilometraje</small>
                    <span class="fw-bold">{{ $inspeccion->kilometraje ?: '—' }}</span>
                </div>

                @if($inspeccion->plantilla->tipo_unidad === 'liviano')
                    <div class="col-6 col-md-3">
                        <small class="text-muted d-block">Próxima mantención</small>
                        <span class="fw-bold">{{ $inspeccion->proxima_mantencion ?: '—' }}</span>
                    </div>
                @else
                    <div class="col-6 col-md-3">
                        <small class="text-muted d-block">Hora Motor</small>
                        <span class="fw-bold">{{ $inspeccion->hora_motor ?: '—' }}</span>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted d-block">Hora Bomba</small>
                        <span class="fw-bold">{{ $inspeccion->hora_bomba ?: '—' }}</span>
                    </div>
                    <div class="col-6 col-md-3">
                        <small class="text-muted d-block">Últ. cambio aceite</small>
                        <span class="fw-bold">{{ $inspeccion->hora_ultimo_cambio_aceite ?: '—' }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
{{-- Secciones con respuestas (solo lectura) --}}
@foreach($inspeccion->plantilla->secciones as $seccion)
    <div class="card mb-3">
        <div class="card-header py-2">
            <strong>{{ $seccion->nombre }}</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <tbody>
                        @foreach($seccion->items as $item)
                            @php
                                $respuesta = $respuestasMap[$item->id] ?? null;
                                $valor = $respuesta->valor ?? null;

                                $badgeClass = match($valor) {
                                    'bueno', 'full', '3/4', 'ok' => 'bg-success',
                                    'regular', '1/2'             => 'bg-warning text-dark',
                                    'malo', 'vencido', '1/4'     => 'bg-danger',
                                    'no_aplica'                  => 'bg-secondary',
                                    default                      => 'bg-light text-dark',
                                };

                                $valorDisplay = match($valor) {
                                    'bueno'     => 'Bueno',
                                    'regular'   => 'Regular',
                                    'malo'      => 'Malo',
                                    'full'      => 'FULL',
                                    'no_aplica' => 'N/A',
                                    'ok'        => 'OK',
                                    'vencido'   => 'Vencido',
                                    '1/4'       => '1/4',
                                    '1/2'       => '1/2',
                                    '3/4'       => '3/4',
                                    default     => 'Sin respuesta',
                                };
                            @endphp
                            <tr class="{{ in_array($valor, ['malo', 'vencido']) ? 'table-danger' : (in_array($valor, ['regular', '1/4']) ? 'table-warning' : '') }}">
                                <td class="ps-3 {{ $item->es_critico ? 'fw-bold' : '' }}">
                                    {{ $item->nombre }}
                                    @if($item->es_critico)
                                        <i class="bi bi-exclamation-triangle-fill text-danger ms-1"></i>
                                    @endif
                                </td>
                                <td class="text-end pe-3" style="width: 100px;">
                                    <span class="badge {{ $badgeClass }}">{{ $valorDisplay }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endforeach

{{-- Observaciones --}}
@if($inspeccion->observaciones)
    <div class="card mb-3">
        <div class="card-header py-2">
            <strong><i class="bi bi-chat-left-text me-1"></i> Observaciones</strong>
        </div>
        <div class="card-body">
            <p class="mb-0">{{ $inspeccion->observaciones }}</p>
        </div>
    </div>
@endif

{{-- Hallazgos detectados --}}
@if($inspeccion->hallazgos->isNotEmpty())
    <div class="card mb-4">
        <div class="card-header bg-danger text-white py-2">
            <i class="bi bi-exclamation-diamond me-1"></i> Hallazgos detectados ({{ $inspeccion->hallazgos->count() }})
        </div>
        <div class="list-group list-group-flush">
            @foreach($inspeccion->hallazgos->sortByDesc('severidad') as $hallazgo)
                <a href="{{ route('hallazgos.show', $hallazgo) }}" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge {{ $hallazgo->severidad === 'critico' ? 'bg-danger' : ($hallazgo->severidad === 'atencion' ? 'bg-warning text-dark' : 'bg-info') }} me-2">
                                {{ strtoupper($hallazgo->severidad) }}
                            </span>
                            <strong>{{ $hallazgo->item->nombre }}</strong>
                            <small class="text-muted ms-2">{{ $hallazgo->item->seccion->nombre }}</small>
                        </div>
                        <span class="badge bg-secondary">{{ $hallazgo->estado }}</span>
                    </div>
                    @if($hallazgo->fotos->isNotEmpty())
                        <small class="text-muted"><i class="bi bi-camera me-1"></i>{{ $hallazgo->fotos->count() }} foto(s)</small>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
@endif
@endsection