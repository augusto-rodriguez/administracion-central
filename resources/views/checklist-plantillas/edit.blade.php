@extends('layouts.app')

@section('title', 'Editar Plantilla')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-gear me-2"></i>{{ $plantilla->nombre }}</h4>
    <a href="{{ route('checklist-plantillas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>No se pudo guardar:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="form-plantilla" action="{{ route('checklist-plantillas.update', $plantilla) }}" method="POST">
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="bi bi-list-check me-1"></i>Secciones</h5>
        <button type="button" class="btn btn-sm btn-success" id="btn-agregar-seccion">
            <i class="bi bi-plus-lg me-1"></i>Agregar sección
        </button>
    </div>

    <div id="secciones-container">
        @foreach($plantilla->secciones as $sIndex => $seccion)
            @php
                $seccionConHistorial = $seccion->items->contains(
                    fn($i) => ($i->respuestas_count + $i->hallazgos_count) > 0
                );
            @endphp
            <div class="card mb-3 seccion-card"
                 data-seccion-key="{{ $sIndex }}"
                 data-existente="1"
                 data-historial="{{ $seccionConHistorial ? 1 : 0 }}">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <strong>
                        <span class="badge bg-secondary me-1 seccion-numero">{{ $sIndex + 1 }}</span>
                        <span class="seccion-titulo">{{ $seccion->nombre }}</span>
                    </strong>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-{{ $seccion->activa ? 'success' : 'secondary' }}">
                            {{ $seccion->activa ? 'Activa' : 'Inactiva' }}
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-seccion" title="Quitar sección">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <input type="hidden" name="secciones[{{ $sIndex }}][id]" value="{{ $seccion->id }}">

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Nombre sección</label>
                            <input type="text" name="secciones[{{ $sIndex }}][nombre]" class="form-control form-control-sm seccion-nombre"
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
                            <input type="number" name="secciones[{{ $sIndex }}][orden]" class="form-control form-control-sm seccion-orden"
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
                        <table class="table table-sm table-bordered align-middle mb-2">
                            <thead class="table-light">
                                <tr class="small">
                                    <th>Nombre del ítem</th>
                                    <th class="text-center" style="width: 70px;">Orden</th>
                                    <th class="text-center" style="width: 70px;">Crítico</th>
                                    <th class="text-center" style="width: 70px;">Activo</th>
                                    <th class="text-center" style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody class="items-body">
                                <tr class="fila-vacia {{ $seccion->items->isEmpty() ? '' : 'd-none' }}">
                                    <td colspan="5" class="text-center text-muted small py-3">
                                        Esta sección no tiene ítems. Usa "Agregar ítem".
                                    </td>
                                </tr>
                                @foreach($seccion->items as $iIndex => $item)
                                    @php $itemConHistorial = ($item->respuestas_count + $item->hallazgos_count) > 0; @endphp
                                    <tr class="item-row {{ $item->activo ? '' : 'table-secondary' }}"
                                        data-existente="1"
                                        data-historial="{{ $itemConHistorial ? 1 : 0 }}">
                                        <td>
                                            <input type="hidden" name="secciones[{{ $sIndex }}][items][{{ $iIndex }}][id]" value="{{ $item->id }}">
                                            <input type="text" name="secciones[{{ $sIndex }}][items][{{ $iIndex }}][nombre]"
                                                   class="form-control form-control-sm border-0 item-nombre"
                                                   value="{{ $item->nombre }}" required>
                                        </td>
                                        <td class="text-center">
                                            <input type="number" name="secciones[{{ $sIndex }}][items][{{ $iIndex }}][orden]"
                                                   class="form-control form-control-sm text-center border-0 item-orden"
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
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-quitar-item"
                                                    title="{{ $itemConHistorial ? 'Tiene historial: se desactivará al guardar' : 'Quitar ítem' }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary btn-agregar-item">
                        <i class="bi bi-plus-lg me-1"></i>Agregar ítem
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <div id="sin-secciones" class="text-center text-muted border rounded py-4 mb-3 {{ $plantilla->secciones->isEmpty() ? '' : 'd-none' }}">
        Esta plantilla aún no tiene secciones. Usa "Agregar sección" para crear la primera.
    </div>

    <div class="d-flex gap-2 mb-5">
        <button type="submit" class="btn btn-danger">
            <i class="bi bi-save me-1"></i>Guardar cambios
        </button>
        <a href="{{ route('checklist-plantillas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

{{-- ═══ Plantillas HTML para elementos nuevos (no se envían con el formulario) ═══ --}}

<template id="tpl-seccion">
    <div class="card mb-3 seccion-card border-success" data-seccion-key="__S__">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <strong>
                <span class="badge bg-secondary me-1 seccion-numero"></span>
                <span class="seccion-titulo">Nueva sección</span>
            </strong>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-info text-dark">Nueva</span>
                <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-seccion" title="Quitar sección">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Nombre sección</label>
                    <input type="text" name="secciones[__S__][nombre]" class="form-control form-control-sm seccion-nombre"
                           placeholder="Ej: Luces y balizas" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Tipo respuesta</label>
                    <select name="secciones[__S__][tipo_respuesta]" class="form-select form-select-sm" required>
                        <option value="nivel">Nivel (1/4, 1/2, 3/4, FULL)</option>
                        <option value="estado" selected>Estado (Bueno, Regular, Malo)</option>
                        <option value="documento">Documento (OK, Vencido)</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold">Orden</label>
                    <input type="number" name="secciones[__S__][orden]" class="form-control form-control-sm seccion-orden"
                           value="0" min="0" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold">Activa</label>
                    <div class="form-check form-switch mt-1">
                        <input type="hidden" name="secciones[__S__][activa]" value="0">
                        <input type="checkbox" class="form-check-input" name="secciones[__S__][activa]" value="1" checked>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Descripción</label>
                    <input type="text" name="secciones[__S__][descripcion]" class="form-control form-control-sm">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-2">
                    <thead class="table-light">
                        <tr class="small">
                            <th>Nombre del ítem</th>
                            <th class="text-center" style="width: 70px;">Orden</th>
                            <th class="text-center" style="width: 70px;">Crítico</th>
                            <th class="text-center" style="width: 70px;">Activo</th>
                            <th class="text-center" style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody class="items-body">
                        <tr class="fila-vacia">
                            <td colspan="5" class="text-center text-muted small py-3">
                                Esta sección no tiene ítems. Usa "Agregar ítem".
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="button" class="btn btn-sm btn-outline-primary btn-agregar-item">
                <i class="bi bi-plus-lg me-1"></i>Agregar ítem
            </button>
        </div>
    </div>
</template>

<template id="tpl-item">
    <tr class="item-row table-success">
        <td>
            <input type="text" name="secciones[__S__][items][__I__][nombre]"
                   class="form-control form-control-sm border-0 item-nombre"
                   placeholder="Nombre del nuevo ítem" required>
        </td>
        <td class="text-center">
            <input type="number" name="secciones[__S__][items][__I__][orden]"
                   class="form-control form-control-sm text-center border-0 item-orden"
                   value="0" min="0" required style="width: 50px; margin: 0 auto;">
        </td>
        <td class="text-center">
            <input type="hidden" name="secciones[__S__][items][__I__][es_critico]" value="0">
            <input type="checkbox" class="form-check-input" name="secciones[__S__][items][__I__][es_critico]" value="1">
        </td>
        <td class="text-center">
            <input type="hidden" name="secciones[__S__][items][__I__][activo]" value="0">
            <input type="checkbox" class="form-check-input" name="secciones[__S__][items][__I__][activo]" value="1" checked>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-quitar-item" title="Quitar ítem">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
(function () {
    const form        = document.getElementById('form-plantilla');
    const contenedor  = document.getElementById('secciones-container');
    const avisoVacio  = document.getElementById('sin-secciones');
    let contador      = 0;
    let hayCambios    = false;

    // Clave única para elementos nuevos (no choca con los índices numéricos existentes)
    const nuevaClave = () => 'n' + (++contador);

    function crearDesdeTemplate(id, reemplazos) {
        let html = document.getElementById(id).innerHTML;
        for (const [buscar, valor] of Object.entries(reemplazos)) {
            html = html.split(buscar).join(valor);
        }
        const tpl = document.createElement('template');
        tpl.innerHTML = html.trim();
        return tpl.content.firstElementChild;
    }

    function siguienteOrden(inputs) {
        let max = -1;
        inputs.forEach(i => {
            const v = parseInt(i.value, 10);
            if (!isNaN(v) && v > max) max = v;
        });
        return max + 1;
    }

    function renumerarSecciones() {
        const cards = contenedor.querySelectorAll('.seccion-card');
        cards.forEach((card, i) => card.querySelector('.seccion-numero').textContent = i + 1);
        avisoVacio.classList.toggle('d-none', cards.length > 0);
    }

    function actualizarFilaVacia(card) {
        const hayItems = card.querySelectorAll('.item-row').length > 0;
        card.querySelector('.fila-vacia').classList.toggle('d-none', hayItems);
    }

    function agregarItem(card) {
        const tbody = card.querySelector('.items-body');
        const fila  = crearDesdeTemplate('tpl-item', {
            '__S__': card.dataset.seccionKey,
            '__I__': nuevaClave(),
        });
        fila.querySelector('.item-orden').value = siguienteOrden(tbody.querySelectorAll('.item-orden'));
        tbody.appendChild(fila);
        actualizarFilaVacia(card);
        fila.querySelector('.item-nombre').focus();
        hayCambios = true;
    }

    // ── Agregar sección ──
    document.getElementById('btn-agregar-seccion').addEventListener('click', () => {
        const card = crearDesdeTemplate('tpl-seccion', { '__S__': nuevaClave() });
        card.querySelector('.seccion-orden').value = siguienteOrden(contenedor.querySelectorAll('.seccion-orden'));
        contenedor.appendChild(card);
        renumerarSecciones();
        agregarItem(card); // se parte con un ítem vacío
        card.scrollIntoView({ behavior: 'smooth', block: 'start' });
        card.querySelector('.seccion-nombre').focus();
    });

    // ── Botones dentro de las secciones (delegación de eventos) ──
    contenedor.addEventListener('click', (e) => {
        // Agregar ítem
        const btnAgregar = e.target.closest('.btn-agregar-item');
        if (btnAgregar) {
            agregarItem(btnAgregar.closest('.seccion-card'));
            return;
        }

        // Quitar ítem
        const btnItem = e.target.closest('.btn-quitar-item');
        if (btnItem) {
            const fila   = btnItem.closest('.item-row');
            const card   = fila.closest('.seccion-card');
            const nombre = fila.querySelector('.item-nombre').value || 'Este ítem';

            if (fila.dataset.existente === '1') {
                const msg = fila.dataset.historial === '1'
                    ? `"${nombre}" tiene respuestas o hallazgos en inspecciones anteriores.\n\nPara no perder ese historial se DESACTIVARÁ (no se borrará) al guardar.\n\n¿Continuar?`
                    : `"${nombre}" se eliminará definitivamente al guardar los cambios.\n\n¿Continuar?`;
                if (!confirm(msg)) return;
            }

            fila.remove();
            actualizarFilaVacia(card);
            hayCambios = true;
            return;
        }

        // Quitar sección
        const btnSeccion = e.target.closest('.btn-quitar-seccion');
        if (btnSeccion) {
            const card   = btnSeccion.closest('.seccion-card');
            const nombre = card.querySelector('.seccion-nombre').value || 'Esta sección';

            if (card.dataset.existente === '1') {
                const msg = card.dataset.historial === '1'
                    ? `"${nombre}" tiene ítems con historial de inspecciones.\n\nLos ítems sin historial se eliminarán; los que tienen historial y la sección quedarán DESACTIVADOS al guardar.\n\n¿Continuar?`
                    : `"${nombre}" y todos sus ítems se eliminarán definitivamente al guardar.\n\n¿Continuar?`;
                if (!confirm(msg)) return;
            } else if (card.querySelector('.item-nombre')?.value && !confirm(`¿Descartar la sección "${nombre}"?`)) {
                return;
            }

            card.remove();
            renumerarSecciones();
            hayCambios = true;
        }
    });

    // Título de la sección se actualiza al escribir el nombre
    contenedor.addEventListener('input', (e) => {
        if (e.target.classList.contains('seccion-nombre')) {
            e.target.closest('.seccion-card').querySelector('.seccion-titulo').textContent =
                e.target.value || 'Nueva sección';
        }
    });

    // Aviso si se intenta salir sin guardar
    form.addEventListener('input',  () => hayCambios = true);
    form.addEventListener('change', () => hayCambios = true);
    form.addEventListener('submit', () => hayCambios = false);
    window.addEventListener('beforeunload', (e) => {
        if (hayCambios) { e.preventDefault(); e.returnValue = ''; }
    });
})();
</script>
@endsection