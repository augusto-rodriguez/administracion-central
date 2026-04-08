@extends('layouts.app')

@section('title', 'Unidades')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-truck-front me-2"></i>Unidades</h4>
    <a href="{{ route('unidades.create') }}" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Nueva Unidad
    </a>
</div>

{{-- Filtro por compañía --}}
<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('unidades.index') }}" class="d-flex align-items-center gap-3">
            <label class="fw-bold mb-0 text-nowrap">Filtrar por compañía:</label>
            <select name="compania_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="">Todas las compañías</option>
                @foreach($companias as $compania)
                    <option value="{{ $compania->id }}" {{ request('compania_id') == $compania->id ? 'selected' : '' }}>
                        {{ $compania->numero }} - {{ $compania->nombre }}
                    </option>
                @endforeach
            </select>
            @if(request('compania_id'))
                <a href="{{ route('unidades.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Limpiar
                </a>
            @endif
            <span class="text-muted small">{{ $unidades->count() }} unidad(es)</span>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
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
                    <td class="fw-bold">{{ $unidad->nombre }}</td>
                    <td><span class="badge bg-dark">{{ $unidad->patente }}</span></td>
                    <td><span class="badge bg-info text-dark">{{ $unidad->tipo }}</span></td>
                    <td>{{ $unidad->compania->nombre }}</td>
                    <td>{{ $unidad->descripcion ?? '—' }}</td>
                    <td>
                        @if($unidad->activa)
                            <span class="badge bg-success">Activa</span>
                        @else
                            <span class="badge bg-secondary">Inactiva</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('unidades.edit', $unidad) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('unidades.destroy', $unidad) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar esta unidad?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
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
</div>
@endsection