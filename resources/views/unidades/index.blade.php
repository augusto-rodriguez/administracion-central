@extends('layouts.app')

@section('title', 'Unidades')

@section('content')

@php $esCapitan = auth()->user()->esCapitanCia(); @endphp

{{-- Encabezado --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <h4 class="mb-0">
        <i class="bi bi-truck-front me-2"></i>Unidades
        @if($esCapitan)
            <span class="text-muted fs-6 fw-normal ms-2">
                — {{ auth()->user()->voluntario?->compania->nombre }}
            </span>
        @endif
    </h4>
    @if(!$esCapitan)
        <a href="{{ route('unidades.create') }}" class="btn btn-danger btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nueva Unidad
        </a>
    @endif
</div>

{{-- Filtro por compañía --}}
@if(!$esCapitan)
<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('unidades.index') }}"
              class="d-flex flex-column flex-md-row align-items-md-center gap-2">
            <label class="fw-bold mb-0 text-nowrap small">Filtrar por compañía:</label>
            <select name="compania_id" class="form-select form-select-sm" style="max-width:300px;" onchange="this.form.submit()">
                <option value="">Todas las compañías</option>
                @foreach($companias as $compania)
                    <option value="{{ $compania->id }}" {{ request('compania_id') == $compania->id ? 'selected' : '' }}>
                        {{ $compania->numero }} - {{ $compania->nombre }}
                    </option>
                @endforeach
            </select>
            <div class="d-flex align-items-center gap-2">
                @if(request('compania_id'))
                    <a href="{{ route('unidades.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
                <span class="text-muted small">{{ $unidades->count() }} unidad(es)</span>
            </div>
        </form>
    </div>
</div>
@else
<div class="mb-3">
    <span class="text-muted small">{{ $unidades->count() }} unidad(es)</span>
</div>
@endif

<div class="card">
    <div class="card-body p-0">

        {{-- Tabla desktop --}}
        <div class="table-responsive">
            <table class="table table-hover mb-0 d-none d-md-table">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Patente</th>
                        <th>Tipo</th>
                        <th>Compañía</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($unidades as $unidad)
                    <tr>
                        <td class="fw-bold text-nowrap">{{ $unidad->nombre }}</td>
                        <td><span class="badge bg-dark">{{ $unidad->patente }}</span></td>
                        <td class="text-nowrap"><span class="badge bg-info text-dark">{{ $unidad->tipo }}</span></td>
                        <td class="text-nowrap">{{ $unidad->compania->nombre }}</td>
                        <td>{{ $unidad->descripcion ?? '—' }}</td>
                        <td>
                            @if($unidad->activa)
                                <span class="badge bg-success">Activa</span>
                            @else
                                <span class="badge bg-secondary">Inactiva</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('unidades.edit', $unidad) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if(!$esCapitan)
                                <form action="{{ route('unidades.destroy', $unidad) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar esta unidad?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No hay unidades registradas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Cards móvil --}}
        <div class="d-md-none">
            @forelse($unidades as $unidad)
                <div class="border-bottom px-3 py-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="fw-bold">{{ $unidad->nombre }}</span>
                            <span class="badge bg-dark ms-1">{{ $unidad->patente }}</span>
                            @if($unidad->activa)
                                <span class="badge bg-success ms-1">Activa</span>
                            @else
                                <span class="badge bg-secondary ms-1">Inactiva</span>
                            @endif
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0 ms-2">
                            <a href="{{ route('unidades.edit', $unidad) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if(!$esCapitan)
                                <form action="{{ route('unidades.destroy', $unidad) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar esta unidad?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 small text-muted">
                        <span><span class="badge bg-info text-dark">{{ $unidad->tipo }}</span></span>
                        <span><i class="bi bi-building me-1"></i>{{ $unidad->compania->nombre }}</span>
                    </div>
                    @if($unidad->descripcion)
                        <div class="small text-muted mt-1">{{ $unidad->descripcion }}</div>
                    @endif
                </div>
            @empty
                <div class="text-center text-muted py-4">No hay unidades registradas</div>
            @endforelse
        </div>

    </div>
</div>
@endsection