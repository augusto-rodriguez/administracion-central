@extends('layouts.app')

@section('title', 'Nuevo Cargo')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-award me-2"></i>Nuevo Cargo</h4>
    <a href="{{ route('cargos.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('cargos.store') }}" method="POST">
            @csrf
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre') }}" placeholder="Ej: Capitán" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Tipo <span class="text-danger">*</span></label>
                    <select name="tipo" id="tipo"
                            class="form-select @error('tipo') is-invalid @enderror"
                            required>
                        <option value="">Seleccionar...</option>
                        <option value="compania" {{ old('tipo') === 'compania' ? 'selected' : '' }}>
                            Cargo de Compañía
                        </option>
                        <option value="general" {{ old('tipo') === 'general' ? 'selected' : '' }}>
                            Cargo General del Cuerpo
                        </option>
                    </select>
                    @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">Estado</label>
                    <select name="activo" class="form-select">
                        <option value="1" {{ old('activo', '1') == '1' ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ old('activo') == '0' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea name="descripcion" rows="2"
                              class="form-control @error('descripcion') is-invalid @enderror"
                              placeholder="Descripción opcional del cargo">{{ old('descripcion') }}</textarea>
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">¿Cargo único?</label>
                    <select name="es_unico" class="form-select">
                        <option value="1" {{ old('es_unico', '1') == '1' ? 'selected' : '' }}>
                            Sí — solo un titular a la vez
                        </option>
                        <option value="0" {{ old('es_unico', '1') == '0' ? 'selected' : '' }}>
                            No — pueden haber varios titulares
                        </option>
                    </select>
                    <div class="form-text">
                        Ej: "Capitán Inspector de Comandancia" no es único ya que pueden haber varios.
                    </div>
                </div>

            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-save me-1"></i>Guardar Cargo
                </button>
            </div>
        </form>
    </div>
</div>

@endsection