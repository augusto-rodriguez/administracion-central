@extends('layouts.app')
@section('title', 'Consultas')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-search me-2"></i>Consultas</h4>
</div>

<div class="row g-4">

    {{-- Buscar por unidades --}}
    <div class="col-md-6">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-truck-front me-2"></i>¿Quién maneja estas unidades?
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('consultas.index') }}">
                    <input type="hidden" name="tipo" value="unidades">
                    <label class="form-label fw-bold">Selecciona una o más unidades:</label>
                    <div class="border rounded p-2 mb-3" style="max-height:200px; overflow-y:auto">
                        @foreach($unidades as $unidad)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="unidades_ids[]"
                                   value="{{ $unidad->id }}" id="u_{{ $unidad->id }}"
                                   {{ in_array($unidad->id, request('unidades_ids', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="u_{{ $unidad->id }}">
                                <strong>{{ $unidad->nombre }}</strong>
                                <span class="text-muted small">— {{ $unidad->compania->nombre }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>Buscar Voluntarios
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Buscar por voluntario --}}
    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-header bg-success text-white fw-bold">
                <i class="bi bi-person-badge me-2"></i>¿Qué unidades maneja este voluntario?
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('consultas.index') }}">
                    <input type="hidden" name="tipo" value="voluntario">
                    <label class="form-label fw-bold">Selecciona un voluntario:</label>
                    <select name="voluntario_id" class="form-select mb-3">
                        <option value="">Seleccionar...</option>
                        @foreach($voluntarios as $voluntario)
                            <option value="{{ $voluntario->id }}"
                                {{ request('voluntario_id') == $voluntario->id ? 'selected' : '' }}>
                                {{ $voluntario->nombre }} — {{ $voluntario->compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-search me-1"></i>Buscar Unidades
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

{{-- Resultados --}}
@if(request()->hasAny(['unidades_ids', 'voluntario_id']) && $resultados->isNotEmpty())
<div class="card mt-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-list-check me-2"></i>Resultados
        <span class="badge bg-secondary ms-1">{{ $resultados->count() }}</span>
    </div>
    <div class="card-body p-0">

        @if($tipo === 'unidades')
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Voluntario</th>
                    <th>Compañía</th>
                    <th>Roles</th>
                    <th>Unidades autorizadas</th>
                    <th>Estado actual</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultados as $voluntario)
                <tr>
                    <td class="fw-bold">
                        <a href="{{ route('voluntarios.show', $voluntario) }}" class="text-decoration-none">
                            {{ $voluntario->nombre }}
                        </a>
                    </td>
                    <td>{{ $voluntario->compania->nombre }}</td>
                    <td>
                        @foreach($voluntario->roles->where('activo', true) as $rol)
                            @if($rol->rol === 'maquinista')
                                <span class="badge bg-danger">Maquinista</span>
                            @elseif($rol->rol === 'oficial')
                                <span class="badge bg-primary">Oficial</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($rol->rol) }}</span>
                            @endif
                        @endforeach
                    </td>
                    <td>
                        @foreach($voluntario->unidades_match as $unidad)
                            <span class="badge bg-primary me-1">{{ $unidad->nombre }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if($voluntario->turnoActivo)
                            <span class="badge bg-success">
                                <i class="bi bi-circle-fill me-1" style="font-size:8px"></i>En Servicio
                            </span>
                        @else
                            <span class="badge bg-secondary">Fuera de servicio</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @else
        @foreach($resultados as $voluntario)
        <div class="p-3 border-bottom">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div>
                    <div class="fw-bold fs-5">{{ $voluntario->nombre }}</div>
                    <div class="text-muted">
                        {{ $voluntario->compania->nombre }}
                        — {{ $voluntario->roles_lista }}
                    </div>
                </div>
                <div class="ms-auto">
                    @if($voluntario->turnoActivo)
                        <span class="badge bg-success fs-6">
                            <i class="bi bi-circle-fill me-1" style="font-size:8px"></i>En Servicio
                        </span>
                    @else
                        <span class="badge bg-secondary fs-6">Fuera de servicio</span>
                    @endif
                </div>
            </div>
            <div class="row g-2">
                @forelse($voluntario->unidadesAutorizadas as $unidad)
                <div class="col-md-3">
                    <div class="border rounded p-2 d-flex align-items-center gap-2">
                        <i class="bi bi-truck-front text-primary"></i>
                        <div>
                            <div class="fw-bold">{{ $unidad->nombre }}</div>
                            <div class="text-muted small">{{ $unidad->compania->nombre }}</div>
                            <span class="badge bg-info text-dark" style="font-size:10px">{{ $unidad->tipo }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-muted">Sin unidades autorizadas</div>
                @endforelse
            </div>
        </div>
        @endforeach
        @endif

    </div>
</div>

@elseif(request()->hasAny(['unidades_ids', 'voluntario_id']) && $resultados->isEmpty())
<div class="alert alert-info mt-4">
    <i class="bi bi-info-circle me-2"></i>No se encontraron resultados para la búsqueda.
</div>
@endif

@endsection