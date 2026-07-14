@extends('layouts.app')
@section('title', 'Voluntarios')
@section('content')

@php $esCapitan = auth()->user()->esCapitanCia(); @endphp

{{-- Encabezado --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <h4 class="mb-0">
        <i class="bi bi-people me-2"></i>Voluntarios
        @if($esCapitan)
            <span class="text-muted fs-6 fw-normal ms-2">
                — {{ auth()->user()->voluntario?->compania->nombre }}
            </span>
        @endif
    </h4>
    @if(!$esCapitan)
        <a href="{{ route('voluntarios.create') }}" class="btn btn-danger btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Nuevo Voluntario
        </a>
    @endif
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('voluntarios.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label fw-bold mb-1 small">Buscar por nombre</label>
                    <input type="text"
                           id="buscadorNombre"
                           class="form-control form-control-sm"
                           placeholder="Escribe un nombre...">
                </div>

                @if(!$esCapitan)
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-bold mb-1 small">Compañía</label>
                        <select name="compania_id" class="form-select form-select-sm">
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

                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold mb-1 small">Rol</label>
                    <select name="rol" class="form-select form-select-sm">
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

                <div class="col-12 col-md-2">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-danger btn-sm flex-grow-1">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                        @if(request()->hasAny(['compania_id', 'rol']))
                            <a href="{{ route('voluntarios.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">

        {{-- Tabla desktop --}}
        <div class="table-responsive">
            <table class="table table-hover mb-0 d-none d-md-table">
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
                            <td class="fw-bold text-nowrap">
                                <a href="{{ route('voluntarios.show', $voluntario) }}"
                                   class="text-decoration-none text-dark">
                                    {{ $voluntario->nombre }}
                                </a>
                            </td>

                            <td class="text-nowrap">{{ $voluntario->rut ?? '—' }}</td>

                            <td class="text-nowrap">{{ $voluntario->compania->nombre }}</td>

                            <td>
                                @foreach($voluntario->roles->where('activo', true) as $rol)
                                    @if($rol->rol === 'maquinista')
                                        <span class="badge bg-danger">Maquinista</span>
                                    @elseif($rol->rol === 'oficial')
                                        <span class="badge bg-primary">Oficial</span>
                                    @elseif($rol->rol === 'honorario')
                                        <span class="badge bg-info">Honorario</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($rol->rol) }}</span>
                                    @endif
                                @endforeach
                                @if($voluntario->roles->where('activo', true)->isEmpty())
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            <td>
                                @php $cargoActivo = $voluntario->cargosActivos->first(); @endphp
                                @if($cargoActivo)
                                    @if($cargoActivo->cargo->tipo === 'general')
                                        <span class="badge bg-dark">{{ $cargoActivo->cargo->nombre }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ $cargoActivo->cargo->nombre }}</span>
                                    @endif
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            <td class="text-nowrap">{{ $voluntario->telefono ?? '—' }}</td>
                            <td class="text-nowrap">
                                {{ $voluntario->fecha_ingreso ? \Carbon\Carbon::parse($voluntario->fecha_ingreso)->format('d/m/Y') : '—' }}
                            </td>

                            <td>
                                @if($voluntario->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>

                            <td class="text-nowrap">
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
                            <td colspan="9" class="text-center text-muted py-4">
                                No hay voluntarios registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Vista cards móvil --}}
        <div class="d-md-none" id="listaVoluntariosMobile">
            @forelse($voluntarios as $voluntario)
                <div class="border-bottom px-3 py-3 voluntario-card-mobile"
                     data-nombre="{{ strtolower($voluntario->nombre) }}">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <a href="{{ route('voluntarios.show', $voluntario) }}"
                               class="text-decoration-none text-dark fw-bold">
                                {{ $voluntario->nombre }}
                            </a>
                            <div class="text-muted small">{{ $voluntario->compania->nombre }}</div>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            @if($voluntario->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-1 mb-2">
                        @foreach($voluntario->roles->where('activo', true) as $rol)
                            @if($rol->rol === 'maquinista')
                                <span class="badge bg-danger">Maquinista</span>
                            @elseif($rol->rol === 'oficial')
                                <span class="badge bg-primary">Oficial</span>
                            @elseif($rol->rol === 'honorario')
                                <span class="badge bg-info">Honorario</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($rol->rol) }}</span>
                            @endif
                        @endforeach

                        @php $cargoActivo = $voluntario->cargosActivos->first(); @endphp
                        @if($cargoActivo)
                            @if($cargoActivo->cargo->tipo === 'general')
                                <span class="badge bg-dark">{{ $cargoActivo->cargo->nombre }}</span>
                            @else
                                <span class="badge bg-warning text-dark">{{ $cargoActivo->cargo->nombre }}</span>
                            @endif
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-wrap gap-2 small text-muted">
                            @if($voluntario->rut)
                                <span><i class="bi bi-person-vcard me-1"></i>{{ $voluntario->rut }}</span>
                            @endif
                            @if($voluntario->telefono)
                                <span><i class="bi bi-telephone me-1"></i>{{ $voluntario->telefono }}</span>
                            @endif
                            @if($voluntario->fecha_ingreso)
                                <span><i class="bi bi-calendar me-1"></i>{{ \Carbon\Carbon::parse($voluntario->fecha_ingreso)->format('d/m/Y') }}</span>
                            @endif
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0 ms-2">
                            <a href="{{ route('voluntarios.show', $voluntario) }}"
                               class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('voluntarios.edit', $voluntario) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    No hay voluntarios registrados
                </div>
            @endforelse
        </div>

    </div>
</div>

@push('scripts')
<script>
document.getElementById('buscadorNombre').addEventListener('input', function () {
    const busqueda = this.value.toLowerCase().trim();

    // Filtrar tabla desktop
    const filas = document.querySelectorAll('#tablaVoluntarios tr[data-nombre]');
    filas.forEach(fila => {
        fila.style.display = fila.dataset.nombre.includes(busqueda) ? '' : 'none';
    });

    // Filtrar cards móvil
    const cards = document.querySelectorAll('.voluntario-card-mobile');
    cards.forEach(card => {
        card.style.display = card.dataset.nombre.includes(busqueda) ? '' : 'none';
    });

    // Mensaje sin resultados — desktop
    const sinResultadosDesktop = [...filas].every(f => f.style.display === 'none');
    let msgDesktop = document.getElementById('sinResultadosBusqueda');
    if (sinResultadosDesktop && busqueda !== '') {
        if (!msgDesktop) {
            const tr = document.createElement('tr');
            tr.id = 'sinResultadosBusqueda';
            tr.innerHTML = `
                <td colspan="9" class="text-center text-muted py-4">
                    <i class="bi bi-search me-2"></i>
                    No se encontraron voluntarios con ese nombre.
                </td>
            `;
            document.getElementById('tablaVoluntarios').appendChild(tr);
        }
    } else if (msgDesktop) {
        msgDesktop.remove();
    }

    // Mensaje sin resultados — móvil
    const sinResultadosMobile = [...cards].every(c => c.style.display === 'none');
    let msgMobile = document.getElementById('sinResultadosBusquedaMobile');
    if (sinResultadosMobile && busqueda !== '') {
        if (!msgMobile) {
            const div = document.createElement('div');
            div.id = 'sinResultadosBusquedaMobile';
            div.className = 'text-center text-muted py-4';
            div.innerHTML = '<i class="bi bi-search me-2"></i>No se encontraron voluntarios con ese nombre.';
            document.getElementById('listaVoluntariosMobile').appendChild(div);
        }
    } else if (msgMobile) {
        msgMobile.remove();
    }
});
</script>
@endpush

@endsection