@extends('layouts.app')
@section('title', 'Cuarteleros')
@section('content')

@php $puedeGestionar = auth()->user()->esComandante() || auth()->user()->esAdmin(); @endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>Cuarteleros</h4>
    @if($puedeGestionar)
    <a href="{{ route('cuarteleros.create') }}" class="btn btn-danger">
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
        <table class="table table-hover mb-0">
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
                    <td class="fw-bold">{{ $cuartelero->nombre }}</td>
                    <td>{{ $cuartelero->rut ?? '—' }}</td>
                    <td>{{ $cuartelero->compania->nombre }}</td>
                    <td>{{ $cuartelero->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
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
                    <td>
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
</div>

{{-- Histórico --}}
@php $historico = $cuarteleros->filter(fn($c) => !$c->estaActivo()); @endphp
@if($historico->count())
<div class="card">
    <div class="card-header fw-bold text-muted">
        <i class="bi bi-clock-history me-1"></i>Historial de cuarteleros
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0 text-muted">
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
                    <td>{{ $cuartelero->nombre }}</td>
                    <td>{{ $cuartelero->rut ?? '—' }}</td>
                    <td>{{ $cuartelero->compania->nombre }}</td>
                    <td><span class="small">{{ $cuartelero->periodoFormateado() }}</span></td>
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
</div>
@endif

@endsection