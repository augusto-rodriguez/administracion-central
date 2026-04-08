@extends('layouts.app')

@section('title', 'Detalle Maquinista')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>{{ $maquinista->nombre }}</h4>
    <a href="{{ route('maquinistas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="row g-4">

    {{-- Info del maquinista --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-person me-2"></i>Información
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr><th>RUT</th><td>{{ $maquinista->rut }}</td></tr>
                    <tr><th>Compañía</th><td>{{ $maquinista->compania->nombre }}</td></tr>
                    <tr><th>Cargo</th><td>{{ $maquinista->cargo ?? '—' }}</td></tr>
                    <tr><th>Teléfono</th><td>{{ $maquinista->telefono ?? '—' }}</td></tr>
                    <tr>
                        <th>Estado</th>
                        <td>
                            @if($maquinista->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Unidades autorizadas --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-truck-front me-2"></i>Unidades Autorizadas
            </div>
            <div class="card-body">
                @if($maquinista->unidadesAutorizadas->isEmpty())
                    <p class="text-muted">Sin unidades autorizadas aún.</p>
                @else
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Unidad</th>
                                <th>Compañía</th>
                                <th>Tipo</th>
                                <th>Autorizado por</th>
                                <th>Fecha</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($maquinista->unidadesAutorizadas as $unidad)
                            <tr>
                                <td class="fw-bold">{{ $unidad->nombre }}</td>
                                <td>{{ $unidad->compania->nombre }}</td>
                                <td><span class="badge bg-info text-dark">{{ $unidad->tipo }}</span></td>
                                <td>{{ $unidad->pivot->autorizado_por ?? '—' }}</td>
                                <td>{{ $unidad->pivot->fecha_autorizacion ?? '—' }}</td>
                                <td>
                                    <form action="{{ route('maquinistas.revocar-unidad', [$maquinista, $unidad]) }}"
                                          method="POST" onsubmit="return confirm('¿Revocar autorización?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                {{-- Formulario autorizar nueva unidad --}}
                <hr>
                <p class="fw-bold mb-2"><i class="bi bi-plus-circle me-1"></i>Autorizar nueva unidad</p>
                <form action="{{ route('maquinistas.autorizar-unidad', $maquinista) }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-4">
                            <select name="unidad_id" class="form-select form-select-sm" required>
                                <option value="">Seleccionar unidad...</option>
                                @foreach($unidadesDisponibles as $unidad)
                                    <option value="{{ $unidad->id }}">
                                        {{ $unidad->nombre }} — {{ $unidad->compania->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="autorizado_por" class="form-control form-control-sm"
                                   placeholder="Autorizado por">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="fecha_autorizacion" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-danger w-100">
                                <i class="bi bi-plus-lg"></i> Autorizar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Historial de turnos --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-clock-history me-2"></i>Historial de Turnos
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Tiempo</th>
                            <th>Unidades</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maquinista->turnos->sortByDesc('entrada_at') as $turno)
                        <tr>
                            <td>{{ $turno->entrada_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $turno->salida_at ? $turno->salida_at->format('d/m/Y H:i') : '—' }}</td>
                            <td>
                                @if($turno->salida_at)
                                    <span class="badge bg-secondary">{{ $turno->tiempo_formateado }}</span>
                                @else
                                    <span class="badge bg-success">En servicio</span>
                                @endif
                            </td>
                            <td>
                                @foreach($turno->unidades as $unidad)
                                    <span class="badge bg-primary me-1">{{ $unidad->nombre }}</span>
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