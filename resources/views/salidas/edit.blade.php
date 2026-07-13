@extends('layouts.app')
@section('title', 'Editar Salida')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
        <i class="bi bi-pencil-square me-2"></i>Editar Salida — {{ $salida->unidad->nombre }}
    </h4>
    <a href="{{ route('salidas.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

{{-- Aviso de ventana de edición --}}
@php
    $minutosRestantes = (int) now()->diffInMinutes($salida->salida_at->addHours(12), false);
    $horasRestantes   = floor($minutosRestantes / 60);
    $minsRestantes    = $minutosRestantes % 60;
@endphp
<div class="alert alert-warning py-2 mb-2" style="font-size:13px">
    <i class="bi bi-clock-history me-1"></i>
    Puedes editar esta salida durante las primeras 12 horas.
    Tiempo restante: <strong>{{ $horasRestantes }}h {{ $minsRestantes }}m</strong>.
</div>

@if(!$kmYConductorEditables)
<div class="alert alert-info py-2 mb-2" style="font-size:13px">
    <i class="bi bi-info-circle me-1"></i>
    Salida con <strong>sobresalidas encadenadas</strong>. Puedes editar clave, dirección,
    al mando, hora y observaciones. El km de llegada se propagará a todos los tramos.
</div>
@endif

{{-- Datos no editables — compacto --}}
<div class="card mb-2">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-auto">
                <span class="text-muted small fw-bold">Unidad:</span>
                <strong>{{ $salida->unidad->nombre }}</strong>
                <span class="text-muted">— {{ $salida->unidad->compania->nombre }}</span>
            </div>
            <div class="col-auto">
                <span class="text-muted small fw-bold">Conductor:</span>
                <strong>{{ $salida->conductor_nombre }}</strong>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('salidas.update', $salida) }}" method="POST">
@csrf
@method('PUT')
<input type="hidden" name="confirmar_al_mando_activo" id="confirmarAlMandoActivo" value="0">

{{-- Aviso de voluntario al mando con salida activa --}}
@if(session('warning_al_mando'))
<div class="alert alert-warning d-flex align-items-start gap-2 py-2 mb-2">
    <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
    <div>
        <strong>{{ session('warning_al_mando') }}</strong>
        <div class="form-check mt-1">
            <input class="form-check-input" type="checkbox" id="checkConfirmarAlMando">
            <label class="form-check-label" for="checkConfirmarAlMando">
                Entendido, deseo guardar los cambios de todas formas
            </label>
        </div>
    </div>
</div>
@endif

{{-- Kilometraje --}}
<div class="card mb-2 border-warning">
    <div class="card-header py-2 bg-warning bg-opacity-10 fw-bold text-warning-emphasis" style="font-size:14px">
        <i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i>Kilometraje
    </div>
    <div class="card-body py-2">

        @if($salida->km_salida)
        <div class="alert alert-warning py-1 px-2 mb-2" style="font-size:12px">
            <i class="bi bi-exclamation-triangle me-1"></i>
            El km de llegada debe ser ≥ al de salida
            (<strong>{{ number_format((int) $salida->km_salida, 0, ',', '.') }} km</strong>).
        </div>
        @endif

        <div class="row g-2 align-items-start">

            {{-- Km Salida — solo lectura --}}
            <div class="col-md-4">
                <label class="form-label mb-1 small fw-bold text-muted">Km Salida</label>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control bg-light"
                           value="{{ $salida->km_salida ? number_format((int) $salida->km_salida, 0, ',', '.') : '—' }}"
                           readonly disabled>
                    <span class="input-group-text text-muted">km</span>
                </div>
            </div>

            {{-- Km Llegada — editable --}}
            <div class="col-md-4">
                <label class="form-label mb-1 small fw-bold">Km Llegada</label>
                <div class="input-group input-group-sm">
                    <input type="number"
                           name="km_llegada"
                           id="inputKmLlegada"
                           class="form-control @error('km_llegada') is-invalid @enderror"
                           min="0" step="1"
                           value="{{ old('km_llegada', $salida->km_llegada ? (int) $salida->km_llegada : '') }}"
                           placeholder="{{ $salida->km_llegada ? '' : 'Sin registrar' }}">
                    <span class="input-group-text text-muted">km</span>
                    @error('km_llegada') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div id="avisoKmNegativo" class="text-danger small mt-1" style="display:none; font-size:11px">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    Menor al km de salida ({{ $salida->km_salida ? number_format((int) $salida->km_salida, 0, ',', '.') : '—' }} km)
                </div>
            </div>

            {{-- Km Recorridos — preview calculado --}}
            <div class="col-md-4">
                <label class="form-label mb-1 small fw-bold text-muted">Km Recorridos</label>
                <div class="input-group input-group-sm">
                    <input type="text" id="previewKmRecorridos" class="form-control fw-bold bg-light" readonly
                           value="{{ ($salida->km_salida && $salida->km_llegada) ? number_format((int)$salida->km_llegada - (int)$salida->km_salida, 0, ',', '.') : '—' }}">
                    <span class="input-group-text text-muted">km</span>
                </div>
                <div id="avisoKmRecorridos" class="text-muted mt-1" style="display:none; font-size:11px">
                    <i class="bi bi-arrow-repeat me-1"></i>Actualizado en tiempo real
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Datos editables --}}
<div class="card mb-2">
    <div class="card-header py-2 bg-white fw-bold" style="font-size:14px">
        <i class="bi bi-pencil me-1"></i>Datos editables
    </div>
    <div class="card-body py-2">

        <div class="row g-2">

            {{-- Clave --}}
            <div class="col-md-8">
                <label class="form-label mb-1 small fw-bold">Clave <span class="text-danger">*</span></label>
                <select name="clave_salida_id" id="selectClave"
                        class="form-select form-select-sm @error('clave_salida_id') is-invalid @enderror" required>
                    <optgroup label="🚨 Emergencias">
                        @foreach($claves->where('tipo', 'emergencia') as $clave)
                            <option value="{{ $clave->id }}" data-tipo="emergencia"
                                    {{ old('clave_salida_id', $salida->clave_salida_id) == $clave->id ? 'selected' : '' }}>
                                {{ $clave->codigo }} — {{ $clave->descripcion }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="⚙️ Administrativas">
                        @foreach($claves->where('tipo', 'administrativa') as $clave)
                            <option value="{{ $clave->id }}" data-tipo="administrativa"
                                    {{ old('clave_salida_id', $salida->clave_salida_id) == $clave->id ? 'selected' : '' }}>
                                {{ $clave->codigo }} — {{ $clave->descripcion }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
                @error('clave_salida_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Oficial autorizante --}}
            <div class="col-md-4" id="bloqueOficial" style="{{ old('clave_salida_id') ? '' : ($salida->claveSalida->tipo === 'administrativa' ? '' : 'display:none') }}">
                <label class="form-label mb-1 small fw-bold">
                    Oficial autorizante <span class="text-danger">*</span>
                </label>
                <select name="oficial_id" id="selectOficial"
                        class="form-select form-select-sm @error('oficial_id') is-invalid @enderror">
                    <option value="">Seleccionar oficial...</option>
                    @foreach($oficiales as $oficial)
                        <option value="{{ $oficial->id }}"
                                {{ old('oficial_id', $salida->oficial_id) == $oficial->id ? 'selected' : '' }}>
                            {{ $oficial->nombre }} — {{ $oficial->compania->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('oficial_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Dirección --}}
            <div class="col-12">
                <label class="form-label mb-1 small fw-bold">Dirección / Lugar <span class="text-danger">*</span></label>
                <input type="text" name="direccion"
                       class="form-control form-control-sm @error('direccion') is-invalid @enderror"
                       value="{{ old('direccion', $salida->direccion) }}" required>
                @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Al Mando --}}
            <div class="col-md-5">
                <label class="form-label mb-1 small fw-bold">Voluntario al Mando</label>
                <select name="al_mando_id"
                        class="form-select form-select-sm @error('al_mando_id') is-invalid @enderror">
                    <option value="">— Sin voluntario al mando —</option>
                    @foreach($voluntariosAlMando as $voluntario)
                        <option value="{{ $voluntario->id }}"
                                {{ old('al_mando_id', $salida->al_mando_id) == $voluntario->id ? 'selected' : '' }}>
                            {{ $voluntario->nombre }} — {{ $voluntario->compania->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('al_mando_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Personal --}}
            <div class="col-md-2">
                <label class="form-label mb-1 small fw-bold">Personal</label>
                <input type="number" name="cantidad_personal" class="form-control form-control-sm" min="1"
                       value="{{ old('cantidad_personal', $salida->cantidad_personal) }}">
            </div>

            {{-- Hora de salida --}}
            <div class="col-md-{{ $salida->llegada_at ? '5' : '5' }}">
                <label class="form-label mb-1 small fw-bold">Hora de salida <span class="text-danger">*</span></label>
                <input type="datetime-local" name="salida_at"
                       class="form-control form-control-sm @error('salida_at') is-invalid @enderror"
                       value="{{ old('salida_at', $salida->salida_at->format('Y-m-d\TH:i')) }}"
                       max="{{ now()->format('Y-m-d\TH:i') }}" required>
                @error('salida_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Hora de llegada (solo si ya tiene llegada registrada) --}}
            @if($salida->llegada_at)
            <div class="col-md-5">
                <label class="form-label mb-1 small fw-bold">Hora de llegada</label>
                <input type="datetime-local" name="llegada_at"
                       class="form-control form-control-sm @error('llegada_at') is-invalid @enderror"
                       value="{{ old('llegada_at', $salida->llegada_at->format('Y-m-d\TH:i')) }}"
                       min="{{ old('salida_at', $salida->salida_at->format('Y-m-d\TH:i')) }}"
                       max="{{ now()->format('Y-m-d\TH:i') }}">
                @error('llegada_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            @endif

            {{-- Observaciones --}}
            <div class="col-12">
                <label class="form-label mb-1 small fw-bold">Observaciones</label>
                <input type="text" name="observaciones" class="form-control form-control-sm"
                       value="{{ old('observaciones', $salida->observaciones) }}" placeholder="Opcional...">
            </div>

        </div>

        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-warning btn-sm">
                <i class="bi bi-floppy me-1"></i>Guardar cambios
            </button>
            <a href="{{ route('salidas.index') }}" class="btn btn-outline-secondary btn-sm">
                Cancelar
            </a>
        </div>

    </div>
</div>

</form>

@endsection

@push('scripts')
<script>
const selectClave   = document.getElementById('selectClave');
const bloqueOficial = document.getElementById('bloqueOficial');
const selectOficial = document.getElementById('selectOficial');

// ── Preview km recorridos en tiempo real ──
(function () {
    const inputLlegada = document.getElementById('inputKmLlegada');
    const previewRec   = document.getElementById('previewKmRecorridos');
    const avisoRec     = document.getElementById('avisoKmRecorridos');
    const avisoNeg     = document.getElementById('avisoKmNegativo');
    const kmSalida     = {{ $salida->km_salida ? (int) $salida->km_salida : 'null' }};

    function recalcular() {
        const llegada = parseFloat(inputLlegada.value);

        if (isNaN(llegada) || inputLlegada.value === '') {
            previewRec.value = '—';
            previewRec.classList.remove('text-danger');
            if (avisoNeg) avisoNeg.style.display = 'none';
            if (avisoRec) avisoRec.style.display = 'none';
            return;
        }

        if (kmSalida !== null && llegada < kmSalida) {
            previewRec.value = '—';
            previewRec.classList.add('text-danger');
            if (avisoNeg) avisoNeg.style.display = '';
            if (avisoRec) avisoRec.style.display = 'none';
        } else if (kmSalida !== null) {
            previewRec.value = (llegada - kmSalida).toLocaleString('es-CL');
            previewRec.classList.remove('text-danger');
            if (avisoNeg) avisoNeg.style.display = 'none';
            if (avisoRec) avisoRec.style.display = '';
        } else {
            previewRec.value = '—';
            previewRec.classList.remove('text-danger');
            if (avisoNeg) avisoNeg.style.display = 'none';
            if (avisoRec) avisoRec.style.display = 'none';
        }
    }

    if (inputLlegada) inputLlegada.addEventListener('input', recalcular);
})();

// ── Mostrar/ocultar oficial autorizante según tipo de clave ──
function actualizarOficial() {
    const tipo = selectClave.options[selectClave.selectedIndex]?.dataset.tipo;
    if (tipo === 'administrativa') {
        bloqueOficial.style.display = '';
        selectOficial.setAttribute('required', 'required');
    } else {
        bloqueOficial.style.display = 'none';
        selectOficial.removeAttribute('required');
        selectOficial.value = '';
    }
}

selectClave.addEventListener('change', actualizarOficial);

// ── Confirmación de al mando con salida activa ──
(function() {
    const check  = document.getElementById('checkConfirmarAlMando');
    const hidden = document.getElementById('confirmarAlMandoActivo');
    if (check) {
        check.addEventListener('change', function() {
            hidden.value = this.checked ? '1' : '0';
        });
    }
})();
</script>
@endpush