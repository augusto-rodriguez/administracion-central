@extends('layouts.app')

@section('title', 'Detalle Turno')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-clock-history me-2"></i>Detalle de Turno</h4>
    <a href="{{ route('turnos.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-info-circle me-2"></i>Información del Turno
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr><th>Maquinista</th><td>{{ $turno->maquinista->nombre }}</td></tr>
                    <tr><th>Compañía</th><td>{{ $turno->maquinista->compania->nombre }}</td></tr>
                    <tr><th>Entrada</th><td>{{ $turno->entrada_at->format('d/m/Y H:i') }}</td></tr>
                    <tr>
                        <th>Salida</th>
                        <td>{{ $turno->salida_at ? $turno->salida_at->format('d/m/Y H:i') : '—' }}</td>
                    </tr>
                    <tr>
                        <th>Tiempo total</th>
                        <td>
                            @if($turno->salida_at)
                                <span class="badge bg-secondary fs-6">{{ $turno->tiempo_formateado }}</span>
                            @else
                                <span class="badge bg-success">En servicio</span>
                            @endif
                        </td>
                    </tr>
                    <tr><th>Observaciones</th><td>{{ $turno->observaciones ?? '—' }}</td></tr>
                </table>

                @if(!$turno->salida_at)
                <form action="{{ route('turnos.salida', $turno) }}" method="POST" class="mt-3">
                    @csrf
                    <button class="btn btn-danger" onclick="return confirm('¿Registrar salida?')">
                        <i class="bi bi-box-arrow-right me-1"></i>Registrar Salida
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-truck-front me-2"></i>Unidades en este Turno
            </div>
            <div class="card-body">
                @forelse($turno->unidades as $unidad)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary bg-opacity-10 rounded p-2">
                        <i class="bi bi-truck-front text-primary fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold">{{ $unidad->nombre }}</div>
                        <div class="text-muted small">{{ $unidad->compania->nombre }} — {{ $unidad->tipo }}</div>
                    </div>
                </div>
                @empty
                <p class="text-muted">Sin unidades registradas</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
