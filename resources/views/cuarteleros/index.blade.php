@extends('layouts.app')
@section('title', 'Cuarteleros')
@section('content')

@php $puedeGestionar = auth()->user()->esComandante() || auth()->user()->esAdmin(); @endphp

{{-- Encabezado --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>Cuarteleros</h4>
    @if($puedeGestionar)
    <a href="{{ route('cuarteleros.create') }}" class="btn btn-danger btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nuevo Cuartelero
    </a>
    @endif
</div>

{{-- Activos --}}
<div class="card mb-4">
    <div class="card-header fw-bold">
        <i class="bi bi-person-check me-1"></i>En cargo actualmente
    </div>
    <div class="card-body p-0">

        {{-- Tabla desktop --}}
        <div class="table-responsive">
            <table class="table table-hover mb-0 d-none d-md-table">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>RUT</th>
                        <th>Compañía</th>
                        <th>Desde</th>
                        <th>Unidades autorizadas</th>
                        <th>Turno</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cuarteleros->filter(fn($c) => $c->estaActivo()) as $cuartelero)
                    <tr>
                        <td class="fw-bold text-nowrap">{{ $cuartelero->nombre }}</td>
                        <td class="text-nowrap">{{ $cuartelero->rut ?? '—' }}</td>
                        <td class="text-nowrap">{{ $cuartelero->compania->nombre }}</td>
                        <td class="text-nowrap">{{ $cuartelero->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            @foreach($cuartelero->unidadesAutorizadas as $unidad)
                                <span class="badge bg-secondary">{{ $unidad->nombre }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if($cuartelero->turnoActivo)
                                <span class="badge bg-success">En turno</span>
                            @else
                                <span class="text-muted small">Sin turno</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('cuarteleros.show', $cuartelero) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($puedeGestionar)
                            <a href="{{ route('cuarteleros.edit', $cuartelero) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No hay cuarteleros activos</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Cards móvil --}}
        <div class="d-md-none">
            @forelse($cuarteleros->filter(fn($c) => $c->estaActivo()) as $cuartelero)
                <div class="border-bottom px-3 py-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="fw-bold">{{ $cuartelero->nombre }}</span>
                            <div class="text-muted small">{{ $cuartelero->compania->nombre }}</div>
                        </div>
                        <div>
                            @if($cuartelero->turnoActivo)
                                <span class="badge bg-success">En turno</span>
                            @else
                                <span class="text-muted small">Sin turno</span>
                            @endif
                        </div>
                    </div>

                    @if($cuartelero->unidadesAutorizadas->count())
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            @foreach($cuartelero->unidadesAutorizadas as $unidad)
                                <span class="badge bg-secondary">{{ $unidad->nombre }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-wrap gap-2 small text-muted">
                            @if($cuartelero->rut)
                                <span><i class="bi bi-person-vcard me-1"></i>{{ $cuartelero->rut }}</span>
                            @endif
                            @if($cuartelero->fecha_inicio)
                                <span><i class="bi bi-calendar me-1"></i>Desde {{ $cuartelero->fecha_inicio->format('d/m/Y') }}</span>
                            @endif
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0 ms-2">
                            <a href="{{ route('cuarteleros.show', $cuartelero) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($puedeGestionar)
                            <a href="{{ route('cuarteleros.edit', $cuartelero) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">No hay cuarteleros activos</div>
            @endforelse
        </div>

    </div>
</div>

{{-- Histórico --}}
@php $historico = $cuarteleros->filter(fn($c) => !$c->estaActivo()); @endphp
@if($historico->count())
<div class="card">
    <div class="card-header fw-bold text-muted">
        <i class="bi bi-clock-history me-1"></i>Historial de cuarteleros
    </div>
    <div class="card-body p-0">

        {{-- Tabla desktop --}}
        <div class="table-responsive">
            <table class="table table-hover mb-0 text-muted d-none d-md-table">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>RUT</th>
                        <th>Compañía</th>
                        <th>Período</th>
                        <th>Motivo salida</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($historico as $cuartelero)
                    <tr>
                        <td class="text-nowrap">{{ $cuartelero->nombre }}</td>
                        <td class="text-nowrap">{{ $cuartelero->rut ?? '—' }}</td>
                        <td class="text-nowrap">{{ $cuartelero->compania->nombre }}</td>
                        <td class="text-nowrap small">{{ $cuartelero->periodoFormateado() }}</td>
                        <td>{{ $cuartelero->motivo_fin ?? '—' }}</td>
                        <td>
                            <a href="{{ route('cuarteleros.show', $cuartelero) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Cards móvil --}}
        <div class="d-md-none">
            @foreach($historico as $cuartelero)
                <div class="border-bottom px-3 py-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="text-muted">{{ $cuartelero->nombre }}</span>
                            <div class="text-muted small">{{ $cuartelero->compania->nombre }}</div>
                        </div>
                        <a href="{{ route('cuarteleros.show', $cuartelero) }}"
                           class="btn btn-sm btn-outline-secondary flex-shrink-0">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                    <div class="d-flex flex-wrap gap-2 small text-muted">
                        @if($cuartelero->rut)
                            <span><i class="bi bi-person-vcard me-1"></i>{{ $cuartelero->rut }}</span>
                        @endif
                        <span><i class="bi bi-calendar-range me-1"></i>{{ $cuartelero->periodoFormateado() }}</span>
                    </div>
                    @if($cuartelero->motivo_fin)
                        <div class="small text-muted mt-1">
                            <i class="bi bi-chat-left-text me-1"></i>{{ $cuartelero->motivo_fin }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

    </div>
</div>
@endif

@endsection