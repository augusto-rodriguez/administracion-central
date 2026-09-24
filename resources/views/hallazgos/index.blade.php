@extends('layouts.app')

@section('title', 'Hallazgos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-exclamation-diamond me-2"></i>Hallazgos</h4>
    <a href="{{ route('checklist.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>
{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('hallazgos.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Abiertos</option>
                    <option value="abierto" {{ request('estado') === 'abierto' ? 'selected' : '' }}>Abierto</option>
                    <option value="en_revision" {{ request('estado') === 'en_revision' ? 'selected' : '' }}>En revisión</option>
                    <option value="en_reparacion" {{ request('estado') === 'en_reparacion' ? 'selected' : '' }}>En reparación</option>
                    <option value="resuelto_verificado" {{ request('estado') === 'resuelto_verificado' ? 'selected' : '' }}>Resuelto y Verificado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Severidad</label>
                <select name="severidad" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <option value="critico" {{ request('severidad') === 'critico' ? 'selected' : '' }}>Crítico</option>
                    <option value="atencion" {{ request('severidad') === 'atencion' ? 'selected' : '' }}>Atención</option>
                    <option value="info" {{ request('severidad') === 'info' ? 'selected' : '' }}>Info</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Listado --}}
<div class="card">
    <div class="list-group list-group-flush">
        @forelse($hallazgos as $hallazgo)
            <a href="{{ route('hallazgos.show', $hallazgo) }}" class="list-group-item list-group-item-action py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="me-3">
                        <span class="badge {{ $hallazgo->severidad === 'critico' ? 'bg-danger' : ($hallazgo->severidad === 'atencion' ? 'bg-warning text-dark' : 'bg-info') }} me-1">
                            {{ strtoupper($hallazgo->severidad) }}
                        </span>
                        <strong>{{ $hallazgo->item->nombre }}</strong>
                        <small class="text-muted d-block mt-1">
                            <i class="bi bi-truck me-1"></i>{{ $hallazgo->inspeccion->unidad->nombre }}
                            · {{ $hallazgo->item->seccion->nombre }}
                            · {{ $hallazgo->created_at->format('d/m/Y H:i') }}
                        </small>
                        @if($hallazgo->asignado)
                            <small class="text-primary">
                                <i class="bi bi-person me-1"></i>Asignado a: {{ $hallazgo->asignado->name }}
                            </small>
                        @endif
                    </div>
                    <div class="text-end flex-shrink-0">
                        @php
                            $estadoBadge = match($hallazgo->estado) {
                                'abierto'               => 'bg-danger',
                                'en_revision'           => 'bg-info',
                                'en_reparacion'         => 'bg-primary',
                                'resuelto_verificado'   => 'bg-success',
                                default                 => 'bg-secondary',
                            };
                            $estadoLabel = match($hallazgo->estado) {
                                'abierto'               => 'Abierto',
                                'en_revision'           => 'En revisión',
                                'en_reparacion'         => 'En reparación',
                                'resuelto_verificado'   => 'Resuelto y Verificado',
                                default                 => $hallazgo->estado,
                            };
                        @endphp
                        <span class="badge {{ $estadoBadge }}">{{ $estadoLabel }}</span>
                        <small class="text-muted d-block mt-1">{{ $hallazgo->diasAbierto() }}d</small>
                    </div>
                </div>
            </a>
        @empty
            <div class="text-center text-muted py-5">
                <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                No hay hallazgos que coincidan con los filtros.
            </div>
        @endforelse
    </div>
</div>

@if($hallazgos->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $hallazgos->withQueryString()->links() }}
    </div>
@endif
@endsection