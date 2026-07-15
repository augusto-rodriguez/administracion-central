@extends('layouts.app')
@section('title', 'Citaciones')
@section('content')

{{-- Encabezado --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <h4 class="mb-0"><i class="bi bi-megaphone me-2"></i>Citaciones</h4>
    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCitacion">
        <i class="bi bi-plus-lg me-1"></i>Nueva Citación
    </button>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('citaciones.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-4">
                    <label class="form-label fw-bold mb-1 small">Compañía</label>
                    <select name="compania_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="cuerpo" {{ request('compania_id') === 'cuerpo' ? 'selected' : '' }}>
                            🚒 Todo el Cuerpo
                        </option>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}"
                                    {{ request('compania_id') == $compania->id ? 'selected' : '' }}>
                                {{ $compania->numero }} - {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4">
                    <label class="form-label fw-bold mb-1 small">Medio</label>
                    <select name="medio_recepcion_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($medios as $medio)
                            <option value="{{ $medio->id }}"
                                    {{ request('medio_recepcion_id') == $medio->id ? 'selected' : '' }}>
                                {{ $medio->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-danger btn-sm flex-grow-1">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                        @if(request()->hasAny(['compania_id', 'medio_recepcion_id']))
                            <a href="{{ route('citaciones.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-body p-0">

        {{-- Tabla desktop --}}
        <div class="table-responsive">
            <table class="table table-hover mb-0 d-none d-md-table">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Compañía</th>
                        <th>Medio</th>
                        <th>Mensaje</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($citaciones as $citacion)
                    <tr>
                        <td class="text-nowrap">
                            @if($citacion->fecha_citacion)
                                @php $fecha = \Carbon\Carbon::parse($citacion->fecha_citacion); @endphp
                                {{ $fecha->format('d-m-Y H:i') }}
                                @if($fecha->isPast())
                                    <span class="badge bg-secondary ms-1">Expirada</span>
                                @else
                                    <span class="badge bg-success ms-1">Vigente</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-nowrap">
                            @if($citacion->compania)
                                {{ $citacion->compania->numero }} - {{ $citacion->compania->nombre }}
                            @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-building me-1"></i>Todo el Cuerpo
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $citacion->medioRecepcion->nombre }}</span>
                        </td>
                        <td style="max-width: 400px;">{{ Str::limit($citacion->mensaje, 120) }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarCitacion"
                                    data-id="{{ $citacion->id }}"
                                    data-compania="{{ $citacion->compania_id ?? '' }}"
                                    data-medio="{{ $citacion->medio_recepcion_id }}"
                                    data-fecha="{{ $citacion->fecha_citacion
                                        ? \Carbon\Carbon::parse($citacion->fecha_citacion)->format('Y-m-d\TH:i')
                                        : '' }}"
                                    data-mensaje="{{ $citacion->mensaje }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No hay citaciones registradas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Cards móvil --}}
        <div class="d-md-none">
            @forelse($citaciones as $citacion)
                <div class="border-bottom px-3 py-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            @if($citacion->compania)
                                <span class="fw-bold">{{ $citacion->compania->numero }} - {{ $citacion->compania->nombre }}</span>
                            @else
                                <span class="badge bg-danger"><i class="bi bi-building me-1"></i>Todo el Cuerpo</span>
                            @endif
                            <span class="badge bg-primary ms-1">{{ $citacion->medioRecepcion->nombre }}</span>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary flex-shrink-0 ms-2"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditarCitacion"
                                data-id="{{ $citacion->id }}"
                                data-compania="{{ $citacion->compania_id ?? '' }}"
                                data-medio="{{ $citacion->medio_recepcion_id }}"
                                data-fecha="{{ $citacion->fecha_citacion
                                    ? \Carbon\Carbon::parse($citacion->fecha_citacion)->format('Y-m-d\TH:i')
                                    : '' }}"
                                data-mensaje="{{ $citacion->mensaje }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                    @if($citacion->fecha_citacion)
                        @php $fecha = \Carbon\Carbon::parse($citacion->fecha_citacion); @endphp
                        <div class="small text-muted mb-1">
                            <i class="bi bi-calendar me-1"></i>{{ $fecha->format('d-m-Y H:i') }}
                            @if($fecha->isPast())
                                <span class="badge bg-secondary ms-1">Expirada</span>
                            @else
                                <span class="badge bg-success ms-1">Vigente</span>
                            @endif
                        </div>
                    @endif
                    <div class="small text-muted">{{ Str::limit($citacion->mensaje, 100) }}</div>
                </div>
            @empty
                <div class="text-center text-muted py-4">No hay citaciones registradas</div>
            @endforelse
        </div>

    </div>
</div>

{{-- MODAL CREAR --}}
<div class="modal fade" id="modalCitacion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ route('citaciones.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Nueva Citación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Compañía</label>
                    <select name="compania_id" class="form-select form-select-sm">
                        <option value="">🚒 Todo el Cuerpo de Bomberos</option>
                        <option disabled>──────────────────────────────</option>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}">
                                {{ $compania->numero }} - {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Medio de recepción</label>
                    <select name="medio_recepcion_id" class="form-select form-select-sm" required>
                        @foreach($medios as $medio)
                            <option value="{{ $medio->id }}">{{ $medio->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Fecha</label>
                    <input type="datetime-local" name="fecha_citacion" class="form-control form-control-sm">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Mensaje</label>
                    <textarea name="mensaje" rows="4" class="form-control form-control-sm" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger btn-sm"><i class="bi bi-save me-1"></i>Guardar</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDITAR --}}
<div class="modal fade" id="modalEditarCitacion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" id="formEditarCitacion" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Editar Citación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Compañía</label>
                    <select name="compania_id" id="edit_compania_id" class="form-select form-select-sm">
                        <option value="">🚒 Todo el Cuerpo de Bomberos</option>
                        <option disabled>──────────────────────────────</option>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}">
                                {{ $compania->numero }} - {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Medio de recepción</label>
                    <select name="medio_recepcion_id" id="edit_medio_id" class="form-select form-select-sm" required>
                        @foreach($medios as $medio)
                            <option value="{{ $medio->id }}">{{ $medio->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Fecha</label>
                    <input type="datetime-local" name="fecha_citacion" id="edit_fecha" class="form-control form-control-sm">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Mensaje</label>
                    <textarea name="mensaje" id="edit_mensaje" rows="4" class="form-control form-control-sm" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger btn-sm"><i class="bi bi-save me-1"></i>Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('modalEditarCitacion').addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        const id = btn.dataset.id;
        document.getElementById('formEditarCitacion').action = `/citaciones/${id}`;
        document.getElementById('edit_compania_id').value = btn.dataset.compania;
        document.getElementById('edit_medio_id').value    = btn.dataset.medio;
        document.getElementById('edit_fecha').value       = btn.dataset.fecha;
        document.getElementById('edit_mensaje').value     = btn.dataset.mensaje;
    });
</script>
@endpush

@endsection