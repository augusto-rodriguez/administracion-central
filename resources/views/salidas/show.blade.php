@extends('layouts.app')
@section('title', 'Detalle Salida')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-arrow-up-right-circle me-2"></i>Detalle de Salida</h4>
    <a href="{{ route('salidas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

{{-- Badge de sobresalida --}}
@if($salida->esSobresalida())
<div class="alert alert-warning py-2 mb-3">
    <i class="bi bi-arrow-right-circle me-2"></i>
    Esta es una <strong>sobresalida</strong> encadenada a la salida original del
    {{ $salida->salidaPadre->salida_at->format('d/m/Y H:i') }}
    ({{ $salida->salidaPadre->claveSalida->codigo }} — {{ $salida->salidaPadre->direccion }}).
    <a href="{{ route('salidas.show', $salida->salidaPadre) }}" class="ms-2">Ver raíz</a>
</div>
@endif

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-info-circle me-2"></i>Información de la Salida
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th>Unidad</th>
                        <td><strong>{{ $salida->unidad->nombre }}</strong> — {{ $salida->unidad->compania->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Clave</th>
                        <td>
                            @if($salida->claveSalida->tipo === 'emergencia')
                                <span class="badge bg-danger">{{ $salida->claveSalida->codigo }}</span>
                            @else
                                <span class="badge bg-primary">{{ $salida->claveSalida->codigo }}</span>
                            @endif
                            {{ $salida->claveSalida->descripcion }}
                        </td>
                    </tr>
                    <tr><th>Dirección</th><td>{{ $salida->direccion }}</td></tr>
                    <tr><th>Conductor</th><td>{{ $salida->conductor_nombre }}</td></tr>
                    <tr><th>Oficial</th><td>{{ $salida->oficial?->nombre ?? '—' }}</td></tr>
                    <tr><th>Al mando</th><td>{{ $salida->alMando?->nombre ?? '—' }}</td></tr>
                    <tr><th>Personal</th><td>{{ $salida->cantidad_personal ?? '—' }}</td></tr>
                    <tr><th>Observaciones</th><td>{{ $salida->observaciones ?? '—' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-speedometer2 me-2"></i>Tiempos y Kilometraje
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr><th>Salida</th><td>{{ $salida->salida_at->format('d/m/Y H:i') }}</td></tr>
                    <tr>
                        <th>Llegada</th>
                        <td>{{ $salida->llegada_at ? $salida->llegada_at->format('d/m/Y H:i') : '—' }}</td>
                    </tr>
                    <tr>
                        <th>Tiempo total</th>
                        <td><span class="badge bg-secondary fs-6">{{ $salida->tiempo_formateado }}</span></td>
                    </tr>
                    <tr><th>Km salida</th><td>{{ formatKm($salida->km_salida, 1) }} km</td></tr>
                    <tr>
                        <th>Km llegada</th>
                        <td>{{ $salida->km_llegada ? formatKm($salida->km_llegada, 1) . ' km' : '—' }}</td>
                    </tr>
                    <tr>
                        <th>Km recorridos</th>
                        <td>
                            @if($salida->km_recorrido !== null)
                                <span class="badge bg-info text-dark fs-6">
                                    {{ formatKm($salida->km_recorrido, 1) }} km
                                </span>
                                @if($salida->esSalidaRaiz() && $salida->sobresalidas->count() > 0)
                                    <div class="text-muted small mt-1">
                                        <i class="bi bi-info-circle me-1"></i>Total de la cadena
                                    </div>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                </table>

                @if(!$salida->llegada_at)
                <hr>
                <form action="{{ route('salidas.llegada', $salida) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw-bold">Km Llegada <span class="text-danger">*</span></label>
                        <input type="number" name="km_llegada" step="0.1"
                               class="form-control" min="{{ $salida->km_salida }}" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-arrow-down-left-circle me-1"></i>Registrar Llegada
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Cadena de sobresalidas (solo visible en la raíz) --}}
@if($salida->esSalidaRaiz() && $salida->sobresalidas->count() > 0)
<div class="card mt-4">
    <div class="card-header bg-warning-subtle fw-bold text-warning-emphasis">
        <i class="bi bi-arrow-right-circle me-2"></i>Cadena de Sobresalidas
        <span class="badge bg-warning text-dark ms-1">{{ $salida->sobresalidas->count() }} tramo(s)</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Clave</th>
                    <th>Dirección</th>
                    <th>Al Mando</th>
                    <th>Salida</th>
                    <th>Llegada</th>
                    <th>Km recorridos</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                {{-- Primera fila: la salida raíz --}}
                <tr class="table-light">
                    <td><span class="badge bg-secondary">Raíz</span></td>
                    <td>
                        <span class="badge {{ $salida->claveSalida->tipo === 'emergencia' ? 'bg-danger' : 'bg-primary' }}">
                            {{ $salida->claveSalida->codigo }}
                        </span>
                    </td>
                    <td>{{ $salida->direccion }}</td>
                    <td>{{ $salida->alMando?->nombre ?? '—' }}</td>
                    <td>{{ $salida->salida_at->format('d/m H:i') }}</td>
                    <td>{{ $salida->llegada_at ? $salida->llegada_at->format('d/m H:i') : '—' }}</td>
                    <td>{{ $salida->km_recorrido ? formatKm($salida->km_recorrido, 0) . ' km' : '—' }}</td>
                    <td class="text-nowrap">
                        @if($salida->esEditable())
                        <a href="{{ route('salidas.edit', $salida) }}"
                           class="btn btn-xs btn-outline-warning"
                           title="Editar raíz">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                {{-- Sobresalidas --}}
                @foreach($salida->sobresalidas as $i => $sub)
                <tr>
                    <td><span class="badge bg-warning text-dark">+{{ $i + 1 }}</span></td>
                    <td>
                        <span class="badge {{ $sub->claveSalida->tipo === 'emergencia' ? 'bg-danger' : 'bg-primary' }}">
                            {{ $sub->claveSalida->codigo }}
                        </span>
                    </td>
                    <td>{{ $sub->direccion }}</td>
                    <td>{{ $sub->alMando?->nombre ?? '—' }}</td>
                    <td>{{ $sub->salida_at->format('d/m H:i') }}</td>
                    <td>
                        @if($sub->llegada_at)
                            {{ $sub->llegada_at->format('d/m H:i') }}
                        @else
                            <span class="badge bg-danger">Activa</span>
                        @endif
                    </td>
                    <td>{{ $sub->km_recorrido ? formatKm($sub->km_recorrido, 0) . ' km' : '—' }}</td>
                    <td class="text-nowrap">
                        <a href="{{ route('salidas.show', $sub) }}"
                           class="btn btn-xs btn-outline-secondary"
                           title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if($sub->esEditable())
                        <a href="{{ route('salidas.edit', $sub) }}"
                           class="btn btn-xs btn-outline-warning"
                           title="Editar este tramo">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection