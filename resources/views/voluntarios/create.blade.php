@extends('layouts.app')

@section('title', 'Nuevo Voluntario')

@section('content')

@php $esCapitan = auth()->user()->esCapitanCia(); @endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-person-plus me-2"></i>Nuevo Voluntario</h4>
    <a href="{{ route('voluntarios.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('voluntarios.store') }}" method="POST">
            @csrf
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre completo <span class="text-danger">*</span></label>
                    <input type="text" name="nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre') }}" placeholder="Nombre completo" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">RUT</label>
                    <input type="text" name="rut" id="rut"
                        class="form-control @error('rut') is-invalid @enderror"
                        value="{{ old('rut') }}" placeholder="12.345.678-9"
                        maxlength="12">
                    @error('rut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Compañía <span class="text-danger">*</span></label>
                    @if($esCapitan)
                        {{-- Capitán: compañía fija, no editable --}}
                        <input type="text" class="form-control"
                               value="{{ $companias->first()->numero }} - {{ $companias->first()->nombre }}"
                               disabled>
                        <input type="hidden" name="compania_id" value="{{ $companias->first()->id }}">
                    @else
                        <select name="compania_id" id="compania_id"
                                class="form-select @error('compania_id') is-invalid @enderror"
                                onchange="actualizarCargosDisponibles(this.value)"
                                required>
                            <option value="">Seleccionar...</option>
                            @foreach($companias as $compania)
                                <option value="{{ $compania->id }}"
                                        {{ old('compania_id') == $compania->id ? 'selected' : '' }}>
                                    {{ $compania->numero }} - {{ $compania->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('compania_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @endif
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Teléfono</label>
                    <input type="text" name="telefono" class="form-control"
                           value="{{ old('telefono') }}" placeholder="+56 9 1234 5678">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email') }}" placeholder="correo@ejemplo.cl">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Clave Actual</label>
                    <input type="text" name="clave_actual"
                        class="form-control @error('clave_actual') is-invalid @enderror"
                        value="{{ old('clave_actual') }}"
                        placeholder=""
                        maxlength="5">
                    @error('clave_actual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Fecha de Ingreso</label>
                    <input type="date" name="fecha_ingreso"
                           class="form-control @error('fecha_ingreso') is-invalid @enderror"
                           value="{{ old('fecha_ingreso') }}">
                    @error('fecha_ingreso') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Roles --}}
                <div class="col-md-6">
                    <label class="form-label fw-bold">Roles</label>
                    <div class="border rounded p-3">
                        @foreach($rolesDisponibles as $rol)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="roles[]"
                                   value="{{ $rol }}" id="rol_{{ $rol }}"
                                   {{ in_array($rol, old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="rol_{{ $rol }}">
                                {{ ucfirst($rol) }}
                                @if($rol === 'oficial')
                                    <span id="oficial_auto_tip" class="text-muted small ms-1" style="display:none">
                                        (asignado por cargo)
                                    </span>
                                @endif
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('roles') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Cargo --}}
                <div class="col-md-6">
                    <label class="form-label fw-bold">Cargo</label>
                    <div class="border rounded p-3">

                        <div class="mb-3">
                            <label class="form-label small text-muted">Cargo de Compañía</label>
                            <select name="cargo_compania_id" id="cargo_compania_select"
                                    class="form-select form-select-sm @error('cargo_id') is-invalid @enderror"
                                    onchange="sincronizarCargo(this, 'compania')">
                                <option value="">Sin cargo de compañía</option>
                                @foreach($cargosCompania as $cargo)
                                    <option value="{{ $cargo->id }}"
                                            {{ old('cargo_compania_id') == $cargo->id ? 'selected' : '' }}>
                                        {{ $cargo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label small text-muted">Cargo General del Cuerpo</label>
                            <select name="cargo_general_id" id="cargo_general_select"
                                    class="form-select form-select-sm @error('cargo_id') is-invalid @enderror"
                                    onchange="sincronizarCargo(this, 'general')">
                                <option value="">Sin cargo general</option>
                                @foreach($cargosGenerales as $cargo)
                                    @php $ocupado = in_array($cargo->id, $cargosGeneralesOcupados); @endphp
                                    <option value="{{ $cargo->id }}"
                                            {{ old('cargo_general_id') == $cargo->id ? 'selected' : '' }}
                                            {{ $ocupado ? 'disabled' : '' }}>
                                        {{ $cargo->nombre }}{{ $ocupado ? ' (ocupado)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @error('cargo_id')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror

                        <div class="form-text mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Solo se puede asignar un cargo a la vez.
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-save me-1"></i>Guardar Voluntario
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const cargosCompaniaOcupados = @json($cargosCompaniaOcupados);
    const esCapitan              = {{ $esCapitan ? 'true' : 'false' }};

    function sincronizarCargo(origen, tipo) {
        const selectCompania = document.getElementById('cargo_compania_select');
        const selectGeneral  = document.getElementById('cargo_general_select');
        const checkOficial   = document.getElementById('rol_oficial');
        const tipOficial     = document.getElementById('oficial_auto_tip');

        if (tipo === 'compania') {
            selectGeneral.value = '';
        } else {
            selectCompania.value = '';
        }

        const hayCargoSeleccionado = selectCompania.value || selectGeneral.value;
        if (hayCargoSeleccionado) {
            checkOficial.checked     = true;
            checkOficial.disabled    = true;
            tipOficial.style.display = 'inline';
        } else {
            checkOficial.disabled    = false;
            tipOficial.style.display = 'none';
        }
    }

    function actualizarCargosDisponibles(companiaId) {
        const select = document.getElementById('cargo_compania_select');
        Array.from(select.options).forEach(option => {
            if (!option.value) return;
            const ocupados    = cargosCompaniaOcupados[option.value] ?? [];
            const estaOcupado = ocupados.includes(parseInt(companiaId));
            option.disabled   = estaOcupado;
            option.text       = option.text.replace(' (ocupado)', '');
            if (estaOcupado) option.text += ' (ocupado)';
            if (estaOcupado && option.selected) {
                option.selected = false;
                document.getElementById('rol_oficial').disabled          = false;
                document.getElementById('oficial_auto_tip').style.display = 'none';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const selectCompania = document.getElementById('cargo_compania_select');
        const selectGeneral  = document.getElementById('cargo_general_select');
        const checkOficial   = document.getElementById('rol_oficial');
        const tipOficial     = document.getElementById('oficial_auto_tip');

        if (selectCompania.value || selectGeneral.value) {
            checkOficial.checked     = true;
            checkOficial.disabled    = true;
            tipOficial.style.display = 'inline';
        }

        // Para capitán la compañía es fija, cargar cargos ocupados directamente
        if (esCapitan) {
            const hiddenCompania = document.querySelector('input[name="compania_id"]');
            if (hiddenCompania) actualizarCargosDisponibles(hiddenCompania.value);
        } else {
            const companiaId = document.getElementById('compania_id')?.value;
            if (companiaId) actualizarCargosDisponibles(companiaId);
        }
    });

    document.getElementById('rut').addEventListener('input', function () {
        let val = this.value.replace(/\./g, '').replace(/-/g, '').replace(/[^0-9kK]/g, '');
        if (val.length === 0) { this.value = ''; return; }
        const dv   = val.slice(-1).toUpperCase();
        let cuerpo = val.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        this.value = cuerpo ? `${cuerpo}-${dv}` : dv;
    });
</script>
@endpush

@endsection