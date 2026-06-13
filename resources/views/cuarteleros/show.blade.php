@extends('layouts.app')
@section('title', 'Detalle Cuartelero')
@section('content')

@php $puedeGestionar = auth()->user()->esComandante() || auth()->user()->esAdmin(); @endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-person-badge me-2"></i>{{ $cuartelero->nombre }}
        @if($cuartelero->estaActivo())
            <span class="badge bg-success ms-2">Activo</span>
        @else
            <span class="badge bg-secondary ms-2">Histórico</span>
        @endif
    </h4>
    <div>
        @if($puedeGestionar && $cuartelero->estaActivo())
        <a href="{{ route('cuarteleros.edit', $cuartelero) }}" class="btn btn-outline-primary me-2">
            <i class="bi bi-pencil me-1"></i>Editar
        </a>
        @endif
        <a href="{{ route('cuarteleros.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row g-4">

    {{-- Info personal --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header fw-bold">Información Personal</div>
            <div class="card-body">
                <p class="mb-1"><strong>Compañía:</strong> {{ $cuartelero->compania->nombre }}</p>
                <p class="mb-1"><strong>RUT:</strong> {{ $cuartelero->rut ?? '—' }}</p>
                <p class="mb-1"><strong>Teléfono:</strong> {{ $cuartelero->telefono ?? '—' }}</p>
                <p class="mb-1"><strong>Período:</strong> {{ $cuartelero->periodoFormateado() }}</p>
                @if($cuartelero->motivo_fin)
                <p class="mb-1"><strong>Motivo salida:</strong> {{ $cuartelero->motivo_fin }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Cerrar período --}}
    @if($puedeGestionar && $cuartelero->estaActivo())
    <div class="col-md-4">
        <div class="card h-100 border-warning">
            <div class="card-header fw-bold text-warning">
                <i class="bi bi-door-open me-1"></i>Dar de baja del cargo
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Cierra el período de este cuartelero. El registro quedará en el historial
                    y podrás registrar al reemplazante como nuevo cuartelero.
                </p>
                <form action="{{ route('cuarteleros.cerrar', $cuartelero) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw-bold">Fecha de salida <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_fin" class="form-control"
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivo (opcional)</label>
                        <input type="text" name="motivo_fin" class="form-control"
                               placeholder="Ej: renuncia, traslado, término contrato...">
                    </div>
                    <button type="submit" class="btn btn-warning w-100"
                            onclick="return confirm('¿Confirma dar de baja a {{ $cuartelero->nombre }}?')">
                        <i class="bi bi-check-lg me-1"></i>Confirmar baja
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Unidades autorizadas --}}
    <div class="col-md-{{ $puedeGestionar && $cuartelero->estaActivo() ? '4' : '8' }}">
        <div class="card h-100">
            <div class="card-header fw-bold">Unidades Autorizadas</div>
            <div class="card-body">
                @if($puedeGestionar && $cuartelero->estaActivo())
                <form action="{{ route('cuarteleros.autorizar-unidad', $cuartelero) }}" method="POST" class="d-flex gap-2 mb-3">
                    @csrf
                    <select name="unidad_id" class="form-select form-select-sm">
                        <option value="">Agregar unidad...</option>
                        @foreach($unidades as $unidad)
                            @if(!$cuartelero->unidadesAutorizadas->contains($unidad))
                            <option value="{{ $unidad->id }}">
                                {{ $unidad->nombre }} — {{ $unidad->compania->nombre }}
                            </option>
                            @endif
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </form>
                @endif

                @forelse($cuartelero->unidadesAutorizadas as $unidad)
                <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                    <span>
                        <span class="badge bg-danger me-1">{{ $unidad->nombre }}</span>
                        {{ $unidad->compania->nombre }}
                    </span>
                    @if($puedeGestionar && $cuartelero->estaActivo())
                    <form action="{{ route('cuarteleros.revocar-unidad', $cuartelero) }}"
                          method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <input type="hidden" name="unidad_id" value="{{ $unidad->id }}">
                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('¿Revocar autorización?')">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </form>
                    @endif
                </div>
                @empty
                <p class="text-muted">Sin unidades autorizadas.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Historial de cuarteleros anteriores de la misma compañía --}}
    @if($historial->count())
    <div class="col-12">
        <div class="card">
            <div class="card-header fw-bold text-muted">
                <i class="bi bi-clock-history me-1"></i>
                Cuarteleros anteriores — {{ $cuartelero->compania->nombre }}
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 text-muted">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>RUT</th>
                            <th>Período</th>
                            <th>Motivo salida</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historial as $anterior)
                        <tr>
                            <td>{{ $anterior->nombre }}</td>
                            <td>{{ $anterior->rut ?? '—' }}</td>
                            <td><span class="small">{{ $anterior->periodoFormateado() }}</span></td>
                            <td>{{ $anterior->motivo_fin ?? '—' }}</td>
                            <td>
                                <a href="{{ route('cuarteleros.show', $anterior) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Historial de turnos --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header fw-bold">Historial de Turnos</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Duración</th>
                            <th>Unidades</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cuartelero->turnos->sortByDesc('entrada_at') as $turno)
                        <tr>
                            <td>{{ $turno->entrada_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($turno->salida_at)
                                    {{ $turno->salida_at->format('d/m/Y H:i') }}
                                @else
                                    <span class="badge bg-success">En turno</span>
                                @endif
                            </td>
                            <td>{{ $turno->tiempo_formateado }}</td>
                            <td>
                                @foreach($turno->unidades as $u)
                                    <span class="badge bg-secondary">{{ $u->nombre }}</span>
                                @endforeach
                            </td>
                            <td>{{ $turno->observaciones ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Sin turnos registrados</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection