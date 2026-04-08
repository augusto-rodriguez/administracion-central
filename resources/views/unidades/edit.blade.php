@extends('layouts.app')

@section('title', 'Editar Unidad')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-truck-front me-2"></i>Editar Unidad</h4>
    <a href="{{ route('unidades.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('unidades.update', $unidad) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $unidad->nombre) }}">
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Patente <span class="text-danger">*</span></label>
                    <input type="text" name="patente" class="form-control @error('patente') is-invalid @enderror"
                           value="{{ old('patente', $unidad->patente) }}">
                    @error('patente') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tipo <span class="text-danger">*</span></label>
                    <select name="tipo" class="form-select">
                        <option value="Bomba" {{ $unidad->tipo == 'Bomba' ? 'selected' : '' }}>Bomba</option>
                        <option value="Bomba Portaescala" {{ $unidad->tipo == 'Bomba Portaescala' ? 'selected' : '' }}>Bomba Portaescala</option>
                        <option value="Rescate" {{ $unidad->tipo == 'Rescate' ? 'selected' : '' }}>Rescate</option>
                        <option value="Rescate Tecnico" {{ $unidad->tipo == 'Rescate Tecnico' ? 'selected' : '' }}>Rescate Técnico</option>
                        <option value="Hazmat" {{ $unidad->tipo == 'Hazmat' ? 'selected' : '' }}>Hazmat</option>
                        <option value="Forestal" {{ $unidad->tipo == 'Forestal' ? 'selected' : '' }}>Forestal</option>
                        <option value="Cisterna" {{ $unidad->tipo == 'Cisterna' ? 'selected' : '' }}>Cisterna</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Compañía <span class="text-danger">*</span></label>
                    <select name="compania_id" class="form-select @error('compania_id') is-invalid @enderror">
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}" {{ $unidad->compania_id == $compania->id ? 'selected' : '' }}>
                                {{ $compania->numero }} - {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('compania_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion', $unidad->descripcion) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Estado</label>
                    <select name="activa" class="form-select">
                        <option value="1" {{ $unidad->activa ? 'selected' : '' }}>Activa</option>
                        <option value="0" {{ !$unidad->activa ? 'selected' : '' }}>Inactiva</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-save me-1"></i>Actualizar Unidad
                </button>
            </div>
        </form>
    </div>
</div>
@endsection