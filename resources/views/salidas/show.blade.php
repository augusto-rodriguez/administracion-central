@extends('layouts.app')
@section('title', 'Detalle Salida')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-arrow-up-right-circle me-2"></i>Detalle de Salida</h4>
    <a href="{{ route('salidas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

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
                            @if($salida->km_recorrido)
                                <span class="badge bg-info text-dark fs-6">{{ formatKm($salida->km_recorrido, 1) }} km</span>
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
@endsection