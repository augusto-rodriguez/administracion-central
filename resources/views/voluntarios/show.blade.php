@extends('layouts.app')
@section('title', 'Detalle Voluntario')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>{{ $voluntario->nombre }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('voluntarios.edit', $voluntario) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Editar
        </a>
        <a href="{{ route('voluntarios.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Info personal --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-person me-2"></i>Información Personal
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr><th>Nombre</th><td>{{ $voluntario->nombre }}</td></tr>
                    <tr><th>RUT</th><td>{{ $voluntario->rut ?? '—' }}</td></tr>
                    <tr><th>Compañía</th><td>{{ $voluntario->compania->nombre }}</td></tr>
                    <tr><th>Teléfono</th><td>{{ $voluntario->telefono ?? '—' }}</td></tr>
                    <tr><th>Email</th><td>{{ $voluntario->email ?? '—' }}</td></tr>
                    <tr>
                        <th>Estado</th>
                        <td>
                            @if($voluntario->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Roles</th>
                        <td>
                            @foreach($voluntario->roles->where('activo', true) as $rol)
                                @if($rol->rol === 'maquinista')
                                    <span class="badge bg-danger">Maquinista</span>
                                @elseif($rol->rol === 'oficial')
                                    <span class="badge bg-primary">Oficial</span>
                                @elseif($rol->rol === 'voluntario')
                                    <span class="badge bg-success">Voluntario</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($rol->rol) }}</span>
                                @endif
                            @endforeach
                        </td>
                    </tr>
                </table>

                {{-- Panel autorización salidas — solo si es oficial y es admin/comandante --}}
                @php
                    $rolOficial = $voluntario->roles->where('activo', true)->firstWhere('rol', 'oficial');
                @endphp
                @if($rolOficial && (auth()->user()->esAdmin() || auth()->user()->esComandante()))
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold small mb-1">
                            <i class="bi bi-shield me-1"></i>Autorización de Salidas
                        </div>
                        @if($rolOficial->puede_autorizar_salidas)
                            <span class="badge bg-success">
                                <i class="bi bi-shield-check me-1"></i>Puede autorizar salidas
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                <i class="bi bi-shield-x me-1"></i>Sin autorización de salidas
                            </span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('voluntarios.toggle-autorizante', $voluntario) }}">
                        @csrf
                        <button type="submit"
                                class="btn btn-sm {{ $rolOficial->puede_autorizar_salidas ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                onclick="return confirm('¿Confirmar cambio de autorización?')">
                            <i class="bi bi-shield-{{ $rolOficial->puede_autorizar_salidas ? 'x' : 'check' }} me-1"></i>
                            {{ $rolOficial->puede_autorizar_salidas ? 'Quitar' : 'Autorizar' }}
                        </button>
                    </form>
                </div>
                @endif

            </div>
        </div>
    </div>

    {{-- Unidades autorizadas --}}
    @if($voluntario->esMaquinista())
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-truck me-2"></i>Unidades Autorizadas</span>
            </div>
            <div class="card-body">
                {{-- Autorizar nueva unidad --}}
                <form action="{{ route('voluntarios.autorizar-unidad', $voluntario) }}" method="POST" class="mb-3">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Unidad</label>
                            <select name="unidad_id" class="form-select form-select-sm" required>
                                <option value="">Seleccionar...</option>
                                @foreach($unidades as $unidad)
                                    <option value="{{ $unidad->id }}">
                                        {{ $unidad->nombre }} — {{ $unidad->compania->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <!-- <div class="col-md-3">
                            <label class="form-label fw-bold small">Autorizado por</label>
                            <input type="text" name="autorizado_por" class="form-control form-control-sm"
                                   placeholder="Nombre oficial">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Fecha</label>
                            <input type="date" name="fecha_autorizacion" class="form-control form-control-sm">
                        </div> -->
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="bi bi-plus-lg"></i> Autorizar
                            </button>
                        </div>
                    </div>
                </form>

                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Unidad</th>
                            <th>Compañía</th>
                            <th>Autorizado por</th>
                            <th>Fecha</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($voluntario->unidadesAutorizadas as $unidad)
                        <tr>
                            <td><span class="badge bg-primary">{{ $unidad->nombre }}</span></td>
                            <td>{{ $unidad->compania->nombre }}</td>
                            <td>{{ $unidad->pivot->autorizado_por ?? '—' }}</td>
                            <td>{{ $unidad->pivot->fecha_autorizacion ?? '—' }}</td>
                            <td>
                                <form action="{{ route('voluntarios.revocar-unidad', $voluntario) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Revocar autorización?')">
                                    @csrf @method('DELETE')
                                    <input type="hidden" name="unidad_id" value="{{ $unidad->id }}">
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-muted text-center py-2">Sin unidades autorizadas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Historial de turnos --}}
@if($voluntario->esMaquinista() && $voluntario->turnos->isNotEmpty())
<div class="card mt-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-clock-history me-2"></i>Historial de Turnos
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Unidades</th>
                    <th>Tiempo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($voluntario->turnos->sortByDesc('entrada_at')->take(10) as $turno)
                <tr>
                    <td>{{ $turno->entrada_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $turno->salida_at ? $turno->salida_at->format('d/m/Y H:i') : '—' }}</td>
                    <td>
                        @foreach($turno->unidades as $unidad)
                            <span class="badge bg-primary me-1">{{ $unidad->nombre }}</span>
                        @endforeach
                    </td>
                    <td><span class="badge bg-secondary">{{ $turno->tiempo_formateado }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection