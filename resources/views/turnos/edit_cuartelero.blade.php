@extends('layouts.app')
@section('title', 'Editar Puesta en Servicio')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-pencil-square me-2"></i>Editar Puesta en Servicio — Cuartelero
    </h4>
    <a href="{{ route('turnos.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

{{-- Aviso ventana de edición --}}
@php
    $minutosRestantes = (int) now()->diffInMinutes($turno->salida_at->addHours(12), false);
    $horasRestantes   = floor($minutosRestantes / 60);
    $minsRestantes    = $minutosRestantes % 60;
@endphp
<div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-clock-history fs-5"></i>
    <div>
        <strong>Ventana de edición:</strong>
        Puedes modificar este turno durante las primeras 12 horas tras su cierre.
        Tiempo restante: <strong>{{ $horasRestantes }}h {{ $minsRestantes }}m</strong>.
    </div>
</div>

<div class="card">
    <div class="card-header bg-warning bg-opacity-25 fw-bold">
        <i class="bi bi-person-gear me-2 text-primary"></i>
        {{ $turno->cuartelero->nombre }} — {{ $turno->cuartelero->compania->nombre }}
    </div>
    <div class="card-body">

        {{-- Unidades (solo lectura) --}}
        <div class="mb-4">
            <label class="form-label fw-bold text-muted small">UNIDADES DEL TURNO</label>
            <div>
                @foreach($turno->unidades as $unidad)
                    <span class="badge bg-secondary me-1">
                        <i class="bi bi-truck-front me-1"></i>{{ $unidad->nombre }}
                    </span>
                @endforeach
            </div>
        </div>

        <form action="{{ route('cuarteleros.turnos.update', $turno) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">
                        Hora de entrada <span class="text-danger">*</span>
                    </label>
                    <input type="datetime-local"
                           name="entrada_at"
                           class="form-control @error('entrada_at') is-invalid @enderror"
                           value="{{ old('entrada_at', $turno->entrada_at->format('Y-m-d\TH:i')) }}"
                           max="{{ now()->format('Y-m-d\TH:i') }}"
                           required>
                    @error('entrada_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">
                        Hora de salida <span class="text-danger">*</span>
                    </label>
                    <input type="datetime-local"
                           name="salida_at"
                           class="form-control @error('salida_at') is-invalid @enderror"
                           value="{{ old('salida_at', $turno->salida_at->format('Y-m-d\TH:i')) }}"
                           max="{{ now()->format('Y-m-d\TH:i') }}"
                           required>
                    @error('salida_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Observaciones</label>
                    <input type="text"
                           name="observaciones"
                           class="form-control @error('observaciones') is-invalid @enderror"
                           placeholder="Opcional..."
                           maxlength="500"
                           value="{{ old('observaciones', $turno->observaciones) }}">
                    @error('observaciones')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-check-lg me-1"></i>Guardar cambios
                </button>
                <a href="{{ route('turnos.index') }}" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

@endsection