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
    $gnCompania  = $guardia->companias->firstWhere('compania_id', $compania->id);
    $guardada    = $gnCompania !== null && !$gnCompania->sin_reporte;
    $especialidades = $compania->especialidades->pluck('nombre');
@endphp

<div class="card mb-4" id="card-compania-{{ $compania->id }}">
    <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-building me-2"></i>
            {{ $compania->numero }}ª Compañía — {{ $compania->nombre }}
            @foreach($compania->especialidades as $esp)
                <span class="badge bg-info text-dark ms-1" style="font-size: 0.7rem;">
                    {{ $esp->nombre }}
                </span>
            @endforeach
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
                            Oficial/Voluntario a cargo <span class="text-danger">*</span>
                        </label>
                        <select name="oficial_a_cargo_id"
                                id="oficial_{{ $compania->id }}"
                                class="form-select tomselect-oficial"
                                data-compania="{{ $compania->id }}">
                            <option value=""></option>
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
                            <option value=""></option>
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

                    {{-- Especialidades dinámicas --}}
                    @if($especialidades->contains('Rescate'))
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-warning">
                            <i class="bi bi-life-preserver me-1"></i>Operadores Rescate
                        </label>
                        <input type="number" name="operadores_rescate"
                               class="form-control"
                               min="0" max="99"
                               placeholder="Cantidad"
                               value="{{ $gnCompania?->operadores_rescate }}">
                    </div>
                    @endif

                    @if($especialidades->contains('Hazmat'))
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-danger">
                            <i class="bi bi-radioactive me-1"></i>Operadores Hazmat
                        </label>
                        <input type="number" name="operadores_hazmat"
                               class="form-control"
                               min="0" max="99"
                               placeholder="Cantidad"
                               value="{{ $gnCompania?->operadores_hazmat }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-danger">
                            <i class="bi bi-radioactive me-1"></i>Técnicos Hazmat
                        </label>
                        <input type="number" name="tecnicos_hazmat"
                               class="form-control"
                               min="0" max="99"
                               placeholder="Cantidad"
                               value="{{ $gnCompania?->tecnicos_hazmat }}">
                    </div>
                    @endif

                </div>

                {{-- Unidades --}}
                <div class="card border mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <span class="fw-bold small">
                            <i class="bi bi-truck-front me-1"></i>Unidades en servicio
                        </span>
                       <div class="d-flex gap-2 align-items-center">
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    onclick="heredarSituacion({{ $compania->id }}, {{ $guardia->id }})">
                                <i class="bi bi-arrow-repeat me-1"></i>Heredar situación actual
                            </button>
                            <span class="text-muted fw-bold">o</span>
                            <button type="button"
                                    class="btn btn-sm btn-outline-success"
                                    onclick="agregarFilaUnidad({{ $compania->id }})">
                                <i class="bi bi-plus-circle me-1"></i>Agregar manualmente
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="unidades-loading-{{ $compania->id }}" class="text-center text-muted py-3" style="display:none;">
                            <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                        </div>
                        <div id="unidades-warning-{{ $compania->id }}"></div>
                        <div id="unidades-container-{{ $compania->id }}">
                            <table class="table table-sm mb-0" id="tabla-unidades-{{ $compania->id }}">
                                <thead class="table-light">
                                    <tr>
                                        <th>Unidad</th>
                                        <th>Tipo conductor</th>
                                        <th>Conductor</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-unidades-{{ $compania->id }}">
                                    @if($gnCompania && $gnCompania->unidades->isNotEmpty())
                                        @php $unidadesVistas = []; @endphp
                                        @foreach($gnCompania->unidades->sortBy('id')->values() as $u)
                                        @php
                                            // 2ª fila de la misma unidad = conductor secundario
                                            $esSec = in_array($u->unidad_id, $unidadesVistas);
                                            $unidadesVistas[] = $u->unidad_id;
                                        @endphp
                                        <tr data-idx="{{ $loop->index }}"
                                            data-cuartelero-id="{{ $u->cuartelero_id }}"
                                            data-maquinista-id="{{ $u->maquinista_id }}"
                                            class="{{ $esSec ? 'table-warning' : '' }}">
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
                                                @if($u->maquinista_id)
                                                    <span class="badge bg-danger">Maquinista</span>
                                                @elseif($u->cuartelero_id)
                                                    <span class="badge bg-info text-dark">Cuartelero</span>
                                                @else
                                                    <span class="badge bg-secondary">Sin conductor</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $u->maquinista?->nombre ?? $u->cuartelero?->nombre ?? '—' }}
                                                @if($esSec)
                                                    <span class="badge bg-warning text-dark ms-1" title="También en servicio con esta unidad">
                                                        <i class="bi bi-person-plus me-1"></i>2° conductor
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="this.closest('tr').remove(); renumerarFilas({{ $compania->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr id="fila-vacia-{{ $compania->id }}">
                                            <td colspan="4" class="text-muted small text-center py-3">
                                                Hereda la situación actual o agrega unidades manualmente.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
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
// dropdown_input: el buscador va dentro del desplegable, así lo escrito
// no se mezcla con el valor seleccionado ni con el placeholder
document.querySelectorAll('.tomselect-oficial').forEach(el => {
    const companiaId = el.dataset.compania;

    const tsOficial = new TomSelect(el, {
        plugins: ['dropdown_input', 'clear_button'],
        placeholder: 'Buscar oficial...',
        maxOptions: null,
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

document.querySelectorAll('.tomselect-cuartelero').forEach(el => {
    const companiaId = el.closest('form').querySelector('input[name="compania_id"]').value;

    const tsCuartelero = new TomSelect(el, {
        plugins: ['dropdown_input', 'clear_button'],
        placeholder: 'Sin cuartelero (buscar...)',
        maxOptions: null,
    });

    tsCuartelero.on('change', function(value) {
        if (!value) return;
        cargarUnidadesCuartelero(companiaId, value);
    });
});

function cargarUnidadesCuartelero(companiaId, cuarteleroId) {
    const tbody     = document.getElementById('tbody-unidades-' + companiaId);
    const container = document.getElementById('unidades-container-' + companiaId);
    const loading   = document.getElementById('unidades-loading-' + companiaId);
    const warning   = document.getElementById('unidades-warning-' + companiaId);

    // Solo auto-cargar si la tabla está vacía o solo tiene la fila placeholder
    const filasActuales = tbody.querySelectorAll('tr:not([id^="fila-vacia"])');
    if (filasActuales.length > 0) return; // ya hay unidades cargadas, no pisar

    loading.style.display   = 'block';
    container.style.display = 'none';
    warning.innerHTML       = '';

    fetch(`/guardias-nocturnas/cuartelero/${cuarteleroId}/unidades`)
        .then(r => r.json())
        .then(data => {
            loading.style.display   = 'none';
            container.style.display = 'block';

            if (!data.en_turno || data.unidades.length === 0) return;

            // Limpiar tabla
            const filaVacia = document.getElementById('fila-vacia-' + companiaId);
            if (filaVacia) filaVacia.remove();

            let html = '';
            data.unidades.forEach((u, i) => {
                html += `
                    <tr data-cuartelero-id="${u.responsable_id}" data-idx="${i}">
                        <td>
                            <span class="badge bg-primary">${u.unidad_nombre}</span>
                            <input type="hidden" name="unidades[${i}][unidad_id]" value="${u.unidad_id}">
                            <input type="hidden" name="unidades[${i}][maquinista_id]" value="">
                            <input type="hidden" name="unidades[${i}][cuartelero_id]" value="${u.responsable_id}">
                        </td>
                        <td>
                            <span class="badge bg-info text-dark">Cuartelero</span>
                        </td>
                        <td>${u.responsable_nombre}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="this.closest('tr').remove(); renumerarFilas(${companiaId})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>`;
            });

            tbody.innerHTML = html;
        })
        .catch(() => {
            loading.style.display   = 'none';
            container.style.display = 'block';
        });
}

// Inicializar TomSelect voluntarios
document.querySelectorAll('.tomselect-voluntarios').forEach(el => {
    const ts = new TomSelect(el, {
        plugins: ['remove_button'],
        placeholder: 'Buscar y agregar voluntarios...',
        maxOptions: null,
        onItemAdd: function() {
            this.setTextboxValue('');
            this.refreshOptions();
        },
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

            // Se pinta dentro del tbody existente para que "Agregar manualmente"
            // y la verificación del cuartelero sigan funcionando después de heredar
            const tbody = document.getElementById('tbody-unidades-' + companiaId);

            if (data.unidades.length === 0) {
                tbody.innerHTML = `<tr id="fila-vacia-${companiaId}"><td colspan="4" class="text-muted small text-center py-3">No hay unidades activas para esta compañía.</td></tr>`;
                return;
            }

            const secundarios = data.unidades.filter(u => u.es_secundario);
            if (secundarios.length) {
                warning.innerHTML += `
                    <div class="alert alert-info mx-3 mt-2 py-2 mb-0 small">
                        <i class="bi bi-person-plus me-1"></i>
                        También quedan en servicio como <strong>conductor secundario</strong>:
                        ${secundarios.map(u => `<strong>${u.responsable_nombre}</strong> (${u.unidad_nombre})`).join(', ')}.
                    </div>`;
            }

            let html = '';
            data.unidades.forEach((u, i) => {
                const responsable = u.responsable_nombre ?? '—';
                let tipoBadge = '<span class="badge bg-secondary">Sin conductor</span>';
                if (u.tipo === 'maquinista') tipoBadge = '<span class="badge bg-danger">Maquinista</span>';
                if (u.tipo === 'cuartelero') tipoBadge = '<span class="badge bg-info text-dark">Cuartelero</span>';
                const secBadge = u.es_secundario
                    ? '<span class="badge bg-warning text-dark ms-1"><i class="bi bi-person-plus me-1"></i>2° conductor</span>'
                    : '';

                html += `
                    <tr data-idx="${i}" class="${u.es_secundario ? 'table-warning' : ''}"
                        data-cuartelero-id="${u.tipo === 'cuartelero' ? u.responsable_id : ''}"
                        data-maquinista-id="${u.tipo === 'maquinista' ? u.responsable_id : ''}">
                        <td>
                            <span class="badge bg-primary">${u.unidad_nombre}</span>
                            <input type="hidden" name="unidades[${i}][unidad_id]" value="${u.unidad_id}">
                            <input type="hidden" name="unidades[${i}][maquinista_id]"
                                   value="${u.tipo === 'maquinista' ? u.responsable_id : ''}">
                            <input type="hidden" name="unidades[${i}][cuartelero_id]"
                                   value="${u.tipo === 'cuartelero' ? u.responsable_id : ''}">
                        </td>
                        <td>${tipoBadge}</td>
                        <td>${responsable} ${secBadge}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="this.closest('tr').remove(); renumerarFilas(${companiaId})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>`;
            });

            tbody.innerHTML = html;

            // Verificar cuartelero DESPUÉS de heredar la situación actual
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
            warning.innerHTML = '<div class="alert alert-danger alert-sm mx-3 mt-2 py-2 mb-0">Error al cargar la situación actual.</div>';
        });
}

function confirmarCierre() {
    new bootstrap.Modal(document.getElementById('modalCerrar')).show();
}

// Verifica que el cuartelero seleccionado aparezca a cargo de alguna unidad
function verificarCuarteleroEnUnidades(companiaId, cuarteleroId, selectEl) {
    const warningId = 'warning-cuartelero-' + companiaId;
    const existing  = document.getElementById(warningId);
    if (existing) existing.remove();

    if (!cuarteleroId) return;

    const tsInstance   = selectEl.tomselect;
    const nombreCuart  = tsInstance.options[cuarteleroId]?.text ?? 'El cuartelero seleccionado';

    // Buscar en la tabla ACTUAL (después de heredar)
    const tbody        = document.getElementById('tbody-unidades-' + companiaId);
    const filas        = tbody?.querySelectorAll('tr') ?? [];
    let aparece        = false;

    filas.forEach(fila => {
        const cuarteleroEnFila = fila.getAttribute('data-cuartelero-id');
        if (cuarteleroEnFila && cuarteleroEnFila == cuarteleroId) {
            aparece = true;
        }
    });

    // Solo mostrar aviso si la tabla tiene unidades Y el cuartelero no aparece
    if (!aparece && filas.length > 0) {
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

        selectEl.closest('.col-md-6').appendChild(warning);
    }
}

// Datos disponibles por compañía para el modo manual
const datosCompanias = @json($datosCompanias);

function agregarFilaUnidad(companiaId) {
    const tbody = document.getElementById('tbody-unidades-' + companiaId);

    // Quitar fila vacía si existe
    const filaVacia = document.getElementById('fila-vacia-' + companiaId);
    if (filaVacia) filaVacia.remove();

    const idx   = tbody.querySelectorAll('tr').length;
    const datos = datosCompanias[companiaId];

    // Opciones unidades
    let optsUnidad = '<option value="">Seleccionar unidad...</option>';
    datos.unidades.forEach(u => {
        optsUnidad += `<option value="${u.id}">${u.nombre}</option>`;
    });

    const tr = document.createElement('tr');
    tr.dataset.idx = idx;
    tr.innerHTML = `
        <td>
            <select class="form-select form-select-sm"
                    onchange="actualizarHiddenUnidad(this)"
                    style="min-width:130px">
                ${optsUnidad}
            </select>
            <input type="hidden" name="unidades[${idx}][unidad_id]" value="">
            <input type="hidden" name="unidades[${idx}][maquinista_id]" value="">
            <input type="hidden" name="unidades[${idx}][cuartelero_id]" value="">
        </td>
        <td>
            <select class="form-select form-select-sm"
                    onchange="cambiarTipoConductor(this, ${companiaId})"
                    style="min-width:130px">
                <option value="">Sin conductor</option>
                <option value="maquinista">Maquinista</option>
                <option value="cuartelero">Cuartelero</option>
            </select>
        </td>
        <td>
            <div class="conductor-container" style="min-width:200px">
                <span class="text-muted small">—</span>
            </div>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger"
                    onclick="this.closest('tr').remove(); renumerarFilas(${companiaId})">
                <i class="bi bi-trash"></i>
            </button>
        </td>`;

    tbody.appendChild(tr);
}

// Al elegir tipo de conductor, se crea un buscador TomSelect con la lista que corresponde
function cambiarTipoConductor(selectTipo, companiaId) {
    const tipo      = selectTipo.value;
    const tr        = selectTipo.closest('tr');
    const container = tr.querySelector('.conductor-container');
    const datos     = datosCompanias[companiaId];

    const inputMaq  = tr.querySelector('input[name$="[maquinista_id]"]');
    const inputCuar = tr.querySelector('input[name$="[cuartelero_id]"]');
    inputMaq.value  = '';
    inputCuar.value = '';

    // Destruir TomSelect anterior si existía
    const prev = container.querySelector('select');
    if (prev?.tomselect) prev.tomselect.destroy();

    if (!tipo) {
        container.innerHTML = '<span class="text-muted small">—</span>';
        return;
    }

    const lista  = tipo === 'maquinista' ? datos.maquinistas : datos.cuarteleros;
    const hidden = tipo === 'maquinista' ? inputMaq : inputCuar;

    container.innerHTML = '<select class="form-select form-select-sm"></select>';
    const sel = container.querySelector('select');

    const ts = new TomSelect(sel, {
        plugins: ['dropdown_input'],
        options: lista.map(p => ({ value: String(p.id), text: p.nombre })),
        placeholder: tipo === 'maquinista' ? 'Buscar maquinista...' : 'Buscar cuartelero...',
        maxOptions: null,
        dropdownParent: 'body', // evita que la tabla recorte el desplegable
        onChange: value => { hidden.value = value; },
    });

    ts.open(); // abre el buscador directo para escribir
}

function actualizarHiddenUnidad(select) {
    select.closest('tr').querySelector('input[name$="[unidad_id]"]').value = select.value;
}

// Renumera los índices de los inputs tras eliminar una fila
function renumerarFilas(companiaId) {
    const tbody = document.getElementById('tbody-unidades-' + companiaId);
    tbody.querySelectorAll('tr').forEach((tr, newIdx) => {
        tr.dataset.idx = newIdx;
        tr.querySelectorAll('input[type="hidden"]').forEach(inp => {
            inp.name = inp.name.replace(/unidades\[\d+\]/, `unidades[${newIdx}]`);
        });
    });
}
</script>
@endpush

@endsection