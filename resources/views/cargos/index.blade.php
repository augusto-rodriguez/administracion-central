@extends('layouts.app')

@section('title', 'Cargos')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-award me-2"></i>Cargos</h4>
    <a href="{{ route('cargos.create') }}" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Nuevo Cargo
    </a>
</div>

{{-- Cargos de Compañía --}}
<div class="card mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-building me-2"></i>Cargos de Compañía
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($cargos->where('tipo', 'compania') as $cargo)
                <tr>
                    <td class="fw-bold">{{ $cargo->nombre }}</td>
                    <td class="text-muted small">{{ $cargo->descripcion ?? '—' }}</td>
                    <td>
                        @if($cargo->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-secondary">Inactivo</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('cargos.edit', $cargo) }}"
                        class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('cargos.destroy', $cargo) }}" method="POST"
                            class="d-inline"
                            onsubmit="return confirm('¿Eliminar el cargo {{ $cargo->nombre }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">Sin cargos de compañía registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Cargos Generales --}}
<div class="card">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-star me-2"></i>Cargos Generales del Cuerpo
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($cargos->where('tipo', 'general') as $cargo)
                <tr>
                    <td class="fw-bold">{{ $cargo->nombre }}</td>
                    <td class="text-muted small">{{ $cargo->descripcion ?? '—' }}</td>
                    <td>
                        @if($cargo->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-secondary">Inactivo</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('cargos.edit', $cargo) }}"
                        class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('cargos.destroy', $cargo) }}" method="POST"
                            class="d-inline"
                            onsubmit="return confirm('¿Eliminar el cargo {{ $cargo->nombre }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">Sin cargos generales registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection