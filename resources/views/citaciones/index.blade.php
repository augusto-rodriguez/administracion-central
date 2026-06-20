@extends('layouts.app')
@section('title', 'Citaciones')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-megaphone me-2"></i>Citaciones
    </h4>

    <!-- BOTÓN ABRIR MODAL -->
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalCitacion">
        <i class="bi bi-plus-lg me-1"></i>Nueva Citación
    </button>
</div>

{{-- FILTROS --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('citaciones.index') }}">
            <div class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label class="form-label fw-bold">Compañía</label>
                    <select name="compania_id" class="form-select">
                        <option value="">Todas</option>
                        <option value="cuerpo" {{ request('compania_id') === 'cuerpo' ? 'selected' : '' }}>
                            🚒 Todo el Cuerpo de Bomberos
                        </option>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}"
                                    {{ request('compania_id') == $compania->id ? 'selected' : '' }}>
                                {{ $compania->numero }} - {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Medio</label>
                    <select name="medio_recepcion_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($medios as $medio)
                            <option value="{{ $medio->id }}"
                                    {{ request('medio_recepcion_id') == $medio->id ? 'selected' : '' }}>
                                {{ $medio->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-danger flex-grow-1">
                        <i class="bi bi-search me-1"></i>Filtrar
                    </button>

                    @if(request()->hasAny(['compania_id', 'medio_recepcion_id']))
                        <a href="{{ route('citaciones.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>

            </div>
        </form>
    </div>
</div>

{{-- TABLA --}}
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Compañía</th>
                    <th>Medio</th>
                    <th>Mensaje</th>
                    <th></th>  {{-- columna acciones --}}
                </tr>
            </thead>
            <tbody>
                @forelse($citaciones as $citacion)
                <tr>
                    <td>
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

                    <td>
                        @if($citacion->compania)
                            {{ $citacion->compania->numero }} - {{ $citacion->compania->nombre }}
                        @else
                            <span class="badge bg-danger">
                                <i class="bi bi-building me-1"></i>Todo el Cuerpo
                            </span>
                        @endif
                    </td>

                    <td>
                        <span class="badge bg-primary">
                            {{ $citacion->medioRecepcion->nombre }}
                        </span>
                    </td>

                    <td style="max-width: 400px;">
                        {{ Str::limit($citacion->mensaje, 120) }}
                    </td>

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
                    <td colspan="4" class="text-center text-muted py-4">
                        No hay citaciones registradas
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL CREAR --}}
<div class="modal fade" id="modalCitacion" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('citaciones.store') }}" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Nueva Citación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label fw-bold">Compañía</label>
                    <select name="compania_id" class="form-select">
                        {{-- value vacío → NULL → todo el cuerpo --}}
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
                    <label class="form-label fw-bold">Medio de recepción</label>
                    <select name="medio_recepcion_id" class="form-select" required>
                        @foreach($medios as $medio)
                            <option value="{{ $medio->id }}">
                                {{ $medio->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Fecha</label>
                    <input type="datetime-local" name="fecha_citacion" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mensaje</label>
                    <textarea name="mensaje" rows="4" class="form-control" required></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button class="btn btn-danger">
                    <i class="bi bi-save me-1"></i>Guardar
                </button>
            </div>

        </form>
    </div>
</div>

{{-- MODAL EDITAR --}}
<div class="modal fade" id="modalEditarCitacion" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="formEditarCitacion" class="modal-content">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h5 class="modal-title">Editar Citación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label fw-bold">Compañía</label>
                    <select name="compania_id" id="edit_compania_id" class="form-select">
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
                    <label class="form-label fw-bold">Medio de recepción</label>
                    <select name="medio_recepcion_id" id="edit_medio_id" class="form-select" required>
                        @foreach($medios as $medio)
                            <option value="{{ $medio->id }}">{{ $medio->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Fecha</label>
                    <input type="datetime-local" name="fecha_citacion" id="edit_fecha" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mensaje</label>
                    <textarea name="mensaje" id="edit_mensaje" rows="4" class="form-control" required></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button class="btn btn-danger">
                    <i class="bi bi-save me-1"></i>Guardar cambios
                </button>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('modalEditarCitacion').addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;

        // Apunta el action del form al ID correcto
        const id = btn.dataset.id;
        document.getElementById('formEditarCitacion').action = `/citaciones/${id}`;

        // Rellena los campos
        document.getElementById('edit_compania_id').value = btn.dataset.compania;
        document.getElementById('edit_medio_id').value    = btn.dataset.medio;
        document.getElementById('edit_fecha').value       = btn.dataset.fecha;
        document.getElementById('edit_mensaje').value     = btn.dataset.mensaje;
    });
</script>
@endpush

@endsection