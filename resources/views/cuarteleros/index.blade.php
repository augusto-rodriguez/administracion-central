@extends('layouts.app')
@section('title', 'Cuarteleros')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>Cuarteleros</h4>
    @if(auth()->user()->esComandante())
    <a href="{{ route('cuarteleros.create') }}" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Nuevo Cuartelero
    </a>
    @endif
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>RUT</th>
                    <th>Compañía</th>
                    <th>Unidades autorizadas</th>
                    <th>Estado</th>
                    <th>Turno</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($cuarteleros as $cuartelero)
                <tr>
                    <td class="fw-bold">{{ $cuartelero->nombre }}</td>
                    <td>{{ $cuartelero->rut ?? '—' }}</td>
                    <td>{{ $cuartelero->compania->nombre }}</td>
                    <td>
                        @foreach($cuartelero->unidadesAutorizadas as $unidad)
                            <span class="badge bg-secondary">{{ $unidad->nombre }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if($cuartelero->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-secondary">Inactivo</span>
                        @endif
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
                        @if(auth()->user()->esComandante())
                        <a href="{{ route('cuarteleros.edit', $cuartelero) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No hay cuarteleros registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection