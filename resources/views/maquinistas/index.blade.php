@extends('layouts.app')

@section('title', 'Maquinistas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>Maquinistas</h4>
    <a href="{{ route('maquinistas.create') }}" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Nuevo Maquinista
    </a>
</div>

{{-- Filtro por compañía --}}
<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('maquinistas.index') }}" class="d-flex align-items-center gap-3">
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
                <a href="{{ route('maquinistas.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Limpiar
                </a>
            @endif
            <span class="text-muted small">{{ $maquinistas->count() }} maquinista(s)</span>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>RUT</th>
                    <th>Compañía</th>
                    <th>Cargo</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($maquinistas as $maquinista)
                <tr>
                    <td>
                        <div class="fw-bold">{{ $maquinista->nombre }}</div>
                        @if($maquinista->turnoActivo)
                            <span class="badge bg-success"><i class="bi bi-circle-fill me-1" style="font-size:8px"></i>En Servicio</span>
                        @endif
                    </td>
                    <td>{{ $maquinista->rut }}</td>
                    <td>{{ $maquinista->compania->nombre }}</td>
                    <td>{{ $maquinista->cargo ?? '—' }}</td>
                    <td>{{ $maquinista->telefono ?? '—' }}</td>
                    <td>
                        @if($maquinista->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-secondary">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('maquinistas.show', $maquinista) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('maquinistas.edit', $maquinista) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('maquinistas.destroy', $maquinista) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar este maquinista?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No hay maquinistas registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection