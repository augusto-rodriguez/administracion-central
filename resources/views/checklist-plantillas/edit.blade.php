@extends('layouts.app')

@section('title', 'Editar Plantilla')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-gear me-2"></i>{{ $plantilla->nombre }}</h4>
    <a href="{{ route('checklist-plantillas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<form action="{{ route('checklist-plantillas.update', $plantilla) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- Datos de la plantilla --}}
    <div class="card mb-4">
        <div class="card-header bg-dark text-white py-2">
            <i class="bi bi-info-circle me-1"></i> Datos generales
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $plantilla->nombre) }}" required>
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <input type="hidden" name="tipo_unidad" value="{{ $plantilla->tipo_unidad }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Estado</label>
                    <div class="form-check form-switch mt-2">
                        <input type="hidden" name="activa" value="0">
                        <input type="checkbox" class="form-check-input" name="activa" value="1"
                               {{ old('activa', $plantilla->activa) ? 'checked' : '' }}>
                        <label class="form-check-label">Activa</label>
                    </div>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2"
                              placeholder="Instrucciones generales para el cuartelero">{{ old('descripcion', $plantilla->descripcion) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Unidades asignadas --}}
    <div class="card mb-4">
        <div class="card-header bg-dark text-white py-2">
            <i class="bi bi-truck me-1"></i> Unidades que usan esta plantilla
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Selecciona las unidades que usarán esta plantilla automáticamente al crear una inspección.
                Las unidades no seleccionadas requerirán selección manual de plantilla.
            </p>
            <div class="row g-2">
                @foreach($todasUnidades as $unidad)
                    @php
                        $asignada = in_array($unidad->id, $unidadesAsignadas);
                        $otraPlantilla = $unidad->checklist_plantilla_id && $unidad->checklist_plantilla_id != $plantilla->id;
                    @endphp
                    <div class="col-md-4 col-6">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="unidades_ids[]"
                                   value="{{ $unidad->id }}" id="unidad_{{ $unidad->id }}"
                                   {{ $asignada ? 'checked' : '' }}
                                   {{ $otraPlantilla ? 'disabled' : '' }}>
                            <label class="form-check-label {{ $otraPlantilla ? 'text-muted' : '' }}" for="unidad_{{ $unidad->id }}">
                                <strong>{{ $unidad->nombre }}</strong>
                                @if($unidad->patente)
                                    <br><small class="text-muted">{{ $unidad->patente }}</small>
                                @endif
                                @if($otraPlantilla)
                                    <br><small class="text-warning">
                                        <i class="bi bi-exclamation-circle me-1"></i>Usa: {{ $unidad->checklistPlantilla->nombre }}
                                    </small>
                                @endif
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Secciones --}}
    @foreach($plantilla->secciones as $sIndex => $seccion)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <strong>
                    <span class="badge bg-secondary me-1">{{ $sIndex + 1 }}</span>
                    {{ $seccion->nombre }}
                </strong>
                <span class="badge bg-{{ $seccion->activa ? 'success' : 'secondary' }}">
                    {{ $seccion->activa ? 'Activa' : 'Inactiva' }}
                </span>
            </div>
            <div class="card-body">
                <input type="hidden" name="secciones[{{ $sIndex }}][id]" value="{{ $seccion->id }}">

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Nombre sección</label>
                        <input type="text" name="secciones[{{ $sIndex }}][nombre]" class="form-control form-control-sm"
                               value="{{ $seccion->nombre }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Tipo respuesta</label>
                        <select name="secciones[{{ $sIndex }}][tipo_respuesta]" class="form-select form-select-sm" required>
                            <option value="nivel" {{ $seccion->tipo_respuesta === 'nivel' ? 'selected' : '' }}>Nivel (1/4, 1/2, 3/4, FULL)</option>
                            <option value="estado" {{ $seccion->tipo_respuesta === 'estado' ? 'selected' : '' }}>Estado (Bueno, Regular, Malo)</option>
                            <option value="documento" {{ $seccion->tipo_respuesta === 'documento' ? 'selected' : '' }}>Documento (OK, Vencido)</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-bold">Orden</label>
                        <input type="number" name="secciones[{{ $sIndex }}][orden]" class="form-control form-control-sm"
                               value="{{ $seccion->orden }}" min="0" required>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-bold">Activa</label>
                        <div class="form-check form-switch mt-1">
                            <input type="hidden" name="secciones[{{ $sIndex }}][activa]" value="0">
                            <input type="checkbox" class="form-check-input" name="secciones[{{ $sIndex }}][activa]" value="1"
                                   {{ $seccion->activa ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Descripción</label>
                        <input type="text" name="secciones[{{ $sIndex }}][descripcion]" class="form-control form-control-sm"
                               value="{{ $seccion->descripcion }}">
                    </div>
                </div>

                {{-- Ítems de la sección --}}
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small">
                                <th>Nombre del ítem</th>
                                <th class="text-center" style="width: 70px;">Orden</th>
                                <th class="text-center" style="width: 70px;">Crítico</th>
                                <th class="text-center" style="width: 70px;">Activo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($seccion->items as $iIndex => $item)
                                <tr>
                                    <input type="hidden" name="secciones[{{ $sIndex }}][items][{{ $iIndex }}][id]" value="{{ $item->id }}">
                                    <td>
                                        <input type="text" name="secciones[{{ $sIndex }}][items][{{ $iIndex }}][nombre]"
                                               class="form-control form-control-sm border-0"
                                               value="{{ $item->nombre }}" required>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" name="secciones[{{ $sIndex }}][items][{{ $iIndex }}][orden]"
                                               class="form-control form-control-sm text-center border-0"
                                               value="{{ $item->orden }}" min="0" required style="width: 50px; margin: 0 auto;">
                                    </td>
                                    <td class="text-center">
                                        <input type="hidden" name="secciones[{{ $sIndex }}][items][{{ $iIndex }}][es_critico]" value="0">
                                        <input type="checkbox" class="form-check-input"
                                               name="secciones[{{ $sIndex }}][items][{{ $iIndex }}][es_critico]" value="1"
                                               {{ $item->es_critico ? 'checked' : '' }}>
                                    </td>
                                    <td class="text-center">
                                        <input type="hidden" name="secciones[{{ $sIndex }}][items][{{ $iIndex }}][activo]" value="0">
                                        <input type="checkbox" class="form-check-input"
                                               name="secciones[{{ $sIndex }}][items][{{ $iIndex }}][activo]" value="1"
                                               {{ $item->activo ? 'checked' : '' }}>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex gap-2 mb-5">
        <button type="submit" class="btn btn-danger">
            <i class="bi bi-save me-1"></i>Guardar cambios
        </button>
        <a href="{{ route('checklist-plantillas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
@endsection