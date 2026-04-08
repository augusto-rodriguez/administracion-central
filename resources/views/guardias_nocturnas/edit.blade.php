@extends('layouts.app')
@section('title', 'Guardia Nocturna — ' . $guardia->fecha->format('d/m/Y'))
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-moon-stars me-2"></i>Guardia Nocturna
        </h4>
        <small class="text-muted">{{ $guardia->fecha->format('d/m/Y') }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('guardias-nocturnas.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>

        @php
            $companiasSinReporte = $companias->filter(function($comp) use ($guardia) {
                $gnComp = $guardia->companias->firstWhere('compania_id', $comp->id);
                return $gnComp === null || $gnComp->sin_reporte;
            });
        @endphp

        {{-- Formulario oculto que se envía al confirmar --}}
        <form action="{{ route('guardias-nocturnas.cerrar', $guardia) }}" method="POST"
            id="formCerrarGuardia">
            @csrf
        </form>

        {{-- Botón que abre el modal --}}
        <button type="button" class="btn btn-danger btn-sm" onclick="confirmarCierre()">
            <i class="bi bi-lock me-1"></i>Cerrar guardia nocturna
        </button>
    </div>
</div>

{{-- Modal confirmación cierre --}}
<div class="modal fade" id="modalCerrar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white py-2">
                <h6 class="modal-title mb-0">
                    <i class="bi bi-lock me-2"></i>Cerrar guardia nocturna
                </h6>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($companiasSinReporte->isNotEmpty())
                    <div class="alert alert-warning d-flex gap-2 align-items-start">
                        <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
                        <div>
                            <strong>
                                {{ $companiasSinReporte->count() }}
                                compañía(s) sin reporte:
                            </strong>
                            <ul class="mb-0 mt-1">
                                @foreach($companiasSinReporte as $comp)
                                    <li>{{ $comp->numero }}ª — {{ $comp->nombre }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                <p class="mb-0">
                    ¿Estás seguro de cerrar la guardia nocturna?
                    <strong>Esta acción no se puede deshacer.</strong>
                </p>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formCerrarGuardia"
                        class="btn btn-danger btn-sm">
                    <i class="bi bi-lock me-1"></i>Confirmar cierre
                </button>
            </div>
        </div>
    </div>
</div>


@foreach($companias as $compania)
@php
    $gnCompania = $guardia->companias->firstWhere('compania_id', $compania->id);
    $guardada   = $gnCompania !== null && !$gnCompania->sin_reporte;
@endphp

<div class="card mb-4" id="card-compania-{{ $compania->id }}">
    <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-building me-2"></i>
            {{ $compania->numero }}ª Compañía — {{ $compania->nombre }}
        </span>
        @if($gnCompania)
            @if($gnCompania->sin_reporte)
                <span class="badge bg-danger">Sin reporte</span>
            @else
                <span class="badge bg-success">
                    <i class="bi bi-check2 me-1"></i>Guardado
                </span>
            @endif
        @else
            <span class="badge bg-secondary">Pendiente</span>
        @endif
    </div>
    <div class="card-body">
        <form action="{{ route('guardias-nocturnas.guardar-compania', $guardia) }}" method="POST">
            @csrf
            <input type="hidden" name="compania_id" value="{{ $compania->id }}">

            {{-- Sin reporte --}}
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="sin_reporte"
                       value="1" id="sin_reporte_{{ $compania->id }}"
                       {{ $gnCompania?->sin_reporte ? 'checked' : '' }}
                       onchange="toggleFormCompania({{ $compania->id }}, this.checked)">
                <label class="form-check-label text-danger fw-bold"
                       for="sin_reporte_{{ $compania->id }}">
                    Sin reporte de guardia nocturna
                </label>
            </div>

            <div id="form-compania-{{ $compania->id }}"
                 style="{{ $gnCompania?->sin_reporte ? 'display:none' : '' }}">

                <div class="row g-3 mb-3">
                    {{-- Oficial a cargo --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            Oficial a cargo <span class="text-danger">*</span>
                        </label>
                        <select name="oficial_a_cargo_id"
                                id="oficial_{{ $compania->id }}"
                                class="form-select tomselect-oficial"
                                data-compania="{{ $compania->id }}">
                            <option value="">Buscar voluntario...</option>
                            @foreach($compania->voluntarios->where('activo', true) as $vol)
                                <option value="{{ $vol->id }}"
                                        {{ $gnCompania?->oficial_a_cargo_id == $vol->id ? 'selected' : '' }}>
                                    {{ $vol->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Cuartelero (opcional) --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            Cuartelero de turno
                            <span class="text-muted fw-normal small">(opcional)</span>
                        </label>
                        <select name="cuartelero_id"
                                id="cuartelero_{{ $compania->id }}"
                                class="form-select tomselect-cuartelero">
                            <option value="">Sin cuartelero</option>
                            @foreach($compania->cuarteleros->where('activo', true) as $cuar)
                                <option value="{{ $cuar->id }}"
                                        {{ $gnCompania?->cuartelero_id == $cuar->id ? 'selected' : '' }}>
                                    {{ $cuar->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Voluntarios en guardia --}}
                    <div class="col-12">
                        <label class="form-label fw-bold">Voluntarios en guardia</label>
                        <select name="voluntarios[]"
                                id="voluntarios_{{ $compania->id }}"
                                class="form-select tomselect-voluntarios"
                                multiple>
                            @foreach($compania->voluntarios->where('activo', true) as $vol)
                                <option value="{{ $vol->id }}"
                                    {{ $gnCompania?->voluntarios->pluck('voluntario_id')->contains($vol->id) ? 'selected' : '' }}>
                                    {{ $vol->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Observaciones --}}
                    <div class="col-12">
                        <label class="form-label fw-bold">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2"
                                  placeholder="Observaciones opcionales...">{{ $gnCompania?->observaciones }}</textarea>
                    </div>
                </div>

                {{-- Unidades --}}
                <div class="card border mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <span class="fw-bold small">
                            <i class="bi bi-truck-front me-1"></i>Unidades en servicio
                        </span>
                        <button type="button"
                                class="btn btn-sm btn-outline-primary"
                                onclick="heredarSituacion({{ $compania->id }}, {{ $guardia->id }})">
                            <i class="bi bi-arrow-repeat me-1"></i>Heredar situación actual
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div id="unidades-loading-{{ $compania->id }}" class="text-center text-muted py-3"
                             style="display:none;">
                            <div class="spinner-border spinner-border-sm me-2"></div>
                            Cargando situación actual...
                        </div>
                        <div id="unidades-warning-{{ $compania->id }}"></div>
                        <div id="unidades-container-{{ $compania->id }}">
                            @if($gnCompania && $gnCompania->unidades->isNotEmpty())
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Unidad</th>
                                            <th>Conductor</th>
                                            <th>Tipo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($gnCompania->unidades as $u)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">{{ $u->unidad->nombre }}</span>
                                                <input type="hidden" name="unidades[{{ $loop->index }}][unidad_id]"
                                                       value="{{ $u->unidad_id }}">
                                                <input type="hidden" name="unidades[{{ $loop->index }}][maquinista_id]"
                                                       value="{{ $u->maquinista_id }}">
                                                <input type="hidden" name="unidades[{{ $loop->index }}][cuartelero_id]"
                                                       value="{{ $u->cuartelero_id }}">
                                            </td>
                                            <td>
                                                {{ $u->maquinista?->nombre ?? $u->cuartelero?->nombre ?? '—' }}
                                            </td>
                                            <td>
                                                @if($u->maquinista_id)
                                                    <span class="badge bg-danger">Maquinista</span>
                                                @elseif($u->cuartelero_id)
                                                    <span class="badge bg-info text-dark">Cuartelero</span>
                                                @else
                                                    <span class="badge bg-secondary">Sin conductor</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-muted small text-center py-3 mb-0">
                                    Presiona "Heredar situación actual" para cargar las unidades.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>{{-- fin form-compania --}}

            <div class="mt-3">
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-floppy me-1"></i>Guardar {{ $compania->nombre }}
                </button>
            </div>

        </form>
    </div>
</div>
@endforeach

@push('scripts')
<script>
// Inicializar TomSelect oficial + listener automático a voluntarios
document.querySelectorAll('.tomselect-oficial').forEach(el => {
    const companiaId = el.dataset.compania;

    const tsOficial = new TomSelect(el, {
        allowEmptyOption: true,
        placeholder: 'Buscar oficial...',
    });

    tsOficial.on('change', function(value) {
        if (!value) return;

        const volSelect = document.getElementById('voluntarios_' + companiaId);
        if (!volSelect?.tomselect) return;

        const tsVol = volSelect.tomselect;
        if (!tsVol.getValue().includes(value)) {
            tsVol.addItem(value);
        }
    });
});

// Inicializar TomSelect cuarteleros
document.querySelectorAll('.tomselect-cuartelero').forEach(el => {
    const companiaId = el.closest('form').querySelector('input[name="compania_id"]').value;

    const tsCuartelero = new TomSelect(el, {
        allowEmptyOption: true,
        placeholder: 'Buscar cuartelero...',
    });

    tsCuartelero.on('change', function(value) {
        verificarCuarteleroEnUnidades(companiaId, value, el);
    });
});

// Inicializar TomSelect voluntarios
document.querySelectorAll('.tomselect-voluntarios').forEach(el => {
    new TomSelect(el, {
        plugins: ['remove_button'],
        placeholder: 'Buscar y agregar voluntarios...',
    });
});

// Mostrar/ocultar formulario si sin reporte
function toggleFormCompania(companiaId, sinReporte) {
    const form = document.getElementById('form-compania-' + companiaId);
    form.style.display = sinReporte ? 'none' : 'block';
}

// Heredar situación actual
function heredarSituacion(companiaId, guardiaId) {
    const loading   = document.getElementById('unidades-loading-' + companiaId);
    const container = document.getElementById('unidades-container-' + companiaId);
    const warning   = document.getElementById('unidades-warning-' + companiaId);

    loading.style.display   = 'block';
    container.style.display = 'none';
    warning.innerHTML       = '';

    fetch(`/guardias-nocturnas/${guardiaId}/heredar/${companiaId}`)
        .then(r => r.json())
        .then(data => {
            loading.style.display   = 'none';
            container.style.display = 'block';

            if (data.sin_conductor > 0) {
                warning.innerHTML = `
                    <div class="alert alert-warning alert-sm mx-3 mt-2 py-2 mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>${data.sin_conductor}</strong> unidad(es) sin conductor asignado.
                        Si es necesario, actualiza primero las Puestas en Servicio.
                    </div>`;
            }

            if (data.unidades.length === 0) {
                container.innerHTML = '<p class="text-muted small text-center py-3 mb-0">No hay unidades activas para esta compañía.</p>';
                return;
            }

            let html = `
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Unidad</th><th>Conductor</th><th>Tipo</th></tr>
                    </thead>
                    <tbody>`;

            data.unidades.forEach((u, i) => {
                const responsable = u.responsable_nombre ?? '—';
                let tipoBadge = '<span class="badge bg-secondary">Sin conductor</span>';
                if (u.tipo === 'maquinista') tipoBadge = '<span class="badge bg-danger">Maquinista</span>';
                if (u.tipo === 'cuartelero') tipoBadge = '<span class="badge bg-info text-dark">Cuartelero</span>';

                html += `
                    <tr>
                        <td>
                            <span class="badge bg-primary">${u.unidad_nombre}</span>
                            <input type="hidden" name="unidades[${i}][unidad_id]" value="${u.unidad_id}">
                            <input type="hidden" name="unidades[${i}][maquinista_id]"
                                   value="${u.tipo === 'maquinista' ? u.responsable_id : ''}">
                            <input type="hidden" name="unidades[${i}][cuartelero_id]"
                                   value="${u.tipo === 'cuartelero' ? u.responsable_id : ''}">
                        </td>
                        <td>${responsable}</td>
                        <td>${tipoBadge}</td>
                    </tr>`;
            });

            html += '</tbody></table>';
            container.innerHTML = html;

            // Verificar cuartelero después de heredar
            const cuarteleroSelect = document.querySelector(
                `select[name="cuartelero_id"][id="cuartelero_${companiaId}"]`
            );
            if (cuarteleroSelect?.tomselect?.getValue()) {
                verificarCuarteleroEnUnidades(
                    companiaId,
                    cuarteleroSelect.tomselect.getValue(),
                    cuarteleroSelect
                );
            }
        })
        .catch(() => {
            loading.style.display   = 'none';
            container.style.display = 'block';
            warning.innerHTML = '<div class="alert alert-danger mx-3 mt-2 py-2">Error al cargar la situación actual.</div>';
        });
}

function confirmarCierre() {
    new bootstrap.Modal(document.getElementById('modalCerrar')).show();
}

function verificarCuarteleroEnUnidades(companiaId, cuarteleroId, selectEl) {
    // Limpiar advertencia previa
    const warningId = 'warning-cuartelero-' + companiaId;
    const existing  = document.getElementById(warningId);
    if (existing) existing.remove();

    if (!cuarteleroId) return;

    // Buscar el nombre del cuartelero seleccionado
    const tsInstance   = selectEl.tomselect;
    const nombreCuart  = tsInstance.options[cuarteleroId]?.text ?? 'El cuartelero seleccionado';

    // Revisar si aparece en alguna unidad heredada
    const container    = document.getElementById('unidades-container-' + companiaId);
    const filas        = container?.querySelectorAll('tbody tr') ?? [];
    let aparece        = false;

    filas.forEach(fila => {
        const inputCuart = fila.querySelector('input[name*="cuartelero_id"]');
        if (inputCuart && inputCuart.value == cuarteleroId) {
            aparece = true;
        }
    });

    if (!aparece && filas.length > 0) {
        // Insertar advertencia debajo del select de cuartelero
        const warning = document.createElement('div');
        warning.id    = warningId;
        warning.className = 'alert alert-warning py-2 mt-2 mb-0 small';
        warning.innerHTML = `
            <i class="bi bi-exclamation-triangle me-1"></i>
            <strong>${nombreCuart}</strong> está seleccionado como cuartelero de turno
            pero no aparece a cargo de ninguna unidad.
            Si corresponde, actualiza las
            <a href="{{ route('turnos.index') }}" target="_blank">Puestas en Servicio</a>
            y vuelve a heredar la situación.`;

        // Insertar después del select
        selectEl.closest('.col-md-6').appendChild(warning);
    }
}
</script>
@endpush

@endsection