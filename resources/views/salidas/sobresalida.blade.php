@extends('layouts.app')
@section('title', 'Registrar Sobresalida')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-arrow-right-circle me-2 text-warning"></i>Registrar Sobresalida
        — {{ $salida->unidad->nombre }}
    </h4>
    <a href="{{ route('salidas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

{{-- Aviso contextual: qué está haciendo la unidad ahora --}}
<div class="alert alert-warning d-flex align-items-start gap-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-5 mt-1 flex-shrink-0"></i>
    <div>
        <strong>La unidad está actualmente en servicio.</strong><br>
        <span class="text-muted">
            Clave activa:
            <span class="badge {{ $salida->claveSalida->tipo === 'emergencia' ? 'bg-danger' : 'bg-primary' }} ms-1">
                {{ $salida->claveSalida->codigo }}
            </span>
            {{ $salida->claveSalida->descripcion }}
            &mdash; {{ $salida->direccion }}<br>
            Salida: {{ $salida->salida_at->format('d/m/Y H:i') }}
            @if($salida->esSobresalida())
                &mdash; <em class="text-warning">sobresalida {{ $raiz->sobresalidas()->count() }} de la cadena iniciada el {{ $raiz->salida_at->format('d/m/Y H:i') }}</em>
            @endif
        </span>
    </div>
</div>

{{-- Datos no editables: unidad y conductor heredados --}}
<div class="card mb-3">
    <div class="card-header bg-secondary-subtle fw-bold">
        <i class="bi bi-lock me-2"></i>Datos heredados de la salida activa
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold text-muted">Unidad</label>
                <p class="form-control-plaintext">
                    <strong>{{ $salida->unidad->nombre }}</strong>
                    — {{ $salida->unidad->compania->nombre }}
                </p>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold text-muted">Conductor</label>
                <p class="form-control-plaintext">{{ $salida->conductor_nombre }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold text-muted">Km (heredado automáticamente)</label>
                <p class="form-control-plaintext">
                    @if($salida->km_salida)
                        {{ number_format((int) $salida->km_salida, 0, ',', '.') }} km
                        <span class="text-muted small">— se heredará al nuevo tramo</span>
                    @else
                        <span class="text-muted">Sin km registrado</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Formulario de sobresalida --}}
<form action="{{ route('salidas.sobresalida.store', $salida) }}" method="POST">
@csrf
<input type="hidden" name="confirmar_al_mando_activo" id="confirmarAlMandoActivo" value="0">

{{-- Aviso de voluntario al mando con salida activa --}}
@if(session('warning_al_mando'))
<div class="alert alert-warning d-flex align-items-start gap-2 mb-3">
    <i class="bi bi-exclamation-triangle-fill fs-5 mt-1 flex-shrink-0"></i>
    <div>
        <strong>{{ session('warning_al_mando') }}</strong>
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="checkConfirmarAlMando">
            <label class="form-check-label" for="checkConfirmarAlMando">
                Entendido, deseo registrar la sobresalida de todas formas
            </label>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header bg-warning-subtle fw-bold text-warning-emphasis">
        <i class="bi bi-arrow-right-circle me-2"></i>Datos del nuevo destino
    </div>
    <div class="card-body">
        <div class="row g-3">

            {{-- Clave --}}
            <div class="col-md-8">
                <label class="form-label fw-bold">Clave <span class="text-danger">*</span></label>
                <select name="clave_salida_id" id="selectClave"
                        class="form-select @error('clave_salida_id') is-invalid @enderror" required>
                    <option value="">Seleccionar clave...</option>
                    <optgroup label="🚨 Emergencias">
                        @foreach($claves->where('tipo', 'emergencia') as $clave)
                            <option value="{{ $clave->id }}" data-tipo="emergencia"
                                    {{ old('clave_salida_id') == $clave->id ? 'selected' : '' }}>
                                {{ $clave->codigo }} — {{ $clave->descripcion }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="⚙️ Administrativas">
                        @foreach($claves->where('tipo', 'administrativa') as $clave)
                            <option value="{{ $clave->id }}" data-tipo="administrativa"
                                    {{ old('clave_salida_id') == $clave->id ? 'selected' : '' }}>
                                {{ $clave->codigo }} — {{ $clave->descripcion }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
                @error('clave_salida_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Oficial autorizante (solo administrativas) --}}
            <div class="col-md-4" id="bloqueOficial" style="display:none">
                <label class="form-label fw-bold">
                    Oficial autorizante <span class="text-danger">*</span>
                </label>
                <select name="oficial_id" id="selectOficial"
                        class="form-select @error('oficial_id') is-invalid @enderror">
                    <option value="">Seleccionar oficial...</option>
                    @foreach($oficiales as $oficial)
                        <option value="{{ $oficial->id }}"
                                {{ old('oficial_id') == $oficial->id ? 'selected' : '' }}>
                            {{ $oficial->nombre }} — {{ $oficial->compania->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('oficial_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Dirección --}}
            <div class="col-12">
                <label class="form-label fw-bold">Dirección / Lugar nuevo <span class="text-danger">*</span></label>
                <input type="text" name="direccion"
                       class="form-control @error('direccion') is-invalid @enderror"
                       value="{{ old('direccion') }}"
                       placeholder="Ej: Bencinera Shell, Ruta 5 Sur km 512"
                       required autofocus>
                @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Al Mando --}}
            <div class="col-md-6">
                <label class="form-label fw-bold">Voluntario al Mando</label>
                <select name="al_mando_id"
                        class="form-select @error('al_mando_id') is-invalid @enderror">
                    <option value="">— Sin voluntario al mando (cuartelero solo) —</option>
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
                       value="{{ old('cantidad_personal', $salida->cantidad_personal) }}"
                       placeholder="Opcional">
            </div>

            {{-- Hora de la sobresalida --}}
            <div class="col-md-4">
                <label class="form-label fw-bold">
                    <i class="bi bi-clock me-1"></i>Hora de la sobresalida <span class="text-danger">*</span>
                </label>
                <input type="time" id="inputHora" class="form-control">
                <input type="hidden" name="salida_at" id="salidaAtOculto">
                <div class="text-muted small mt-1" id="horaIndicador" style="font-size:11px">
                    <i class="bi bi-arrow-repeat me-1"></i>Actualizando...
                </div>
            </div>

            {{-- Observaciones --}}
            <div class="col-md-8">
                <label class="form-label fw-bold">Observaciones</label>
                <input type="text" name="observaciones" class="form-control"
                       value="{{ old('observaciones') }}" placeholder="Opcional...">
            </div>

        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-warning">
                <i class="bi bi-arrow-right-circle me-1"></i>Registrar Sobresalida
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
// ── Reloj de hora de sobresalida ──
(function () {
    const inputHora     = document.getElementById('inputHora');
    const oculto        = document.getElementById('salidaAtOculto');
    const indicador     = document.getElementById('horaIndicador');
    let modificada      = false;

    function hhMM() {
        const d = new Date();
        return String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
    }
    function fechaHoy() {
        const d = new Date();
        return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    }
    function sincronizar(hora) {
        oculto.value = `${fechaHoy()} ${hora}:00`;
    }
    function tick() {
        if (modificada) return;
        const ahora = hhMM();
        inputHora.value = ahora;
        sincronizar(ahora);
        indicador.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Hora actual (se actualiza sola)';
    }

    tick();
    setInterval(tick, 30000);

    inputHora.addEventListener('change', function () {
        const ahora = hhMM();
        if (this.value > ahora) {
            this.value = ahora;
            modificada = false;
            sincronizar(ahora);
            indicador.innerHTML = '<i class="bi bi-exclamation-triangle me-1 text-warning"></i>No puedes seleccionar hora futura';
            return;
        }
        modificada = true;
        sincronizar(this.value);
        if (this.value === ahora) {
            modificada = false;
            indicador.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Hora actual (se actualiza sola)';
        } else {
            indicador.innerHTML = `<i class="bi bi-clock-history me-1 text-warning"></i><strong>Hora ajustada: ${this.value}</strong>`;
        }
    });
})();

// ── Mostrar/ocultar oficial según tipo de clave ──
(function () {
    const selectClave   = document.getElementById('selectClave');
    const bloqueOficial = document.getElementById('bloqueOficial');
    const selectOficial = document.getElementById('selectOficial');

    function actualizar() {
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

    selectClave.addEventListener('change', actualizar);
    actualizar();
})();

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