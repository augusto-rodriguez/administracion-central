@extends('layouts.app')
@section('title', 'Nuevo Usuario')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-person-plus me-2"></i>Nuevo Usuario</h4>
    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="alert alert-info d-flex gap-2 mb-4">
    <i class="bi bi-info-circle-fill fs-5 mt-1"></i>
    <div>
        <strong>¿El usuario es un voluntario?</strong>
        Si la persona ya está registrada como voluntario, selecciónalo primero
        y sus datos se cargarán automáticamente.
        Si no es voluntario, completa el formulario de forma normal.
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('usuarios.store') }}" method="POST">
            @csrf

            {{-- Selector voluntario --}}
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label class="form-label fw-bold">
                        <i class="bi bi-person-badge me-1"></i>
                        Vincular a voluntario existente
                        <span class="text-muted small">(opcional)</span>
                    </label>
                    <select id="selectVoluntario" name="voluntario_id" class="form-select">
                        <option value="">— Sin vinculación / No es voluntario —</option>
                        @foreach($voluntarios as $voluntario)
                            @php
                                $esComandante  = $voluntario->roles->where('activo', true)->where('rol', 'comandante')->isNotEmpty();
                                $rangoComandante = $voluntario->roles->where('activo', true)->firstWhere('rol', 'comandante')?->rango;
                            @endphp
                            <option value="{{ $voluntario->id }}"
                                    data-nombre="{{ $voluntario->nombre }}"
                                    data-email="{{ $voluntario->email ?? '' }}"
                                    data-es-comandante="{{ $esComandante ? '1' : '0' }}"
                                    data-rango="{{ $rangoComandante ?? '' }}"
                                    {{ old('voluntario_id') == $voluntario->id ? 'selected' : '' }}>
                                {{ $voluntario->nombre }} — {{ $voluntario->compania->nombre }}
                                @if($esComandante)
                                    ({{ $rangoComandante }}° Comandante)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Al seleccionar un voluntario se autocompletarán nombre y email.</div>
                </div>
            </div>

            {{-- Alerta comandante detectado --}}
            <div id="alertaComandante" class="alert alert-success d-flex gap-2 align-items-center mb-4"
                 style="display:none !important;">
                <i class="bi bi-shield-fill-check fs-5"></i>
                <div id="alertaComandanteTexto"></div>
            </div>

            <hr class="mb-4">

            {{-- Datos del usuario --}}
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="inputNombre" name="nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre') }}" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                    <input type="email" id="inputEmail" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Contraseña <span class="text-danger">*</span></label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Confirmar Contraseña <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Rol <span class="text-danger">*</span></label>
                    <select id="selectRol" name="rol"
                            class="form-select @error('rol') is-invalid @enderror" required>
                        <option value="operador"   {{ old('rol') == 'operador'   ? 'selected' : '' }}>Operador</option>
                        <option value="comandante" {{ old('rol') == 'comandante' ? 'selected' : '' }}>Comandante</option>
                    </select>
                    @error('rol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-save me-1"></i>Crear Usuario
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const ordinal = { '1': '1er', '2': '2do', '3': '3er' };

document.getElementById('selectVoluntario').addEventListener('change', function () {
    const selected     = this.options[this.selectedIndex];
    const nombre       = selected.dataset.nombre       ?? '';
    const email        = selected.dataset.email        ?? '';
    const esComandante = selected.dataset.esComandante === '1';
    const rango        = selected.dataset.rango        ?? '';

    const alerta    = document.getElementById('alertaComandante');
    const alertaTxt = document.getElementById('alertaComandanteTexto');
    const selectRol = document.getElementById('selectRol');

    if (this.value) {
        document.getElementById('inputNombre').value = nombre;
        if (email) document.getElementById('inputEmail').value = email;

        if (esComandante) {
            // Seleccionar rol comandante automáticamente
            selectRol.value = 'comandante';
            selectRol.setAttribute('readonly', true);

            // Mostrar alerta
            const ord = ordinal[rango] ?? rango + '°';
            alertaTxt.innerHTML = `
                <strong>Comandante detectado.</strong>
                Este voluntario está registrado como
                <strong>${ord} Comandante</strong>.
                Se ha asignado automáticamente el rol de Comandante.
            `;
            alerta.style.removeProperty('display');
        } else {
            selectRol.value = 'operador';
            selectRol.removeAttribute('readonly');
            alerta.style.display = 'none';
        }
    } else {
        document.getElementById('inputNombre').value = '';
        document.getElementById('inputEmail').value  = '';
        selectRol.value = 'operador';
        selectRol.removeAttribute('readonly');
        alerta.style.display = 'none';
    }
});
</script>
@endpush

@endsection