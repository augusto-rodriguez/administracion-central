@extends('layouts.app')

@section('title', 'Plantillas de Checklist')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-gear me-2"></i>Plantillas de Checklist</h4>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <!-- <th>Tipo de unidad</th>  -->
                    <th class="text-center">Secciones</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plantillas as $plantilla)
                    <tr>
                        <td>
                            <strong>{{ $plantilla->nombre }}</strong>
                            @if($plantilla->descripcion)
                                <br><small class="text-muted">{{ Str::limit($plantilla->descripcion, 60) }}</small>
                            @endif
                        </td>
                        <!-- <td>{{ $plantilla->tipo_unidad ?? 'Todas' }}</td> -->
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $plantilla->secciones_count }}</span>
                        </td>
                        <td class="text-center">
                            @if($plantilla->activa)
                                <span class="badge bg-success">Activa</span>
                            @else
                                <span class="badge bg-secondary">Inactiva</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('checklist-plantillas.edit', $plantilla) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil me-1"></i>Editar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No hay plantillas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection