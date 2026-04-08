@extends('layouts.app')
@section('title', 'Editar Cuartelero')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-person-gear me-2"></i>Editar Cuartelero</h4>
    <a href="{{ route('cuarteleros.show', $cuartelero) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('cuarteleros.update', $cuartelero) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $cuartelero->nombre) }}" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">RUT</label>
                    <input type="text" name="rut"
                           class="form-control @error('rut') is-invalid @enderror"
                           value="{{ old('rut', $cuartelero->rut) }}">
                    @error('rut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Teléfono</label>
                    <input type="text" name="telefono" class="form-control"
                           value="{{ old('telefono', $cuartelero->telefono) }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Compañía <span class="text-danger">*</span></label>
                    <select name="compania_id" class="form-select" required>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}"
                                {{ $cuartelero->compania_id == $compania->id ? 'selected' : '' }}>
                                {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Estado</label>
                    <select name="activo" class="form-select">
                        <option value="1" {{ $cuartelero->activo ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ !$cuartelero->activo ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-save me-1"></i>Actualizar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection