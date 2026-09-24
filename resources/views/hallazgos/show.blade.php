@extends('layouts.app')

@section('title', 'Hallazgo #' . $hallazgo->id)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h5 class="mb-1">
            <span class="badge {{ $hallazgo->severidad === 'critico' ? 'bg-danger' : ($hallazgo->severidad === 'atencion' ? 'bg-warning text-dark' : 'bg-info') }} me-2">
                {{ strtoupper($hallazgo->severidad) }}
            </span>
            {{ $hallazgo->item->nombre }}
        </h5>
        <small class="text-muted">
            {{ $hallazgo->item->seccion->nombre }}
            · {{ $hallazgo->inspeccion->unidad->nombre }}
            · Reportado {{ $hallazgo->created_at->format('d/m/Y H:i') }}
        </small>
    </div>
    @if(auth()->user()->esCuartelero())
        <a href="{{ route('checklist.show', $hallazgo->inspeccion) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    @else
        <a href="{{ route('hallazgos.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- VISTA CUARTELERO: solo resumen y fotos                     --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if(auth()->user()->esCuartelero())

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 small">
                <div class="col-6">
                    <span class="text-muted d-block">Unidad</span>
                    <strong>{{ $hallazgo->inspeccion->unidad->nombre }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted d-block">Fecha inspección</span>
                    <strong>{{ $hallazgo->inspeccion->fecha->format('d/m/Y') }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted d-block">Sección</span>
                    <strong>{{ $hallazgo->item->seccion->nombre }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted d-block">Estado</span>
                    @php
                        $estadoBadge = match($hallazgo->estado) {
                            'abierto'               => 'bg-danger',
                            'en_revision'           => 'bg-info',
                            'en_reparacion'         => 'bg-primary',
                            'resuelto_verificado'   => 'bg-success',
                            default                 => 'bg-secondary',
                        };
                        $estadoLabel = match($hallazgo->estado) {
                            'abierto'               => 'Abierto',
                            'en_revision'           => 'En revisión',
                            'en_reparacion'         => 'En reparación',
                            'resuelto_verificado'   => 'Resuelto y Verificado',
                            default                 => $hallazgo->estado,
                        };
                    @endphp
                    <span class="badge {{ $estadoBadge }}">{{ $estadoLabel }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($hallazgo->fotos->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header py-2"><strong><i class="bi bi-camera me-1"></i>Fotos adjuntas</strong></div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($hallazgo->fotos as $foto)
                        <div class="col-4 col-md-3">
                            <a href="{{ $foto->url }}" target="_blank">
                                <img src="{{ $foto->url }}" alt="{{ $foto->nombre_original }}"
                                     class="img-fluid rounded border" style="height: 120px; width: 100%; object-fit: cover;">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- VISTA COMPLETA: oficiales y admin                          --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@else

<div class="row g-4">
    {{-- Columna principal --}}
    <div class="col-lg-8">

        {{-- Info de la inspección --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 small">
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block">Unidad</span>
                        <strong>{{ $hallazgo->inspeccion->unidad->nombre }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block">Cuartelero</span>
                        <strong>{{ $hallazgo->inspeccion->cuartelero->nombre }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block">Fecha inspección</span>
                        <strong>{{ $hallazgo->inspeccion->fecha->format('d/m/Y') }}</strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="text-muted d-block">Días abierto</span>
                        <strong>{{ $hallazgo->diasAbierto() }} día(s)</strong>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('checklist.show', $hallazgo->inspeccion) }}" class="small">
                        <i class="bi bi-clipboard me-1"></i>Ver inspección completa →
                    </a>
                </div>
            </div>
        </div>

        {{-- Fotos del problema --}}
        @if($hallazgo->fotosProblema->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header py-2"><strong><i class="bi bi-camera me-1"></i>Fotos del problema</strong></div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($hallazgo->fotosProblema as $foto)
                            <div class="col-4 col-md-3">
                                <a href="{{ $foto->url }}" target="_blank">
                                    <img src="{{ $foto->url }}" alt="{{ $foto->nombre_original }}"
                                         class="img-fluid rounded border" style="height: 120px; width: 100%; object-fit: cover;">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Fotos de resolución --}}
        @if($hallazgo->fotosResolucion->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header py-2 bg-success text-white"><strong><i class="bi bi-check-circle me-1"></i>Fotos de resolución</strong></div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($hallazgo->fotosResolucion as $foto)
                            <div class="col-4 col-md-3">
                                <a href="{{ $foto->url }}" target="_blank">
                                    <img src="{{ $foto->url }}" alt="{{ $foto->nombre_original }}"
                                         class="img-fluid rounded border" style="height: 120px; width: 100%; object-fit: cover;">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Historial / Comentarios --}}
        <div class="card mb-3">
            <div class="card-header py-2"><strong><i class="bi bi-clock-history me-1"></i>Historial</strong></div>
            <div class="card-body p-0">
                @if($hallazgo->comentarios->isEmpty())
                    <p class="text-muted text-center py-3 mb-0">Sin actividad registrada.</p>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($hallazgo->comentarios as $comentario)
                            <div class="list-group-item py-2">
                                <div class="d-flex justify-content-between">
                                    <small class="fw-bold">{{ $comentario->usuario->nombre ?? '—' }}</small>
                                    <small class="text-muted">{{ $comentario->created_at->format('d/m H:i') }}</small>
                                </div>
                                @if($comentario->esCambioEstado())
                                    <small class="text-primary">
                                        <i class="bi bi-arrow-right me-1"></i>
                                        {{ $comentario->estado_anterior }} → {{ $comentario->estado_nuevo }}
                                    </small><br>
                                @endif
                                <span class="small">{{ $comentario->comentario }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Agregar comentario --}}
            <div class="card-footer">
                <form action="{{ route('hallazgos.comentar', $hallazgo) }}" method="POST">
                    @csrf
                    <div class="input-group input-group-sm">
                        <input type="text" name="comentario" class="form-control" placeholder="Agregar comentario..."
                               required maxlength="2000">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Columna lateral: acciones --}}
    <div class="col-lg-4">

        {{-- Estado actual --}}
        <div class="card mb-3">
            <div class="card-header py-2"><strong>Estado</strong></div>
            <div class="card-body">
                @php
                    $estadoBadge = match($hallazgo->estado) {
                        'abierto'               => 'bg-danger',
                        'en_revision'           => 'bg-info',
                        'en_reparacion'         => 'bg-primary',
                        'resuelto_verificado'   => 'bg-success',
                        default                 => 'bg-secondary',
                    };
                    $estadoLabel = match($hallazgo->estado) {
                        'abierto'               => 'Abierto',
                        'en_revision'           => 'En revisión',
                        'en_reparacion'         => 'En reparación',
                        'resuelto_verificado'   => 'Resuelto y Verificado',
                        default                 => $hallazgo->estado,
                    };
                @endphp
                <span class="badge {{ $estadoBadge }} fs-6 mb-3">{{ $estadoLabel }}</span>

                <form action="{{ route('hallazgos.cambiar-estado', $hallazgo) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <label class="form-label small fw-bold">Cambiar estado</label>
                    <select name="estado" class="form-select form-select-sm mb-2">
                        <option value="abierto" {{ $hallazgo->estado === 'abierto' ? 'selected' : '' }}>Abierto</option>
                        <option value="en_revision" {{ $hallazgo->estado === 'en_revision' ? 'selected' : '' }}>En revisión</option>
                        <option value="en_reparacion" {{ $hallazgo->estado === 'en_reparacion' ? 'selected' : '' }}>En reparación</option>
                        <option value="resuelto_verificado" {{ $hallazgo->estado === 'resuelto_verificado' ? 'selected' : '' }}>Resuelto y Verificado</option>
                    </select>
                    <textarea name="comentario" class="form-control form-control-sm mb-2" rows="2"
                              placeholder="Comentario (opcional)"></textarea>
                    <button type="submit" class="btn btn-sm btn-dark w-100">
                        <i class="bi bi-arrow-repeat me-1"></i>Actualizar estado
                    </button>
                </form>
            </div>
        </div>

        {{-- Asignar responsable --}}
        <div class="card mb-3">
            <div class="card-header py-2"><strong>Asignación</strong></div>
            <div class="card-body">
                @if($hallazgo->asignado)
                    <p class="small mb-2">
                        <i class="bi bi-person-fill me-1"></i>
                        <strong>{{ $hallazgo->asignado->nombre }}</strong>
                    </p>
                @else
                    <p class="small text-muted mb-2">Sin asignar</p>
                @endif

                <form action="{{ route('hallazgos.asignar', $hallazgo) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <select name="asignado_a" id="selectAsignado" class="form-select form-select-sm mb-2">
                        <option value="" data-email="">Seleccionar...</option>
                        @foreach($usuariosAsignables as $usuario)
                            <option value="{{ $usuario->id }}"
                                    data-email="{{ $usuario->email ?? '' }}"
                                    {{ $hallazgo->asignado_a == $usuario->id ? 'selected' : '' }}>
                                {{ $usuario->nombre }} ({{ $usuario->rol }})
                            </option>
                        @endforeach
                    </select>

                    {{-- Campo de correo editable --}}
                    <div id="emailAsignacion" style="display: none;">
                        <label class="form-label small fw-bold mt-1">
                            <i class="bi bi-envelope me-1"></i>Correo de notificación
                        </label>
                        <input type="email" name="email_notificacion" id="inputEmailAsignacion"
                               class="form-control form-control-sm mb-1"
                               placeholder="correo@ejemplo.com">
                        <div class="form-text small">
                            Puedes modificarlo si no coincide con el correo real del usuario.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-sm btn-outline-primary w-100 mt-2">
                        <i class="bi bi-person-check me-1"></i>Asignar
                    </button>
                </form>
            </div>
        </div>

        {{-- Subir foto de resolución --}}
        <div class="card mb-3">
            <div class="card-header py-2"><strong>Adjuntar foto</strong></div>
            <div class="card-body">
                <form action="{{ route('hallazgos.subir-foto', $hallazgo) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <select name="tipo" class="form-select form-select-sm mb-2">
                        <option value="problema">Foto del problema</option>
                        <option value="resolucion">Foto de resolución</option>
                    </select>
                    <input type="file" name="foto" class="form-control form-control-sm mb-2" accept="image/*" required>
                    <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-upload me-1"></i>Subir foto
                    </button>
                </form>
            </div>
        </div>

        {{-- Info de resolución --}}
        @if($hallazgo->resuelto_at)
            <div class="card border-success">
                <div class="card-body small">
                    <i class="bi bi-check-circle text-success me-1"></i>
                    <strong>Resuelto</strong><br>
                    {{ $hallazgo->resuelto_at->format('d/m/Y H:i') }}<br>
                    Por: {{ $hallazgo->resolutorPor->nombre ?? '—' }}
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAsignado = document.getElementById('selectAsignado');
    const emailDiv = document.getElementById('emailAsignacion');
    const emailInput = document.getElementById('inputEmailAsignacion');

    selectAsignado.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const email = selected.dataset.email || '';

        if (this.value) {
            emailInput.value = email;
            emailDiv.style.display = 'block';

            if (!email) {
                emailInput.classList.add('border-warning');
                emailInput.placeholder = 'Este usuario no tiene correo registrado';
            } else {
                emailInput.classList.remove('border-warning');
                emailInput.placeholder = 'correo@ejemplo.com';
            }
        } else {
            emailDiv.style.display = 'none';
            emailInput.value = '';
        }
    });

    if (selectAsignado.value) {
        selectAsignado.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush

@endif
@endsection