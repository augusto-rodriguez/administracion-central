@extends('layouts.app')

@section('title', 'Editar Compañía')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-building me-2"></i>Editar Compañía</h4>
    <a href="{{ route('companias.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('companias.update', $compania) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $compania->nombre) }}">
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Número <span class="text-danger">*</span></label>
                    <input type="number" name="numero" class="form-control @error('numero') is-invalid @enderror"
                           value="{{ old('numero', $compania->numero) }}">
                    @error('numero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Dirección</label>
                    <input type="text" name="direccion" class="form-control"
                           value="{{ old('direccion', $compania->direccion) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Teléfono</label>
                    <input type="text" name="telefono" class="form-control"
                           value="{{ old('telefono', $compania->telefono) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Estado</label>
                    <select name="activa" class="form-select">
                        <option value="1" {{ $compania->activa ? 'selected' : '' }}>Activa</option>
                        <option value="0" {{ !$compania->activa ? 'selected' : '' }}>Inactiva</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-save me-1"></i>Actualizar Compañía
                </button>
            </div>
        </form>
    </div>
</div>
@endsection