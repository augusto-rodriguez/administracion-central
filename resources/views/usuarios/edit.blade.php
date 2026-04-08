@extends('layouts.app')
@section('title', 'Editar Usuario')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-person-gear me-2"></i>Editar Usuario</h4>
    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $usuario->nombre) }}" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $usuario->email) }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nueva Contraseña <span class="text-muted small">(opcional)</span></label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Rol <span class="text-danger">*</span></label>
                    <select name="rol" class="form-select" required>
                        <option value="operador"   {{ $usuario->rol == 'operador'   ? 'selected' : '' }}>Operador</option>
                        <option value="comandante" {{ $usuario->rol == 'comandante' ? 'selected' : '' }}>Comandante</option>
                        <!-- <option value="admin"      {{ $usuario->rol == 'admin'      ? 'selected' : '' }}>Administrador</option> -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Estado</label>
                    <select name="activo" class="form-select">
                        <option value="1" {{ $usuario->activo ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ !$usuario->activo ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Voluntario vinculado <span class="text-muted small">(opcional)</span></label>
                    <select name="voluntario_id" class="form-select">
                        <option value="">Sin vinculación</option>
                        @foreach($voluntarios as $voluntario)
                            <option value="{{ $voluntario->id }}"
                                {{ $usuario->voluntario_id == $voluntario->id ? 'selected' : '' }}>
                                {{ $voluntario->nombre }} — {{ $voluntario->compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-save me-1"></i>Actualizar Usuario
                </button>
            </div>
        </form>
    </div>
</div>
@endsection