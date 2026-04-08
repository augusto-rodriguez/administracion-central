@extends('layouts.app')
@section('title', 'Oficiales')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-star me-2"></i>Oficiales</h4>
    <a href="{{ route('oficiales.create') }}" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Nuevo Oficial
    </a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Cargo</th>
                    <th>Compañía</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($oficiales as $oficial)
                <tr>
                    <td class="fw-bold">{{ $oficial->nombre }}</td>
                    <td>{{ $oficial->cargo ?? '—' }}</td>
                    <td>{{ $oficial->compania->nombre }}</td>
                    <td>{{ $oficial->telefono ?? '—' }}</td>
                    <td>
                        @if($oficial->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-secondary">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('oficiales.edit', $oficial) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('oficiales.destroy', $oficial) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('¿Eliminar este oficial?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No hay oficiales registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection