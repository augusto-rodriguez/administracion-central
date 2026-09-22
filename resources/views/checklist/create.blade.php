@extends('layouts.app')

@section('title', 'Nueva Inspección')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Nueva Inspección</h4>
    <a href="{{ route('checklist.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-4">
                    Selecciona la unidad que vas a inspeccionar. La plantilla se asignará automáticamente si la unidad tiene una configurada.
                </p>

                <form action="{{ route('checklist.store') }}" method="POST" id="formInspeccion">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold">Unidad a inspeccionar <span class="text-danger">*</span></label>

                        @if($unidades->isEmpty())
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                No tienes unidades asignadas. Contacta al administrador.
                            </div>
                        @else
                            <div class="row g-2">
                                @foreach($unidades as $unidad)
                                    <div class="col-6">
                                        <input type="radio" class="btn-check unidad-radio" name="unidad_id"
                                               id="unidad_{{ $unidad->id }}" value="{{ $unidad->id }}"
                                               data-plantilla-id="{{ $unidad->checklist_plantilla_id ?? '' }}"
                                               {{ old('unidad_id') == $unidad->id ? 'checked' : '' }}>
                                        <label class="btn btn-outline-dark w-100 py-3 text-start" for="unidad_{{ $unidad->id }}">
                                            <i class="bi bi-truck me-1"></i>
                                            <span class="fw-bold">{{ $unidad->nombre }}</span>
                                            @if($unidad->patente)
                                                <br><small class="text-muted">{{ $unidad->patente }}</small>
                                            @endif
                                            @if($unidad->checklistPlantilla)
                                                <br><small class="text-success">
                                                    <i class="bi bi-check-circle me-1"></i>{{ $unidad->checklistPlantilla->nombre }}
                                                </small>
                                            @else
                                                <br><small class="text-warning">
                                                    <i class="bi bi-exclamation-circle me-1"></i>Sin plantilla asignada
                                                </small>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('unidad_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    {{-- Plantilla: visible solo si la unidad no tiene una asignada --}}
                    <div class="mb-4" id="plantilla-group" style="display: none;">
                        <label class="form-label fw-bold">Plantilla de checklist <span class="text-danger">*</span></label>
                        <select name="plantilla_id" id="selectPlantilla" class="form-select @error('plantilla_id') is-invalid @enderror">
                            @foreach($plantillas as $plantilla)
                                <option value="{{ $plantilla->id }}" {{ old('plantilla_id') == $plantilla->id ? 'selected' : '' }}>
                                    {{ $plantilla->nombre }}
                                    @if($plantilla->tipo_unidad)
                                        ({{ $plantilla->tipo_unidad }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('plantilla_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Indicador de plantilla autoasignada --}}
                    <div class="mb-4" id="plantilla-auto" style="display: none;">
                        <div class="alert alert-success py-2 mb-0">
                            <i class="bi bi-check-circle me-1"></i>
                            Plantilla: <strong id="plantilla-auto-nombre"></strong>
                        </div>
                        <input type="hidden" name="plantilla_id" id="plantilla-hidden" value="">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger btn-lg" {{ $unidades->isEmpty() ? 'disabled' : '' }}>
                            <i class="bi bi-clipboard-plus me-1"></i>Iniciar Inspección
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const plantillaGroup = document.getElementById('plantilla-group');
    const plantillaAuto = document.getElementById('plantilla-auto');
    const plantillaAutoNombre = document.getElementById('plantilla-auto-nombre');
    const plantillaHidden = document.getElementById('plantilla-hidden');
    const selectPlantilla = document.getElementById('selectPlantilla');

    // Mapa de plantillas para obtener el nombre
    const plantillas = {
        @foreach($plantillas as $p)
            '{{ $p->id }}': '{{ $p->nombre }}',
        @endforeach
    };

    document.querySelectorAll('.unidad-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            const plantillaId = this.dataset.plantillaId;

            if (plantillaId) {
                // Unidad tiene plantilla asignada: mostrar autoasignada, ocultar select
                plantillaGroup.style.display = 'none';
                selectPlantilla.removeAttribute('name');
                plantillaHidden.setAttribute('name', 'plantilla_id');
                plantillaHidden.value = plantillaId;
                plantillaAutoNombre.textContent = plantillas[plantillaId] || 'Plantilla #' + plantillaId;
                plantillaAuto.style.display = 'block';
            } else {
                // Sin plantilla asignada: mostrar select manual
                plantillaAuto.style.display = 'none';
                plantillaHidden.removeAttribute('name');
                selectPlantilla.setAttribute('name', 'plantilla_id');
                plantillaGroup.style.display = 'block';
            }
        });
    });

    // Disparar al cargar si hay una unidad preseleccionada
    const checked = document.querySelector('.unidad-radio:checked');
    if (checked) checked.dispatchEvent(new Event('change'));
});
</script>
@endpush
@endsection