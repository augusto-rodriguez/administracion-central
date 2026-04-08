@extends('layouts.app')

@section('title', 'Generar Boletín')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-megaphone me-2"></i>Generar Boletín</h4>
    <a href="{{ route('boletines.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-info-circle-fill fs-5"></i>
            <div>
                <strong>Verifica antes de generar.</strong>
                Los datos mostrados a continuación corresponden al estado actual del turno.
                Revisa que los cuarteleros, maquinistas y citaciones sean correctos antes de generar el boletín.
            </div>
        </div>
        <form action="{{ route('boletines.store') }}" method="POST">
            @csrf

            {{-- DATOS --}}
            <div class="card mb-4">
                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-calendar-event me-2"></i>Datos del boletín
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Fecha <span class="text-danger">*</span></label>
                            <input type="date" name="fecha"
                                   class="form-control @error('fecha') is-invalid @enderror"
                                   value="{{ old('fecha', now()->toDateString()) }}" required>
                            @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Turno <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                                <option value="am" {{ now()->hour < 17 ? 'selected' : '' }}>
                                    <i class="bi bi-sun"></i> AM — mañana
                                </option>
                                <option value="pm" {{ now()->hour >= 17 ? 'selected' : '' }}>
                                    <i class="bi bi-moon-stars"></i> PM — noche
                                </option>
                            </select>
                            @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- CUARTELEROS --}}
            <div class="card mb-4">
                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-person-badge me-2"></i>Cuarteleros en turno
                </div>
                <div class="card-body">
                    @forelse($cuarteleros as $index => $c)
                        @if($c->cuartelero)
                            <div class="border rounded p-2 mb-2 d-flex align-items-center gap-2">
                                <input type="hidden"
                                    name="cuarteleros[{{ $index }}][cuartelero_id]"
                                    value="{{ $c->cuartelero->id }}">
                                <i class="bi bi-person-fill text-secondary"></i>
                                <span>
                                    {{ $c->cuartelero->compania ? '38-' . $c->cuartelero->compania->numero : $c->cuartelero->nombre }}
                                </span>
                            </div>
                        @endif
                    @empty
                        <p class="text-muted small mb-0">Sin cuarteleros en turno.</p>
                    @endforelse
                </div>
            </div>

            {{-- MAQUINISTAS --}}
            <div class="card mb-4">
                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-truck-front me-2"></i>Maquinistas en turno
                </div>
                <div class="card-body">
                    @forelse($maquinistas as $index => $m)
                        @if($m->voluntario)
                            <div class="border rounded p-2 mb-2 d-flex align-items-center gap-2">
                                <input type="hidden"
                                    name="maquinistas[{{ $index }}][voluntario_id]"
                                    value="{{ $m->voluntario->id }}">
                                <i class="bi bi-person-fill text-danger"></i>
                                <span>{{ $m->voluntario->nombre ?? 'Sin nombre' }}</span>
                                @if($m->unidades->isNotEmpty())
                                    {{-- Mostrar TODAS las unidades, no solo la primera --}}
                                    @foreach($m->unidades as $unidad)
                                        <span class="badge bg-secondary ms-1">{{ $unidad->nombre }}</span>
                                    @endforeach
                                @endif
                            </div>
                        @endif
                    @empty
                        <p class="text-muted small mb-0">Sin maquinistas en turno.</p>
                    @endforelse
                </div>
            </div>

            {{-- CITACIONES --}}
            <div class="card mb-4">
                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-megaphone me-2"></i>Citaciones vigentes
                </div>
                <div class="card-body">
                    @forelse($citaciones as $citacion)
                        <div class="border rounded p-2 mb-2">
                            <span class="fw-bold text-primary">
                                {{ $citacion->compania->nombre ?? '—' }}:
                            </span>
                            {{ $citacion->mensaje }}
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Sin citaciones vigentes.</p>
                    @endforelse
                </div>
            </div>

            {{-- ── CAMBIO DE GUARDIA (solo domingo PM) ───────────────────────── --}}
            @if($esDomingoPM)
            <div class="card mb-4 border-dark" id="bloqueGuardia"
                style="display: {{ now('America/Santiago')->hour >= 17 ? 'block' : 'none' }};">
                <div class="card-header bg-dark text-white fw-bold">
                    <i class="bi bi-shield-lock me-2"></i>Cambio de Guardia de Comandancia
                </div>
                <div class="card-body">
                    <div class="alert alert-warning d-flex gap-2 align-items-start mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
                        <div>
                            <strong>Es domingo PM.</strong> Corresponde el cambio de comandante de guardia.
                            @if($guardiaActual)
                                <br>Guardia actual:
                                <strong>{{ $guardiaActual->voluntario->nombre }}</strong>
                                ({{ $guardiaActual->voluntario->roles->firstWhere('rol','comandante')?->rango }}°
                                Comandante)
                            @endif
                            @if($proximoComandante)
                                <br>Próximo por correlativo:
                                <strong>{{ $proximoComandante->voluntario->nombre }}</strong>
                                ({{ $proximoComandante->rango }}° Comandante)
                            @endif
                        </div>
                    </div>

                    {{-- Checkbox para confirmar cambio --}}
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="cambiar_guardia"
                            value="1" id="cambiarGuardia" checked
                            onchange="toggleSelectorComandante()">
                        <label class="form-check-label fw-bold" for="cambiarGuardia">
                            Realizar cambio de guardia
                        </label>
                    </div>

                    {{-- Selector de nuevo comandante --}}
                    <div id="selectorComandante">
                        <label class="form-label fw-bold">Nuevo comandante de guardia</label>
                        <select name="nuevo_comandante_id" class="form-select">
                            <option value="">Seleccionar...</option>
                            @foreach($comandantes as $rol)
                                <option value="{{ $rol->voluntario->id }}"
                                        {{ $proximoComandante?->voluntario->id == $rol->voluntario->id ? 'selected' : '' }}>
                                    {{ $rol->rango }}° Comandante — {{ $rol->voluntario->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            @endif

            <div class="mt-4">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-broadcast me-1"></i>Generar boletín
                </button>
            </div>

        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Mostrar/ocultar bloque de guardia según turno seleccionado
    document.querySelector('select[name="tipo"]').addEventListener('change', function () {
        const bloqueGuardia = document.getElementById('bloqueGuardia');
        if (!bloqueGuardia) return;

        bloqueGuardia.style.display = this.value === 'pm' ? 'block' : 'none';
    });
    function toggleSelectorComandante() {
        const checked  = document.getElementById('cambiarGuardia').checked;
        const selector = document.getElementById('selectorComandante');
        selector.style.display = checked ? 'block' : 'none';
    }
</script>
@endpush