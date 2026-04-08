@extends('layouts.app')

@section('title', 'Nuevo Voluntario')

@section('content')

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
                    <input type="text" name="rut"
                           class="form-control @error('rut') is-invalid @enderror"
                           value="{{ old('rut') }}" placeholder="12.345.678-9">
                    @error('rut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Compañía <span class="text-danger">*</span></label>
                    <select name="compania_id"
                            class="form-select @error('compania_id') is-invalid @enderror" required>
                        <option value="">Seleccionar...</option>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}"
                                    {{ old('compania_id') == $compania->id ? 'selected' : '' }}>
                                {{ $compania->numero }} - {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('compania_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                <div class="col-md-6">
                    <label class="form-label fw-bold">Roles adicionales</label>
                    <div class="border rounded p-3">
                        @foreach($rolesDisponibles as $rol)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="roles[]"
                                   value="{{ $rol }}" id="rol_{{ $rol }}"
                                   {{ in_array($rol, old('roles', [])) ? 'checked' : '' }}
                                   onchange="toggleRangoComandante()">
                            <label class="form-check-label" for="rol_{{ $rol }}">
                                {{ ucfirst($rol) }}
                            </label>
                        </div>
                        @endforeach

                        {{-- Selector de rango solo para comandante --}}
                        <div id="rangoComandanteWrap" class="mt-2"
                             style="display: {{ (in_array('comandante', old('roles', [])) || $errors->has('rango_comandante')) ? 'block' : 'none' }};">
                            <label class="form-label fw-bold small">
                                Rango de comandante <span class="text-danger">*</span>
                            </label>
                            <select name="rango_comandante" id="rango_comandante"
                                class="form-select form-select-sm @error('rango_comandante') is-invalid @enderror">
                            <option value="">Seleccionar rango...</option>
                            <option value="1" {{ old('rango_comandante') == '1' ? 'selected' : '' }}
                                    {{ in_array('1', $rangosOcupados) ? 'disabled' : '' }}>
                                1er Comandante {{ in_array('1', $rangosOcupados) ? '(ocupado)' : '' }}
                            </option>
                            <option value="2" {{ old('rango_comandante') == '2' ? 'selected' : '' }}
                                    {{ in_array('2', $rangosOcupados) ? 'disabled' : '' }}>
                                2do Comandante {{ in_array('2', $rangosOcupados) ? '(ocupado)' : '' }}
                            </option>
                            <option value="3" {{ old('rango_comandante') == '3' ? 'selected' : '' }}
                                    {{ in_array('3', $rangosOcupados) ? 'disabled' : '' }}>
                                3er Comandante {{ in_array('3', $rangosOcupados) ? '(ocupado)' : '' }}
                            </option>
                        </select>
                        @error('rango_comandante')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        </div>
                    </div>
                    @error('roles') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
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
function toggleRangoComandante() {
    const checked = document.getElementById('rol_comandante')?.checked;
    const wrap    = document.getElementById('rangoComandanteWrap');
    const select  = document.getElementById('rango_comandante');

    if (wrap) {
        wrap.style.display = checked ? 'block' : 'none';
        if (!checked) select.value = '';
    }
}
</script>
@endpush

@endsection