@extends('layouts.app')
@section('title', 'Nuevo Cuartelero')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-person-plus me-2"></i>Nuevo Cuartelero</h4>
    <a href="{{ route('cuarteleros.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('cuarteleros.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre') }}" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">RUT</label>
                    <input type="text" name="rut"
                           class="form-control @error('rut') is-invalid @enderror"
                           value="{{ old('rut') }}" placeholder="12.345.678-9">
                    @error('rut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Teléfono</label>
                    <input type="text" name="telefono" class="form-control"
                           value="{{ old('telefono') }}" placeholder="+56 9 1234 5678">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Compañía <span class="text-danger">*</span></label>
                    <select name="compania_id"
                            class="form-select @error('compania_id') is-invalid @enderror" required>
                        <option value="">Seleccionar...</option>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}"
                                {{ old('compania_id') == $compania->id ? 'selected' : '' }}>
                                {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('compania_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle me-1"></i>
                Al crear el cuartelero se autorizarán automáticamente todas las unidades activas de su compañía.
                Puedes modificarlas desde el detalle del cuartelero.
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-save me-1"></i>Registrar Cuartelero
                </button>
            </div>
        </form>
    </div>
</div>
@endsection