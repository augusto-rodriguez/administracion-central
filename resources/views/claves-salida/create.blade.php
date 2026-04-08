@extends('layouts.app')
@section('title', 'Nueva Clave')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-tag me-2"></i>Nueva Clave de Salida</h4>
    <a href="{{ route('claves-salida.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>
<div class="card">
    <div class="card-body">
        <form action="{{ route('claves-salida.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Código <span class="text-danger">*</span></label>
                    <input type="text" name="codigo" class="form-control @error('codigo') is-invalid @enderror"
                           value="{{ old('codigo') }}" placeholder="Ej: 10-19">
                    @error('codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Descripción <span class="text-danger">*</span></label>
                    <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror"
                           value="{{ old('descripcion') }}" placeholder="Descripción de la clave">
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Tipo <span class="text-danger">*</span></label>
                    <select name="tipo" class="form-select @error('tipo') is-invalid @enderror">
                        <option value="">Seleccionar...</option>
                        <option value="emergencia" {{ old('tipo') == 'emergencia' ? 'selected' : '' }}>Emergencia</option>
                        <option value="administrativa" {{ old('tipo') == 'administrativa' ? 'selected' : '' }}>Administrativa</option>
                    </select>
                    @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-save me-1"></i>Guardar Clave
                </button>
            </div>
        </form>
    </div>
</div>
@endsection