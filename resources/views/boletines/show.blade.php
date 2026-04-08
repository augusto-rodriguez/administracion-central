@extends('layouts.app')

@section('title', 'Boletín ' . \Carbon\Carbon::parse($boletin->fecha)->format('d/m/Y'))

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="bi bi-megaphone me-2"></i>Boletín</h4>
        <small class="text-muted">
            {{ \Carbon\Carbon::parse($boletin->fecha)->format('d/m/Y') }} —
            @if($boletin->tipo === 'am')
                <span class="badge bg-warning text-dark"><i class="bi bi-sun me-1"></i>AM</span>
            @else
                <span class="badge bg-dark"><i class="bi bi-moon-stars me-1"></i>PM</span>
            @endif
        </small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('boletines.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
        <button class="btn btn-danger btn-sm"
                data-bs-toggle="modal" data-bs-target="#modalLectura">
            <i class="bi bi-broadcast me-1"></i>Leer boletín
        </button>
        {{-- ── Eliminar ── --}}
        <form action="{{ route('boletines.destroy', $boletin) }}" method="POST"
            class="d-inline"
            onsubmit="return confirm('¿Eliminar este boletín? Podrás volver a generarlo.')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-1"></i>Eliminar
            </button>
        </form>
    </div>
</div>

{{-- ── DETALLE ─────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header bg-light fw-bold">
        <i class="bi bi-person-badge me-2"></i>Cuarteleros en turno
    </div>
    <div class="card-body">
        @forelse($boletin->cuarteleros as $c)
            <div class="border rounded p-2 mb-2 d-flex align-items-center gap-2">
                <i class="bi bi-person-fill text-secondary"></i>
                <span>{{ $c->nombre }}</span>
            </div>
        @empty
            <p class="text-muted small mb-0">Sin cuarteleros registrados.</p>
        @endforelse
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light fw-bold">
        <i class="bi bi-truck-front me-2"></i>Maquinistas en turno
    </div>
    <div class="card-body">
        @forelse($boletin->maquinistas as $m)
            <div class="border rounded p-2 mb-2 d-flex align-items-center gap-2">
                <i class="bi bi-person-fill text-danger"></i>
                <span>{{ $m->voluntario->nombre ?? '—' }}</span>
                @if($m->unidad)
                    <span class="badge bg-secondary ms-1">{{ $m->unidad->nombre }}</span>
                @endif
                <span class="badge bg-success ms-1">{{ $m->estado }}</span>
            </div>
        @empty
            <p class="text-muted small mb-0">Sin maquinistas registrados.</p>
        @endforelse
    </div>
</div>

{{-- Cambio de guardia si existe --}}
@if($boletin->texto_guardia)
<div class="card mb-4 border-info">
    <div class="card-header bg-info bg-opacity-10 fw-bold text-info">
        <i class="bi bi-shield-check me-2"></i>Cambio de Guardia de Comandancia
    </div>
    <div class="card-body">
        <p class="mb-0 fw-bold">{{ $boletin->texto_guardia }}</p>
    </div>
</div>
@endif

{{-- ── MODAL LECTURA ───────────────────────────────────────────────── --}}
<div class="modal fade" id="modalLectura" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" style="margin-top: 2rem;">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white py-2">
                <div>
                    <h6 class="modal-title mb-0">
                        <i class="bi bi-broadcast me-2"></i>Lectura del boletín
                    </h6>
                    <small class="opacity-75">
                        {{ \Carbon\Carbon::parse($boletin->fecha)->format('d/m/Y') }} —
                        {{ $boletin->tipo === 'am' ? 'Turno AM' : 'Turno PM' }}
                    </small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">
                @php
                    $lineas   = explode("\n", trim($textoBoletin));
                    $seccion  = 'intro';
                @endphp

                {{-- Intro --}}
                <div class="p-3 bg-danger bg-opacity-10 border-bottom">
                    @foreach($lineas as $linea)
                        @if(str_starts_with($linea, 'EN TURNO') || str_starts_with($linea, 'MAQUINISTAS') || str_starts_with($linea, 'CITACIONES'))
                            @break
                        @endif
                        @if(trim($linea))
                            <p class="mb-0 fw-bold text-danger" style="font-size: 0.85rem;">{{ $linea }}</p>
                        @endif
                    @endforeach
                </div>

                <div class="row g-0">
                    {{-- Columna izquierda: En turno + Maquinistas --}}
                    <div class="col-md-6 border-end p-3">
                        @php $enSeccion = false; $seccionActual = ''; @endphp
                        @foreach($lineas as $linea)
                            @if(str_starts_with($linea, 'EN TURNO'))
                                @php $enSeccion = true; $seccionActual = 'turno'; @endphp
                                <p class="fw-bold text-uppercase border-bottom pb-1 mb-2"
                                   style="font-size: 0.75rem; color: #dc3545; letter-spacing: 0.05em;">
                                    <i class="bi bi-person-badge me-1"></i>{{ $linea }}
                                </p>
                            @elseif(str_starts_with($linea, 'MAQUINISTAS'))
                                @php $seccionActual = 'maquinistas'; @endphp
                                <p class="fw-bold text-uppercase border-bottom pb-1 mb-2 mt-3"
                                   style="font-size: 0.75rem; color: #0d6efd; letter-spacing: 0.05em;">
                                    <i class="bi bi-truck-front me-1"></i>{{ $linea }}
                                </p>
                            @elseif(str_starts_with($linea, 'CITACIONES'))
                                @php $enSeccion = false; @endphp
                            @elseif($enSeccion && trim($linea))
                                <p class="mb-1 ps-2 {{ $seccionActual === 'maquinistas' ? 'fw-bold' : '' }}"
                                   style="font-size: {{ $seccionActual === 'maquinistas' ? '0.92rem' : '0.82rem' }};">
                                    @if($seccionActual === 'turno')
                                        <i class="bi bi-person-fill text-secondary me-1"></i>
                                    @else
                                        <i class="bi bi-truck-front text-primary me-1"></i>
                                    @endif
                                    {{ $linea }}
                                </p>
                            @endif
                        @endforeach
                    </div>

                    {{-- Columna derecha: Citaciones --}}
                    <div class="col-md-6 p-3">
                        @php
                            $enCitaciones    = false;
                            $companiaActual  = null;
                            $citacionesAgrup = [];

                            foreach ($lineas as $linea) {
                                if (str_starts_with($linea, 'CITACIONES')) {
                                    $enCitaciones = true;
                                    continue;
                                }
                                if ($enCitaciones && trim($linea)) {
                                    // Detectar si la línea tiene formato "COMPAÑIA: mensaje"
                                    if (str_contains($linea, ':')) {
                                        [$cia, $msg] = explode(':', $linea, 2);
                                        $citacionesAgrup[trim($cia)][] = trim($msg);
                                    } else {
                                        $citacionesAgrup['—'][] = trim($linea);
                                    }
                                }
                            }
                        @endphp

                        @if($enCitaciones && count($citacionesAgrup))
                            <p class="fw-bold text-uppercase border-bottom pb-1 mb-2"
                            style="font-size: 0.75rem; color: #198754; letter-spacing: 0.05em;">
                                <i class="bi bi-megaphone me-1"></i>CITACIONES
                            </p>

                            @foreach($citacionesAgrup as $compania => $mensajes)
                                <div class="mb-2">
                                    <p class="fw-bold mb-1"
                                    style="font-size: 0.78rem; color: #198754;">
                                        <i class="bi bi-building me-1"></i>{{ $compania }}
                                    </p>
                                    @foreach($mensajes as $msg)
                                        <p class="mb-1 ps-3 fw-bold" style="font-size: 0.82rem;">
                                            <i class="bi bi-chevron-right text-success me-1"
                                            style="font-size: 0.7rem;"></i>
                                            {{ $msg }}
                                        </p>
                                    @endforeach
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted small">Sin citaciones vigentes.</p>
                        @endif
                    </div>
                </div>

                {{-- Guardia de comandancia al final del modal --}}
                <div class="border-top p-3">
                    <div class="alert alert-info d-flex gap-2 align-items-start mb-0"
                        style="font-size: 0.85rem;">
                        <i class="bi bi-shield-check fs-5 text-primary mt-1"></i>
                        <div>
                            @if($boletin->texto_guardia)
                                {{-- Hubo cambio de guardia en este boletín --}}
                                <span class="fw-bold d-block mb-1"
                                    style="font-size: 0.72rem; letter-spacing: 0.05em; color: #0d6efd;">
                                    <i class="bi bi-shield-lock me-1"></i>CAMBIO DE GUARDIA DE COMANDANCIA
                                </span>
                                {{ $boletin->texto_guardia }}
                            @else
                                {{-- Mostrar guardia activa de la semana --}}
                                <span class="fw-bold d-block mb-1"
                                    style="font-size: 0.72rem; letter-spacing: 0.05em; color: #0d6efd;">
                                    <i class="bi bi-shield-fill me-1"></i>GUARDIA DE COMANDANCIA
                                </span>
                                @php
                                    $guardiaActual = \App\Models\GuardiaComandante::activa();
                                    $rolCdte = $guardiaActual?->voluntario
                                        ?->roles->firstWhere('rol', 'comandante');
                                @endphp
                                @if($guardiaActual && $rolCdte)
                                    {{ $rolCdte->rango }}° Comandante<br>
                                    <strong>Sr. {{ strtoupper($guardiaActual->voluntario->nombre) }}</strong>
                                @else
                                    <span class="text-muted">Sin comandante de guardia asignado esta semana.</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Cerrar
                </button>
                <!-- <button type="button" class="btn btn-success btn-sm" onclick="copiarBoletin()">
                    <i class="bi bi-clipboard me-1"></i>Copiar texto
                </button> -->
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
@if($boletinGenerado)
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('modalLectura')).show();
    });
@endif

function copiarBoletin() {
    // Copiar texto plano + guardia si existe
    let texto = document.getElementById('textoBoletin')?.innerText ?? '';

    @if($boletin->texto_guardia)
        texto += '\n\n{{ addslashes($boletin->texto_guardia) }}';
    @endif

    navigator.clipboard.writeText(texto).then(() => {
        const btn = event.target.closest('button');
        btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Copiado';
        setTimeout(() => {
            btn.innerHTML = '<i class="bi bi-clipboard me-1"></i>Copiar texto';
        }, 2000);
    });
}
</script>
@endpush

@endsection