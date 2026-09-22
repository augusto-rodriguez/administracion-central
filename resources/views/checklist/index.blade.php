@extends('layouts.app')

@section('title', 'Mis Inspecciones')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Inspecciones de Material Mayor</h4>
    <a href="{{ route('checklist.create') }}" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Nueva Inspección
    </a>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('checklist.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">Unidad</label>
                <select name="unidad_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($unidades as $unidad)
                        <option value="{{ $unidad->id }}" {{ request('unidad_id') == $unidad->id ? 'selected' : '' }}>
                            {{ $unidad->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="borrador" {{ request('estado') === 'borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="completado" {{ request('estado') === 'completado' ? 'selected' : '' }}>Completado</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Listado --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Unidad</th>
                    <th>Cuartelero</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Hallazgos</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inspecciones as $inspeccion)
                    <tr>
                        <td>
                            <span class="fw-bold">{{ $inspeccion->fecha->format('d/m/Y') }}</span>
                            <br><small class="text-muted">{{ $inspeccion->created_at->format('H:i') }}</small>
                        </td>
                        <td>{{ $inspeccion->unidad->nombre ?? '—' }}</td>
                        <td>{{ $inspeccion->cuartelero->nombre ?? '—' }}</td>
                        <td class="text-center">
                            @if($inspeccion->estado === 'completado')
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Completado</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="bi bi-pencil me-1"></i>Borrador</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($inspeccion->hallazgos_abiertos_count > 0)
                                <span class="badge bg-danger">{{ $inspeccion->hallazgos_abiertos_count }} abierto(s)</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($inspeccion->estado === 'borrador')
                                <a href="{{ route('checklist.edit', $inspeccion) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil-square me-1"></i>Continuar
                                </a>
                            @else
                                <a href="{{ route('checklist.show', $inspeccion) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i>Ver
                                </a>
                            @endif

                            @if(auth()->user()->esAdmin() || auth()->user()->esComandante())
                                <form action="{{ route('checklist.reabrir', $inspeccion) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning"
                                            onclick="return confirm('¿Reabrir esta inspección? Se eliminarán los hallazgos generados.')">
                                        <i class="bi bi-unlock me-1"></i>Reabrir
                                    </button>
                                </form>
                                <form action="{{ route('checklist.destroy', $inspeccion) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('⚠️ ¿Eliminar esta inspección PERMANENTEMENTE?\n\nSe borrarán todas las respuestas, hallazgos, fotos y comentarios asociados.\n\nEsta acción NO se puede deshacer.')">
                                        <i class="bi bi-trash me-1"></i>Eliminar
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-clipboard-x fs-3 d-block mb-2"></i>
                            No hay inspecciones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($inspecciones->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $inspecciones->withQueryString()->links() }}
    </div>
@endif
@endsection