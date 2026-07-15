@extends('layouts.app')

@section('title', 'Libro de Novedades')

@section('content')

{{-- Encabezado --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <h4 class="mb-0"><i class="bi bi-journal-text me-2"></i>Libro de Novedades</h4>
    @if($libroActivo)
    <a href="{{ route('libro-novedades.edit', $libroActivo) }}" class="btn btn-warning btn-sm">
        <i class="bi bi-pencil-square me-1"></i>Continuar turno en curso
    </a>
    @else
        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalIniciar">
            <i class="bi bi-play-circle me-1"></i>Iniciar Libro de Novedades
        </button>
    @endif
</div>

{{-- Alerta turno activo --}}
@if($libroActivo)
<div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
    <div>
        <strong>Turno en curso:</strong>
        {{ $libroActivo->turno_label }} — {{ $libroActivo->fecha->format('d/m/Y') }}
        — Operador: {{ $libroActivo->operador->nombre }}
    </div>
</div>
@endif

<div class="card">
    <div class="card-body p-0">

        {{-- Tabla desktop --}}
        <div class="table-responsive">
            <table class="table table-hover mb-0 d-none d-md-table">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Turno</th>
                        <th>Horario</th>
                        <th>Operador</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($libros as $libro)
                    <tr>
                        <td class="fw-bold text-nowrap">{{ $libro->fecha->format('d/m/Y') }}</td>
                        <td>
                            @if($libro->turno === 'dia')
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-sun me-1"></i>Día
                                </span>
                            @else
                                <span class="badge bg-dark">
                                    <i class="bi bi-moon-stars me-1"></i>Noche
                                </span>
                            @endif
                        </td>
                        <td class="text-muted small text-nowrap">{{ $libro->horario }}</td>
                        <td class="text-nowrap">{{ $libro->operador->nombre ?? '—' }}</td>
                        <td>
                            @if($libro->estado === 'borrador')
                                <span class="badge bg-warning text-dark">En curso</span>
                            @else
                                <span class="badge bg-success">Cerrado</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @if($libro->estado === 'borrador')
                                <a href="{{ route('libro-novedades.edit', $libro) }}"
                                   class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            @else
                                <a href="{{ route('libro-novedades.show', $libro) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No hay libros de novedades registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Cards móvil --}}
        <div class="d-md-none">
            @forelse($libros as $libro)
                <div class="border-bottom px-3 py-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="fw-bold">{{ $libro->fecha->format('d/m/Y') }}</span>
                            @if($libro->turno === 'dia')
                                <span class="badge bg-warning text-dark ms-1">
                                    <i class="bi bi-sun me-1"></i>Día
                                </span>
                            @else
                                <span class="badge bg-dark ms-1">
                                    <i class="bi bi-moon-stars me-1"></i>Noche
                                </span>
                            @endif
                            @if($libro->estado === 'borrador')
                                <span class="badge bg-warning text-dark ms-1">En curso</span>
                            @else
                                <span class="badge bg-success ms-1">Cerrado</span>
                            @endif
                        </div>
                        @if($libro->estado === 'borrador')
                            <a href="{{ route('libro-novedades.edit', $libro) }}"
                               class="btn btn-sm btn-outline-warning flex-shrink-0">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @else
                            <a href="{{ route('libro-novedades.show', $libro) }}"
                               class="btn btn-sm btn-outline-primary flex-shrink-0">
                                <i class="bi bi-eye"></i>
                            </a>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap gap-2 small text-muted">
                        <span><i class="bi bi-clock me-1"></i>{{ $libro->horario }}</span>
                        <span><i class="bi bi-person me-1"></i>{{ $libro->operador->nombre ?? '—' }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    No hay libros de novedades registrados
                </div>
            @endforelse
        </div>

    </div>
</div>

<div class="mt-3">
    {{ $libros->links() }}
</div>

{{-- ── MODAL INICIAR TURNO ─────────────────────────────────────────── --}}
<div class="modal fade" id="modalIniciar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-play-circle me-2"></i>Iniciar Libro de Novedades</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('libro-novedades.iniciar') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if(session('alerta_mismo_operador'))
                    <div class="alert alert-warning d-flex gap-2 align-items-start">
                        <i class="bi bi-exclamation-triangle-fill fs-5 mt-1 flex-shrink-0"></i>
                        <div>
                            <strong>¡Atención!</strong> Eres el mismo operador del turno anterior
                            ({{ session('turno_anterior_turno') }} del {{ session('turno_anterior_fecha') }}).
                            <br>¿Estás seguro de que deseas iniciar este turno?
                        </div>
                    </div>
                    <input type="hidden" name="confirmar_mismo_operador" value="1">
                    @endif
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small">Tipo de turno <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="turno" id="turnoDia" value="dia" checked>
                                    <label class="form-check-label" for="turnoDia">
                                        <i class="bi bi-sun me-1 text-warning"></i>Día
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="turno" id="turnoNoche" value="noche">
                                    <label class="form-check-label" for="turnoNoche">
                                        <i class="bi bi-moon-stars me-1"></i>Noche
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold small">Hora inicio <span class="text-danger">*</span></label>
                            <input type="time" name="hora_inicio" id="modalHoraInicio" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold small">Hora fin <span class="text-danger">*</span></label>
                            <input type="time" name="hora_fin" id="modalHoraFin" class="form-control form-control-sm" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-play-circle me-1"></i>Iniciar turno
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('modalIniciar').addEventListener('show.bs.modal', function () {
    const ahora = new Date();
    const hh = String(ahora.getHours()).padStart(2, '0');
    const mm = String(ahora.getMinutes()).padStart(2, '0');
    document.getElementById('modalHoraInicio').value = hh + ':' + mm;
    actualizarHoraFin();
});

document.querySelectorAll('input[name="turno"]').forEach(r => {
    r.addEventListener('change', actualizarHoraFin);
});

function actualizarHoraFin() {
    const turno = document.querySelector('input[name="turno"]:checked').value;
    document.getElementById('modalHoraFin').value = turno === 'dia' ? '20:00' : '08:00';
}

@if(session('alerta_mismo_operador'))
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('modalHoraInicio').value = '{{ old('hora_inicio') }}';
        document.getElementById('modalHoraFin').value = '{{ old('hora_fin') }}';
        const turno = '{{ old('turno', 'dia') }}';
        document.querySelector('input[name="turno"][value="' + turno + '"]').checked = true;
        new bootstrap.Modal(document.getElementById('modalIniciar')).show();
    });
@endif
</script>
@endpush

@endsection