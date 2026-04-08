@extends('layouts.app')
@section('title', 'Editar Clave')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-tag me-2"></i>Editar Clave de Salida</h4>
    <a href="{{ route('claves-salida.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>
<div class="card">
    <div class="card-body">
        <form action="{{ route('claves-salida.update', $clave) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Código <span class="text-danger">*</span></label>
                    <input type="text" name="codigo" class="form-control @error('codigo') is-invalid @enderror"
                           value="{{ old('codigo', $clave->codigo) }}">
                    @error('codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Descripción <span class="text-danger">*</span></label>
                    <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror"
                           value="{{ old('descripcion', $clave->descripcion) }}">
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Tipo <span class="text-danger">*</span></label>
                    <select name="tipo" class="form-select">
                        <option value="emergencia" {{ $clave->tipo == 'emergencia' ? 'selected' : '' }}>Emergencia</option>
                        <option value="administrativa" {{ $clave->tipo == 'administrativa' ? 'selected' : '' }}>Administrativa</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Estado</label>
                    <select name="activa" class="form-select">
                        <option value="1" {{ $clave->activa ? 'selected' : '' }}>Activa</option>
                        <option value="0" {{ !$clave->activa ? 'selected' : '' }}>Inactiva</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-save me-1"></i>Actualizar Clave
                </button>
            </div>
        </form>
    </div>
</div>
@endsection