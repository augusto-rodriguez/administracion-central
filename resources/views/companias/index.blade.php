@extends('layouts.app')

@section('title', 'Compañías')

@section('content')

{{-- Encabezado --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <h4 class="mb-0"><i class="bi bi-building me-2"></i>Compañías</h4>
    <a href="{{ route('companias.create') }}" class="btn btn-danger btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nueva Compañía
    </a>
</div>

<div class="card">
    <div class="card-body p-0">

        {{-- Tabla desktop --}}
        <div class="table-responsive">
            <table class="table table-hover mb-0 d-none d-md-table">
                <thead class="table-light">
                    <tr>
                        <th>N°</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Voluntarios</th>
                        <th>Unidades</th>
                        <th>Especialidades</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companias as $compania)
                    <tr>
                        <td><span class="badge bg-danger">{{ $compania->numero }}</span></td>
                        <td class="fw-bold text-nowrap">{{ $compania->nombre }}</td>
                        <td>{{ $compania->direccion ?? '—' }}</td>
                        <td class="text-nowrap">{{ $compania->telefono ?? '—' }}</td>
                        <td><span class="badge bg-success">{{ $compania->voluntarios_count }}</span></td>
                        <td><span class="badge bg-primary">{{ $compania->unidades_count }}</span></td>
                        <td>
                            @forelse($compania->especialidades as $esp)
                                <span class="badge bg-info text-dark">{{ $esp->nombre }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>
                        <td>
                            @if($compania->activa)
                                <span class="badge bg-success">Activa</span>
                            @else
                                <span class="badge bg-secondary">Inactiva</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
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
                        <td colspan="9" class="text-center text-muted py-4">No hay compañías registradas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Cards móvil --}}
        <div class="d-md-none">
            @forelse($companias as $compania)
                <div class="border-bottom px-3 py-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="badge bg-danger me-1">{{ $compania->numero }}</span>
                            <span class="fw-bold">{{ $compania->nombre }}</span>
                            @if($compania->activa)
                                <span class="badge bg-success ms-1">Activa</span>
                            @else
                                <span class="badge bg-secondary ms-1">Inactiva</span>
                            @endif
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0 ms-2">
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
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 small text-muted mb-2">
                        @if($compania->direccion)
                            <span><i class="bi bi-geo-alt me-1"></i>{{ $compania->direccion }}</span>
                        @endif
                        @if($compania->telefono)
                            <span><i class="bi bi-telephone me-1"></i>{{ $compania->telefono }}</span>
                        @endif
                    </div>

                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-success">{{ $compania->voluntarios_count }} voluntarios</span>
                        <span class="badge bg-primary">{{ $compania->unidades_count }} unidades</span>
                        @forelse($compania->especialidades as $esp)
                            <span class="badge bg-info text-dark">{{ $esp->nombre }}</span>
                        @empty
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">No hay compañías registradas</div>
            @endforelse
        </div>

    </div>
</div>
@endsection