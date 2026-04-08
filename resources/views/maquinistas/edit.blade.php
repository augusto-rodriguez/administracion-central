@extends('layouts.app')

@section('title', 'Editar Maquinista')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>Editar Maquinista</h4>
    <a href="{{ route('maquinistas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('maquinistas.update', $maquinista) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre completo <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $maquinista->nombre) }}">
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">RUT <span class="text-danger">*</span></label>
                    <input type="text" name="rut" class="form-control @error('rut') is-invalid @enderror"
                           value="{{ old('rut', $maquinista->rut) }}">
                    @error('rut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Compañía <span class="text-danger">*</span></label>
                    <select name="compania_id" class="form-select @error('compania_id') is-invalid @enderror">
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}" {{ $maquinista->compania_id == $compania->id ? 'selected' : '' }}>
                                {{ $compania->numero }} - {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('compania_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Cargo</label>
                    <input type="text" name="cargo" class="form-control"
                           value="{{ old('cargo', $maquinista->cargo) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Teléfono</label>
                    <input type="text" name="telefono" class="form-control"
                           value="{{ old('telefono', $maquinista->telefono) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Estado</label>
                    <select name="activo" class="form-select">
                        <option value="1" {{ $maquinista->activo ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ !$maquinista->activo ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-save me-1"></i>Actualizar Maquinista
                </button>
            </div>
        </form>
    </div>
</div>
@endsection