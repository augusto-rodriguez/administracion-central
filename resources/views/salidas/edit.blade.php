@extends('layouts.app')
@section('title', 'Editar Salida')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-pencil-square me-2"></i>Editar Salida — {{ $salida->unidad->nombre }}
    </h4>
    <a href="{{ route('salidas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

{{-- Aviso de ventana de edición --}}
@php
    $minutosRestantes = (int) now()->diffInMinutes($salida->salida_at->addHours(12), false);
    $horasRestantes   = floor($minutosRestantes / 60);
    $minsRestantes    = $minutosRestantes % 60;
@endphp
<div class="alert alert-warning mb-4">
    <i class="bi bi-clock-history me-2"></i>
    Puedes editar esta salida durante las primeras 12 horas. 
    Tiempo restante: <strong>{{ $horasRestantes }}h {{ $minsRestantes }}m</strong>.
</div>

@if(!$kmYConductorEditables)
<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle me-2"></i>
    Esta salida tiene <strong>sobresalidas encadenadas</strong>. Puedes editar clave, dirección,
    al mando, hora y observaciones. El km de llegada también es editable, pero ten en cuenta
    que se propagará a todos los tramos de la cadena.
</div>
@endif

<div class="card">
    <div class="card-header bg-warning-subtle fw-bold">
        <i class="bi bi-info-circle me-2"></i>Datos no editables
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold text-muted">Unidad</label>
                <p class="form-control-plaintext">
                    {{ $salida->unidad->nombre }} — {{ $salida->unidad->compania->nombre }}
                </p>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold text-muted">Conductor</label>
                <p class="form-control-plaintext">{{ $salida->conductor_nombre }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Kilometraje --}}
{{-- El form envuelve tanto la tarjeta de km como la de datos editables --}}
<form action="{{ route('salidas.update', $salida) }}" method="POST">
@csrf
@method('PUT')

<div class="card mt-3 border-warning">
    <div class="card-header bg-warning bg-opacity-10 fw-bold text-warning-emphasis">
        <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>Kilometraje
    </div>
    <div class="card-body">

        @if($salida->km_salida)
        <div class="alert alert-warning py-2 mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Revisa bien antes de guardar.</strong>
            El km de llegada debe ser mayor o igual al de salida
            (<strong>{{ number_format((int) $salida->km_salida, 0, ',', '.') }} km</strong>).
            El cálculo de km recorridos se actualizará automáticamente.
        </div>
        @endif

        <div class="row g-3 align-items-start">

            {{-- Km Salida — solo lectura --}}
            <div class="col-md-4">
                <label class="form-label fw-bold text-muted">Km Salida</label>
                <div class="input-group">
                    <input type="text" class="form-control bg-light"
                           value="{{ $salida->km_salida ? number_format((int) $salida->km_salida, 0, ',', '.') : '—' }}"
                           readonly disabled>
                    <span class="input-group-text text-muted">km</span>
                </div>
            </div>

            {{-- Km Llegada — editable --}}
            <div class="col-md-4">
                <label class="form-label fw-bold">Km Llegada</label>
                <div class="input-group">
                    <input type="number"
                           name="km_llegada"
                           id="inputKmLlegada"
                           class="form-control @error('km_llegada') is-invalid @enderror"
                           min="0" step="1"
                           value="{{ old('km_llegada', $salida->km_llegada ? (int) $salida->km_llegada : '') }}"
                           placeholder="{{ $salida->km_llegada ? '' : 'Sin registrar aún' }}">
                    <span class="input-group-text text-muted">km</span>
                    @error('km_llegada') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div id="avisoKmNegativo" class="text-danger small mt-1" style="display:none">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    Menor al km de salida ({{ $salida->km_salida ? number_format((int) $salida->km_salida, 0, ',', '.') : '—' }} km)
                </div>
                @if(!$salida->km_llegada)
                <div class="text-muted small mt-1">
                    <i class="bi bi-info-circle me-1"></i>Esta salida aún no tiene km de llegada registrado.
                </div>
                @endif
            </div>

            {{-- Km Recorridos — preview calculado --}}
            <div class="col-md-4">
                <label class="form-label fw-bold text-muted">Km Recorridos (calculado)</label>
                <div class="input-group">
                    <input type="text" id="previewKmRecorridos" class="form-control fw-bold bg-light" readonly
                           value="{{ ($salida->km_salida && $salida->km_llegada) ? number_format((int)$salida->km_llegada - (int)$salida->km_salida, 0, ',', '.') : '—' }}">
                    <span class="input-group-text text-muted">km</span>
                </div>
                <div id="avisoKmRecorridos" class="text-muted small mt-1" style="display:none">
                    <i class="bi bi-arrow-repeat me-1"></i>Actualizado en tiempo real
                </div>
            </div>

        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-pencil me-2"></i>Datos editables
    </div>
    <div class="card-body">

            <div class="row g-3">

                {{-- Clave --}}
                <div class="col-md-8">
                    <label class="form-label fw-bold">Clave <span class="text-danger">*</span></label>
                    <select name="clave_salida_id" id="selectClave"
                            class="form-select @error('clave_salida_id') is-invalid @enderror" required>
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
                    <label class="form-label fw-bold">
                        Oficial autorizante <span class="text-danger">*</span>
                    </label>
                    <select name="oficial_id" id="selectOficial"
                            class="form-select @error('oficial_id') is-invalid @enderror">
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
                    <label class="form-label fw-bold">Dirección / Lugar <span class="text-danger">*</span></label>
                    <input type="text" name="direccion"
                           class="form-control @error('direccion') is-invalid @enderror"
                           value="{{ old('direccion', $salida->direccion) }}" required>
                    @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Al Mando --}}
                <div class="col-md-6">
                    <label class="form-label fw-bold">Voluntario al Mando <span class="text-danger">*</span></label>
                    <select name="al_mando_id"
                            class="form-select @error('al_mando_id') is-invalid @enderror" required>
                        <option value="">Seleccionar voluntario...</option>
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
                <div class="col-md-3">
                    <label class="form-label fw-bold">Cantidad Personal</label>
                    <input type="number" name="cantidad_personal" class="form-control" min="1"
                           value="{{ old('cantidad_personal', $salida->cantidad_personal) }}">
                </div>

                {{-- Hora de salida --}}
                <div class="col-md-4">
                    <label class="form-label fw-bold">Hora de salida <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="salida_at"
                           class="form-control @error('salida_at') is-invalid @enderror"
                           value="{{ old('salida_at', $salida->salida_at->format('Y-m-d\TH:i')) }}"
                           max="{{ now()->format('Y-m-d\TH:i') }}" required>
                    @error('salida_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Observaciones --}}
                <div class="col-md-8">
                    <label class="form-label fw-bold">Observaciones</label>
                    <input type="text" name="observaciones" class="form-control"
                           value="{{ old('observaciones', $salida->observaciones) }}" placeholder="Opcional...">
                </div>

            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-floppy me-1"></i>Guardar cambios
                </button>
                <a href="{{ route('salidas.index') }}" class="btn btn-outline-secondary">
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
            // Sin km_salida: no se puede calcular recorridos, solo mostrar el valor
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
</script>
@endpush