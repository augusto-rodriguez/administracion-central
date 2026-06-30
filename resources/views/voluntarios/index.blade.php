@extends('layouts.app')
@section('title', 'Voluntarios')
@section('content')

@php $esCapitan = auth()->user()->esCapitanCia(); @endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-people me-2"></i>Voluntarios
        @if($esCapitan)
            <span class="text-muted fs-6 fw-normal ms-2">
                — {{ auth()->user()->voluntario?->compania->nombre }}
            </span>
        @endif
    </h4>
    @if(!$esCapitan)
        <a href="{{ route('voluntarios.create') }}" class="btn btn-danger">
            <i class="bi bi-plus-lg me-1"></i>Nuevo Voluntario
        </a>
    @endif
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('voluntarios.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Buscar por nombre</label>
                    <input type="text"
                           id="buscadorNombre"
                           class="form-control"
                           placeholder="Escribe un nombre...">
                </div>

                {{-- El filtro de compañía solo aplica a admin/comandante --}}
                @if(!$esCapitan)
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Compañía</label>
                        <select name="compania_id" class="form-select">
                            <option value="">Todas</option>
                            @foreach($companias as $compania)
                                <option value="{{ $compania->id }}"
                                    {{ request('compania_id') == $compania->id ? 'selected' : '' }}>
                                    {{ $compania->numero }} - {{ $compania->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-2">
                    <label class="form-label fw-bold">Rol</label>
                    <select name="rol" class="form-select">
                        <option value="">Todos</option>
                        <option value="maquinista" {{ request('rol') == 'maquinista' ? 'selected' : '' }}>
                            Maquinista
                        </option>
                        <option value="oficial" {{ request('rol') == 'oficial' ? 'selected' : '' }}>
                            Oficial
                        </option>
                        <option value="honorario" {{ request('rol') == 'honorario' ? 'selected' : '' }}>
                            Honorario
                        </option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-danger flex-grow-1">
                        <i class="bi bi-search me-1"></i>Filtrar
                    </button>

                    @if(request()->hasAny(['compania_id', 'rol']))
                        <a href="{{ route('voluntarios.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </div>
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
                    <th>Roles</th>
                    <th>Cargo</th>
                    <th>Teléfono</th>
                    <th>Fecha Ingreso</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>

            <tbody id="tablaVoluntarios">
                @forelse($voluntarios as $voluntario)
                    <tr data-nombre="{{ strtolower($voluntario->nombre) }}">
                        <td class="fw-bold">
                            <a href="{{ route('voluntarios.show', $voluntario) }}"
                               class="text-decoration-none text-dark">
                                {{ $voluntario->nombre }}
                            </a>
                        </td>

                        <td>{{ $voluntario->rut ?? '—' }}</td>

                        <td>{{ $voluntario->compania->nombre }}</td>

                        <td>
                            @foreach($voluntario->roles->where('activo', true) as $rol)

                                @if($rol->rol === 'maquinista')
                                    <span class="badge bg-danger">Maquinista</span>

                                @elseif($rol->rol === 'oficial')
                                    <span class="badge bg-primary">Oficial</span>

                                @elseif($rol->rol === 'honorario')
                                    <span class="badge bg-info">Honorario</span>

                                @else
                                    <span class="badge bg-secondary">
                                        {{ ucfirst($rol->rol) }}
                                    </span>
                                @endif

                            @endforeach

                            @if($voluntario->roles->where('activo', true)->isEmpty())
                                <span class="text-muted small">—</span>
                            @endif
                        </td>

                        <td>
                            @php
                                $cargoActivo = $voluntario->cargosActivos->first();
                            @endphp

                            @if($cargoActivo)
                                @if($cargoActivo->cargo->tipo === 'general')
                                    <span class="badge bg-dark">
                                        {{ $cargoActivo->cargo->nombre }}
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        {{ $cargoActivo->cargo->nombre }}
                                    </span>
                                @endif
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>

                        <td>{{ $voluntario->telefono ?? '—' }}</td>
                        <td>
                            {{ $voluntario->fecha_ingreso ? \Carbon\Carbon::parse($voluntario->fecha_ingreso)->format('d/m/Y') : '—' }}
                        </td>

                        <td>
                            @if($voluntario->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('voluntarios.show', $voluntario) }}"
                               class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i>
                            </a>

                            <a href="{{ route('voluntarios.edit', $voluntario) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No hay voluntarios registrados
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('buscadorNombre').addEventListener('input', function () {

    const busqueda = this.value.toLowerCase().trim();
    const filas = document.querySelectorAll('#tablaVoluntarios tr[data-nombre]');

    filas.forEach(fila => {
        const nombre = fila.dataset.nombre;
        fila.style.display = nombre.includes(busqueda) ? '' : 'none';
    });

    const sinResultados = [...filas].every(f => f.style.display === 'none');
    let msg = document.getElementById('sinResultadosBusqueda');

    if (sinResultados && busqueda !== '') {

        if (!msg) {
            const tr = document.createElement('tr');
            tr.id = 'sinResultadosBusqueda';
            tr.innerHTML = `
                <td colspan="8" class="text-center text-muted py-4">
                    <i class="bi bi-search me-2"></i>
                    No se encontraron voluntarios con ese nombre.
                </td>
            `;
            document.getElementById('tablaVoluntarios').appendChild(tr);
        }

    } else if (msg) {
        msg.remove();
    }

});
</script>
@endpush

@endsection