@extends('layouts.app')

@section('title', 'Inspección — ' . $inspeccion->unidad->nombre)

@section('content')
{{-- Encabezado con info de la inspección --}}
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h5 class="mb-1">
            <i class="bi bi-clipboard-check me-2"></i>{{ $inspeccion->unidad->nombre }}
        </h5>
        <small class="text-muted">
            {{ $inspeccion->fecha->format('d/m/Y') }} · {{ $inspeccion->cuartelero->nombre ?? 'Sin cuartelero' }}
        </small>
    </div>
    <a href="{{ route('checklist.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

{{-- Barra de progreso --}}
<div class="mb-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <small class="fw-bold text-muted">Progreso</small>
        <small class="fw-bold" id="progreso-texto">0%</small>
    </div>
    <div class="progress" style="height: 8px;">
        <div class="progress-bar bg-danger" id="progreso-barra" role="progressbar" style="width: 0%"></div>
    </div>
</div>

{{-- Indicador de autoguardado --}}
<div id="autosave-indicator" class="text-end mb-2" style="display: none;">
    <small class="text-success"><i class="bi bi-check-circle me-1"></i>Guardado</small>
</div>

<form id="checklist-form" action="{{ route('checklist.update', $inspeccion) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- Datos operativos --}}
    <div class="card mb-3">
        <div class="card-header bg-dark text-white py-2">
            <i class="bi bi-speedometer2 me-1"></i> Datos operativos de la unidad
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold">Kilometraje</label>
                    <input type="text" name="kilometraje" class="form-control form-control-sm campo-autoguardado"
                           value="{{ old('kilometraje', $inspeccion->kilometraje) }}" placeholder="Ej: 45.230" inputmode="decimal">
                </div>

                @if($plantilla->tipo_unidad === 'liviano')
                    {{-- Campos para vehículos livianos --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold">Próxima mantención</label>
                        <input type="text" name="proxima_mantencion" class="form-control form-control-sm campo-autoguardado"
                               value="{{ old('proxima_mantencion', $inspeccion->proxima_mantencion) }}" placeholder="Ej: 50.000 km">
                    </div>
                @else
                    {{-- Campos para material mayor --}}
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold">Hora Motor</label>
                        <input type="text" name="hora_motor" class="form-control form-control-sm campo-autoguardado"
                               value="{{ old('hora_motor', $inspeccion->hora_motor) }}" placeholder="Ej: 1.250">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold">Hora Bomba</label>
                        <input type="text" name="hora_bomba" class="form-control form-control-sm campo-autoguardado"
                               value="{{ old('hora_bomba', $inspeccion->hora_bomba) }}" placeholder="Ej: 320">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold">Últ. cambio aceite</label>
                        <input type="text" name="hora_ultimo_cambio_aceite" class="form-control form-control-sm campo-autoguardado"
                               value="{{ old('hora_ultimo_cambio_aceite', $inspeccion->hora_ultimo_cambio_aceite) }}" placeholder="Ej: 1.100">
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Secciones del checklist (accordion) --}}
    <div class="accordion" id="checklist-secciones">
        @foreach($plantilla->secciones as $seccionIndex => $seccion)
            @php
                $items = $seccion->itemsActivos;
                $respondidos = $items->filter(fn($item) => $respuestasGuardadas->has($item->id))->count();
                $total = $items->count();
                $seccionCompleta = $respondidos === $total && $total > 0;
            @endphp
            <div class="accordion-item" data-seccion-id="{{ $seccion->id }}">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $seccionIndex > 0 ? 'collapsed' : '' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#seccion-{{ $seccion->id }}">
                        <span class="me-auto">
                            <span class="badge rounded-pill {{ $seccionCompleta ? 'bg-success' : 'bg-secondary' }} me-2 seccion-badge"
                                  data-seccion="{{ $seccion->id }}">
                                {{ $respondidos }}/{{ $total }}
                            </span>
                            {{ $seccion->nombre }}
                        </span>
                    </button>
                </h2>
                <div id="seccion-{{ $seccion->id }}" class="accordion-collapse collapse {{ $seccionIndex === 0 ? 'show' : '' }}"
                     data-bs-parent="#checklist-secciones">
                    <div class="accordion-body p-2 p-md-3">
                        @if($seccion->descripcion)
                            <div class="alert alert-light border py-2 small">
                                <i class="bi bi-info-circle me-1"></i>{{ $seccion->descripcion }}
                            </div>
                        @endif

                        {{-- TIPO NIVEL: 1/4, 1/2, 3/4, FULL, NO APLICA --}}
                        @if($seccion->tipo_respuesta === 'nivel')
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr class="text-center small">
                                            <th class="text-start" style="min-width: 120px;">Ítem</th>
                                            <th style="width: 50px;">1/4</th>
                                            <th style="width: 50px;">1/2</th>
                                            <th style="width: 50px;">3/4</th>
                                            <th style="width: 55px;">FULL</th>
                                            <th style="width: 50px;">N/A</th>
                                            <th style="width: 40px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                            @php $valorGuardado = $respuestasGuardadas[$item->id]->valor ?? null; @endphp
                                            <tr class="{{ $valorGuardado === '1/4' ? 'table-warning' : '' }}">
                                                <td class="small {{ $item->es_critico ? 'fw-bold' : '' }}">
                                                    {{ $item->nombre }}
                                                    @if($item->es_critico)
                                                        <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Ítem crítico"></i>
                                                    @endif
                                                </td>
                                                @foreach(['1/4', '1/2', '3/4', 'full', 'no_aplica'] as $opcion)
                                                    <td class="text-center">
                                                        <input type="radio" class="form-check-input respuesta-radio"
                                                               name="respuestas[{{ $item->id }}]" value="{{ $opcion }}"
                                                               data-item-id="{{ $item->id }}" data-seccion-id="{{ $seccion->id }}"
                                                               {{ $valorGuardado === $opcion ? 'checked' : '' }}>
                                                    </td>
                                                @endforeach
                                                <td class="text-center">
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-secondary btn-foto p-0 px-1 {{ $valorGuardado === '1/4' ? '' : 'd-none' }}"
                                                            data-item-id="{{ $item->id }}"
                                                            title="Adjuntar foto">
                                                        <i class="bi bi-camera"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="foto-row d-none" id="foto-row-{{ $item->id }}">
                                                <td colspan="7" class="bg-light">
                                                    <div class="d-flex align-items-center gap-2 py-1">
                                                        <input type="file" class="form-control form-control-sm foto-input"
                                                               data-item-id="{{ $item->id }}"
                                                               accept="image/*" multiple
                                                               style="max-width: 250px;">
                                                        <div class="foto-preview d-flex gap-1 flex-wrap" id="preview-{{ $item->id }}"></div>
                                                        <div class="foto-status small text-muted" id="status-{{ $item->id }}"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        {{-- TIPO ESTADO: Bueno, Regular, Malo, NO APLICA --}}
                        @elseif($seccion->tipo_respuesta === 'estado')
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr class="text-center small">
                                            <th class="text-start" style="min-width: 120px;">Ítem</th>
                                            <th style="width: 60px;"><span class="text-success">Bueno</span></th>
                                            <th style="width: 60px;"><span class="text-warning">Regular</span></th>
                                            <th style="width: 60px;"><span class="text-danger">Malo</span></th>
                                            <th style="width: 50px;">N/A</th>
                                            <th style="width: 40px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                            @php $valorGuardado = $respuestasGuardadas[$item->id]->valor ?? null; @endphp
                                            <tr class="{{ $valorGuardado === 'malo' ? 'table-danger' : ($valorGuardado === 'regular' ? 'table-warning' : '') }}">
                                                <td class="small {{ $item->es_critico ? 'fw-bold' : '' }}">
                                                    {{ $item->nombre }}
                                                    @if($item->es_critico)
                                                        <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Ítem crítico"></i>
                                                    @endif
                                                </td>
                                                @foreach(['bueno', 'regular', 'malo', 'no_aplica'] as $opcion)
                                                    <td class="text-center">
                                                        <input type="radio" class="form-check-input respuesta-radio"
                                                               name="respuestas[{{ $item->id }}]" value="{{ $opcion }}"
                                                               data-item-id="{{ $item->id }}" data-seccion-id="{{ $seccion->id }}"
                                                               {{ $valorGuardado === $opcion ? 'checked' : '' }}>
                                                    </td>
                                                @endforeach
                                                <td class="text-center">
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-secondary btn-foto p-0 px-1 {{ in_array($valorGuardado, ['regular', 'malo']) ? '' : 'd-none' }}"
                                                            data-item-id="{{ $item->id }}"
                                                            title="Adjuntar foto">
                                                        <i class="bi bi-camera"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="foto-row d-none" id="foto-row-{{ $item->id }}">
                                                <td colspan="6" class="bg-light">
                                                    <div class="d-flex align-items-center gap-2 py-1">
                                                        <input type="file" class="form-control form-control-sm foto-input"
                                                               data-item-id="{{ $item->id }}"
                                                               accept="image/*" multiple
                                                               style="max-width: 250px;">
                                                        <div class="foto-preview d-flex gap-1 flex-wrap" id="preview-{{ $item->id }}"></div>
                                                        <div class="foto-status small text-muted" id="status-{{ $item->id }}"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        {{-- TIPO DOCUMENTO: OK, Vencido --}}
                        @elseif($seccion->tipo_respuesta === 'documento')
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                        <tr class="text-center small">
                                            <th class="text-start" style="min-width: 150px;">Documento</th>
                                            <th style="width: 80px;"><span class="text-success">OK</span></th>
                                            <th style="width: 80px;"><span class="text-danger">Vencido</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                            @php $valorGuardado = $respuestasGuardadas[$item->id]->valor ?? null; @endphp
                                            <tr class="{{ $valorGuardado === 'vencido' ? 'table-danger' : '' }}">
                                                <td class="small fw-bold">{{ $item->nombre }}</td>
                                                @foreach(['ok', 'vencido'] as $opcion)
                                                    <td class="text-center">
                                                        <input type="radio" class="form-check-input respuesta-radio"
                                                               name="respuestas[{{ $item->id }}]" value="{{ $opcion }}"
                                                               data-item-id="{{ $item->id }}" data-seccion-id="{{ $seccion->id }}"
                                                               {{ $valorGuardado === $opcion ? 'checked' : '' }}>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Observaciones --}}
    <div class="card mt-3">
        <div class="card-header bg-dark text-white py-2">
            <i class="bi bi-chat-left-text me-1"></i> Observaciones
        </div>
        <div class="card-body">
            <textarea name="observaciones" class="form-control campo-autoguardado" rows="3"
                      placeholder="Detalles de ítems regulares o malos, acciones tomadas, etc.">{{ old('observaciones', $inspeccion->observaciones) }}</textarea>
        </div>
    </div>

    {{-- Botones de acción --}}
    <div class="d-flex gap-2 mt-4 mb-5">
        <button type="submit" class="btn btn-outline-secondary">
            <i class="bi bi-save me-1"></i>Guardar borrador
        </button>
        <button type="button" class="btn btn-danger flex-grow-1" id="btn-completar"
                data-url="{{ route('checklist.completar', $inspeccion) }}">
            <i class="bi bi-check-circle me-1"></i>Completar y Enviar
        </button>
    </div>
</form>

{{-- Formulario oculto para completar --}}
<form id="form-completar" action="{{ route('checklist.completar', $inspeccion) }}" method="POST" style="display:none;">
    @csrf
</form>
@endsection

@push('scripts')
<style>
    .spin { animation: spin 1s linear infinite; display: inline-block; }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checklist-form');
    const indicator = document.getElementById('autosave-indicator');
    let autoguardadoTimer = null;

    // ════════════════════════════════════════════════
    // PROGRESO
    // ════════════════════════════════════════════════
    function actualizarProgreso() {
        const radios = document.querySelectorAll('.respuesta-radio');
        const nombres = new Set();
        const respondidos = new Set();

        radios.forEach(r => {
            nombres.add(r.name);
            if (r.checked) respondidos.add(r.name);
        });

        const total = nombres.size;
        const hechos = respondidos.size;
        const porcentaje = total > 0 ? Math.round((hechos / total) * 100) : 0;

        document.getElementById('progreso-barra').style.width = porcentaje + '%';
        document.getElementById('progreso-texto').textContent = porcentaje + '%';

        // Actualizar badges de secciones
        document.querySelectorAll('.accordion-item').forEach(item => {
            const secId = item.dataset.seccionId;
            const radiosSeccion = item.querySelectorAll('.respuesta-radio');
            const nombresSeccion = new Set();
            const respondidosSeccion = new Set();

            radiosSeccion.forEach(r => {
                nombresSeccion.add(r.name);
                if (r.checked) respondidosSeccion.add(r.name);
            });

            const badge = item.querySelector('.seccion-badge');
            if (badge) {
                badge.textContent = respondidosSeccion.size + '/' + nombresSeccion.size;
                if (respondidosSeccion.size === nombresSeccion.size && nombresSeccion.size > 0) {
                    badge.classList.remove('bg-secondary');
                    badge.classList.add('bg-success');
                } else {
                    badge.classList.remove('bg-success');
                    badge.classList.add('bg-secondary');
                }
            }
        });
    }

    // ════════════════════════════════════════════════
    // COLOREAR FILAS AL MARCAR
    // ════════════════════════════════════════════════
    function colorearFila(radio) {
        const fila = radio.closest('tr');
        if (!fila) return;

        fila.classList.remove('table-danger', 'table-warning');
        if (radio.value === 'malo' || radio.value === 'vencido') {
            fila.classList.add('table-danger');
        } else if (radio.value === 'regular' || radio.value === '1/4') {
            fila.classList.add('table-warning');
        }
    }

    // ════════════════════════════════════════════════
    // AUTOGUARDADO
    // ════════════════════════════════════════════════
    function autoguardar() {
        clearTimeout(autoguardadoTimer);
        autoguardadoTimer = setTimeout(() => {
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(r => {
                if (r.ok) {
                    indicator.style.display = 'block';
                    setTimeout(() => indicator.style.display = 'none', 2000);
                }
            })
            .catch(() => {});
        }, 1500);
    }

    // ════════════════════════════════════════════════
    // EVENTOS DE RESPUESTAS
    // ════════════════════════════════════════════════
    document.querySelectorAll('.respuesta-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            actualizarProgreso();
            colorearFila(this);
            autoguardar();

            // Mostrar/ocultar botón de cámara según la respuesta
            const itemId = this.dataset.itemId;
            const btnFoto = document.querySelector(`.btn-foto[data-item-id="${itemId}"]`);
            const fotoRow = document.getElementById(`foto-row-${itemId}`);

            if (!btnFoto) return;

            const necesitaFoto = ['regular', 'malo', '1/4'].includes(this.value);

            if (necesitaFoto) {
                btnFoto.classList.remove('d-none');
            } else {
                btnFoto.classList.add('d-none');
                if (fotoRow) fotoRow.classList.add('d-none');
            }
        });
    });

    document.querySelectorAll('.campo-autoguardado').forEach(campo => {
        campo.addEventListener('input', autoguardar);
    });

    // ════════════════════════════════════════════════
    // FOTOS POR ÍTEM
    // ════════════════════════════════════════════════

    // Abrir/cerrar fila de foto
    document.querySelectorAll('.btn-foto').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const fotoRow = document.getElementById(`foto-row-${itemId}`);
            fotoRow.classList.toggle('d-none');
        });
    });

    // Subir fotos por AJAX
    document.querySelectorAll('.foto-input').forEach(input => {
        input.addEventListener('change', function() {
            const itemId = this.dataset.itemId;
            const files = this.files;
            const preview = document.getElementById(`preview-${itemId}`);
            const status = document.getElementById(`status-${itemId}`);

            for (let i = 0; i < files.length; i++) {
                const formData = new FormData();
                formData.append('foto', files[i]);
                formData.append('item_id', itemId);
                formData.append('_token', '{{ csrf_token() }}');

                status.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Subiendo...';

                fetch('{{ route("checklist.subir-foto", $inspeccion) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(r => r.json())
                .then(data => {
                    const img = document.createElement('img');
                    img.src = '/storage/' + data.ruta;
                    img.style.cssText = 'width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;';
                    preview.appendChild(img);
                    status.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Subida</span>';
                    setTimeout(() => status.innerHTML = '', 2000);
                })
                .catch(() => {
                    status.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Error</span>';
                });
            }

            this.value = '';
        });
    });

    // ════════════════════════════════════════════════
    // COMPLETAR
    // ════════════════════════════════════════════════
    document.getElementById('btn-completar').addEventListener('click', function() {
        if (!confirm('¿Estás seguro de completar y enviar esta inspección? Una vez enviada no podrás editarla.')) {
            return;
        }

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(() => {
            document.getElementById('form-completar').submit();
        })
        .catch(() => {
            document.getElementById('form-completar').submit();
        });
    });

    // Inicializar progreso
    actualizarProgreso();
});
</script>
@endpush