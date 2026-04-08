@extends('layouts.app')

@section('title', 'Nueva Unidad')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-truck-front me-2"></i>Nueva Unidad</h4>
    <a href="{{ route('unidades.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('unidades.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre') }}" placeholder="Ej: B-2">
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Patente <span class="text-danger">*</span></label>
                    <input type="text" name="patente" class="form-control @error('patente') is-invalid @enderror"
                           value="{{ old('patente') }}" placeholder="Ej: ABCD12">
                    @error('patente') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
               <div class="col-md-6">
                    <label class="form-label fw-bold">Tipo <span class="text-danger">*</span></label>
                    <select name="tipo" class="form-select @error('tipo') is-invalid @enderror">
                        <option value="">Seleccionar tipo...</option>
                        <option value="Bomba" {{ old('tipo') == 'Bomba' ? 'selected' : '' }}>Bomba</option>
                        <option value="Bomba Portaescala" {{ old('tipo') == 'Bomba Portaescala' ? 'selected' : '' }}>Bomba Portaescala</option>
                        <option value="Rescate" {{ old('tipo') == 'Rescate' ? 'selected' : '' }}>Rescate</option>
                        <option value="Rescate Tecnico" {{ old('tipo') == 'Rescate Tecnico' ? 'selected' : '' }}>Rescate Técnico</option>
                        <option value="Hazmat" {{ old('tipo') == 'Hazmat' ? 'selected' : '' }}>Hazmat</option>
                        <option value="Forestal" {{ old('tipo') == 'Forestal' ? 'selected' : '' }}>Forestal</option>
                        <option value="Cisterna" {{ old('tipo') == 'Cisterna' ? 'selected' : '' }}>Cisterna</option>
                    </select>
                    @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Compañía <span class="text-danger">*</span></label>
                    <select name="compania_id" class="form-select @error('compania_id') is-invalid @enderror">
                        <option value="">Seleccionar compañía...</option>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}" {{ old('compania_id') == $compania->id ? 'selected' : '' }}>
                                {{ $compania->numero }} - {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('compania_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2"
                              placeholder="Descripción opcional...">{{ old('descripcion') }}</textarea>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-save me-1"></i>Guardar Unidad
                </button>
            </div>
        </form>
    </div>
</div>
@endsection