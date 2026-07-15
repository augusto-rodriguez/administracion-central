@extends('layouts.app')
@section('title', 'Guardias Nocturnas')
@section('content')

{{-- Encabezado --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <h4 class="mb-0"><i class="bi bi-moon-stars me-2"></i>Guardias Nocturnas</h4>

    @if(!auth()->user()->esAdmin() && !auth()->user()->esComandante())
        @if($guardiaHoy)
            <a href="{{ $guardiaHoy->esCerrada() ? route('guardias-nocturnas.show', $guardiaHoy) : route('guardias-nocturnas.edit', $guardiaHoy) }}"
            class="btn btn-warning btn-sm">
                <i class="bi bi-pencil-square me-1"></i>
                {{ $guardiaHoy->esCerrada() ? 'Ver guardia de hoy' : 'Continuar guardia de hoy' }}
            </a>
        @else
            <form action="{{ route('guardias-nocturnas.iniciar') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-moon-stars me-1"></i>Iniciar guardia nocturna
                </button>
            </form>
        @endif
    @else
        @if($guardiaHoy)
            <a href="{{ route('guardias-nocturnas.show', $guardiaHoy) }}"
            class="btn btn-outline-primary btn-sm">
                <i class="bi bi-eye me-1"></i>Ver guardia de hoy
            </a>
        @endif
    @endif
</div>

{{-- Filtro por fecha --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('guardias-nocturnas.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label fw-bold mb-1 small">Fecha exacta</label>
                    <input type="date" name="fecha" class="form-control form-control-sm"
                        value="{{ request('fecha') }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold mb-1 small">Desde</label>
                    <input type="date" name="desde" class="form-control form-control-sm"
                        value="{{ request('desde') }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold mb-1 small">Hasta</label>
                    <input type="date" name="hasta" class="form-control form-control-sm"
                        value="{{ request('hasta') }}">
                </div>
                <div class="col-12 col-md-3">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-danger btn-sm flex-grow-1">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                        <a href="{{ route('guardias-nocturnas.index') }}"
                        class="btn btn-outline-secondary btn-sm {{ request()->hasAny(['desde', 'hasta', 'fecha']) ? '' : 'invisible' }}">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">

        {{-- Tabla desktop --}}
        <div class="table-responsive">
            <table class="table table-hover mb-0 d-none d-md-table">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Compañías reportadas</th>
                        <th>Cerrado por</th>
                        <th>Cerrado a las</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guardias as $guardia)
                    <tr>
                        <td class="fw-bold text-nowrap">{{ $guardia->fecha->format('d/m/Y') }}</td>
                        <td>
                            @if($guardia->estado === 'abierta')
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-unlock me-1"></i>Abierta
                                </span>
                            @else
                                <span class="badge bg-success">
                                    <i class="bi bi-lock me-1"></i>Cerrada
                                </span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $guardia->companias->where('sin_reporte', false)->count() }}
                            / {{ $guardia->companias->count() }} compañías
                        </td>
                        <td class="text-muted small text-nowrap">{{ $guardia->cerradoPor->nombre ?? '—' }}</td>
                        <td class="text-muted small">
                            {{ $guardia->cerrado_at ? $guardia->cerrado_at->format('H:i') : '—' }}
                        </td>
                        <td class="text-end">
                            @if($guardia->estado === 'abierta')
                                <a href="{{ route('guardias-nocturnas.edit', $guardia) }}"
                                   class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @else
                                <a href="{{ route('guardias-nocturnas.show', $guardia) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No hay guardias nocturnas registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Cards móvil --}}
        <div class="d-md-none">
            @forelse($guardias as $guardia)
                <div class="border-bottom px-3 py-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="fw-bold">{{ $guardia->fecha->format('d/m/Y') }}</span>
                            @if($guardia->estado === 'abierta')
                                <span class="badge bg-warning text-dark ms-1">
                                    <i class="bi bi-unlock me-1"></i>Abierta
                                </span>
                            @else
                                <span class="badge bg-success ms-1">
                                    <i class="bi bi-lock me-1"></i>Cerrada
                                </span>
                            @endif
                        </div>
                        @if($guardia->estado === 'abierta')
                            <a href="{{ route('guardias-nocturnas.edit', $guardia) }}"
                               class="btn btn-sm btn-outline-warning flex-shrink-0">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @else
                            <a href="{{ route('guardias-nocturnas.show', $guardia) }}"
                               class="btn btn-sm btn-outline-primary flex-shrink-0">
                                <i class="bi bi-eye"></i>
                            </a>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap gap-2 small text-muted">
                        <span>
                            <i class="bi bi-building me-1"></i>
                            {{ $guardia->companias->where('sin_reporte', false)->count() }}
                            / {{ $guardia->companias->count() }} compañías
                        </span>
                        @if($guardia->cerradoPor)
                            <span><i class="bi bi-person me-1"></i>{{ $guardia->cerradoPor->nombre }}</span>
                        @endif
                        @if($guardia->cerrado_at)
                            <span><i class="bi bi-clock me-1"></i>{{ $guardia->cerrado_at->format('H:i') }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">No hay guardias nocturnas registradas.</div>
            @endforelse
        </div>

    </div>
</div>

<div class="mt-3">{{ $guardias->links() }}</div>

@endsection