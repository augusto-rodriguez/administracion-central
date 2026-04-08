@extends('layouts.app')

@section('title', 'Compañías')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-building me-2"></i>Compañías</h4>
    <a href="{{ route('companias.create') }}" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Nueva Compañía
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>N°</th>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Maquinistas</th>
                    <th>Unidades</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($companias as $compania)
                <tr>
                    <td><span class="badge bg-danger">{{ $compania->numero }}</span></td>
                    <td class="fw-bold">{{ $compania->nombre }}</td>
                    <td>{{ $compania->direccion ?? '—' }}</td>
                    <td>{{ $compania->telefono ?? '—' }}</td>
                    <td><span class="badge bg-success">{{ $compania->voluntarios_count }}</span></td>
                    <td><span class="badge bg-primary">{{ $compania->unidades_count }}</span></td>
                    <td>
                        @if($compania->activa)
                            <span class="badge bg-success">Activa</span>
                        @else
                            <span class="badge bg-secondary">Inactiva</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('companias.edit', $compania) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('companias.destroy', $compania) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar esta compañía?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No hay compañías registradas</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection