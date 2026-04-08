@extends('layouts.app')
@section('title', 'Guardias Nocturnas')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-moon-stars me-2"></i>Guardias Nocturnas</h4>

    @if(!auth()->user()->esAdmin() && !auth()->user()->esComandante())
        @if($guardiaHoy)
            <a href="{{ $guardiaHoy->esCerrada() ? route('guardias-nocturnas.show', $guardiaHoy) : route('guardias-nocturnas.edit', $guardiaHoy) }}"
            class="btn btn-warning">
                <i class="bi bi-pencil-square me-1"></i>
                {{ $guardiaHoy->esCerrada() ? 'Ver guardia de hoy' : 'Continuar guardia de hoy' }}
            </a>
        @else
            <form action="{{ route('guardias-nocturnas.iniciar') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-moon-stars me-1"></i>Iniciar guardia nocturna
                </button>
            </form>
        @endif
    @else
        {{-- Admin/Comandante: solo puede ver, no iniciar --}}
        @if($guardiaHoy)
            <a href="{{ route('guardias-nocturnas.show', $guardiaHoy) }}"
            class="btn btn-outline-primary btn-sm">
                <i class="bi bi-eye me-1"></i>Ver guardia de hoy
            </a>
        @endif
    @endif
</div>

<div class="card">
    <div class="card-body p-0">
        {{-- Filtro por fecha --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('guardias-nocturnas.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Fecha exacta</label>
                            <input type="date" name="fecha" class="form-control"
                                value="{{ request('fecha') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Desde</label>
                            <input type="date" name="desde" class="form-control"
                                value="{{ request('desde') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Hasta</label>
                            <input type="date" name="hasta" class="form-control"
                                value="{{ request('hasta') }}">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-danger flex-grow-1">
                                <i class="bi bi-search me-1"></i>Filtrar
                            </button>
                            <a href="{{ route('guardias-nocturnas.index') }}"
                            class="btn btn-outline-secondary {{ request()->hasAny(['desde', 'hasta', 'fecha']) ? '' : 'invisible' }}">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <table class="table table-hover mb-0">
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
                    <td class="fw-bold">{{ $guardia->fecha->format('d/m/Y') }}</td>
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
                    <td class="text-muted small">{{ $guardia->cerradoPor->nombre ?? '—' }}</td>
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
</div>

<div class="mt-3">{{ $guardias->links() }}</div>

@endsection