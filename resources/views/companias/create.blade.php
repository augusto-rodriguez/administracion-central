@extends('layouts.app')

@section('title', 'Nueva Compañía')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-building me-2"></i>Nueva Compañía</h4>
    <a href="{{ route('companias.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('companias.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre') }}" placeholder="Ej: Primera Compañía de Bomberos">
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Número <span class="text-danger">*</span></label>
                    <input type="number" name="numero" class="form-control @error('numero') is-invalid @enderror"
                           value="{{ old('numero') }}" placeholder="Ej: 1">
                    @error('numero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Dirección</label>
                    <input type="text" name="direccion" class="form-control"
                           value="{{ old('direccion') }}" placeholder="Ej: Av. Principal 123">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Teléfono</label>
                    <input type="text" name="telefono" class="form-control"
                           value="{{ old('telefono') }}" placeholder="Ej: +56 9 1234 5678">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-save me-1"></i>Guardar Compañía
                </button>
            </div>
        </form>
    </div>
</div>
@endsection