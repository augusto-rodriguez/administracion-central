@extends('layouts.app')
@section('title', 'Guardia Nocturna — ' . $guardia->fecha->format('d/m/Y'))
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-moon-stars me-2"></i>Guardia Nocturna
        </h4>
        <small class="text-muted">
            {{ $guardia->fecha->format('d/m/Y') }} —
            Cerrada a las {{ $guardia->cerrado_at?->format('H:i') ?? '—' }}
            por {{ $guardia->cerradoPor->nombre ?? '—' }}
        </small>
    </div>
    <div class="d-flex align-items-center gap-3">

        {{-- Total voluntarios --}}
        @php
            $totalVoluntarios = $guardia->companias
                ->where('sin_reporte', false)
                ->sum(fn($c) => $c->voluntarios->count());
        @endphp
        <div class="text-center">
            <div class="badge bg-success fs-5 px-3 py-2">
                <i class="bi bi-people me-1"></i>
                {{ $totalVoluntarios }}
            </div>
            <div class="text-muted small mt-1">voluntarios en guardia</div>
        </div>

        <a href="{{ route('guardias-nocturnas.pdf', $guardia) }}"
           class="btn btn-outline-danger btn-sm" target="_blank">
            <i class="bi bi-file-earmark-pdf me-1"></i>Exportar PDF
        </a>

        <a href="{{ auth()->user()->esAdmin() || auth()->user()->esComandante()
                    ? route('reportes.guardias-nocturnas') . '?tab=historial'
                    : route('guardias-nocturnas.index') }}"
        class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

@foreach($guardia->companias as $gnCompania)
<div class="card mb-4">
    <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-building me-2"></i>
            {{ $gnCompania->compania->numero }}ª Compañía —
            {{ $gnCompania->compania->nombre }}
        </span>
        @if($gnCompania->sin_reporte)
            <span class="badge bg-danger">Sin reporte</span>
        @else
            <span class="badge bg-success">
                <i class="bi bi-check2 me-1"></i>Reportado
            </span>
        @endif
    </div>

    @if($gnCompania->sin_reporte)
        <div class="card-body">
            <p class="text-muted small mb-0">
                <i class="bi bi-exclamation-triangle text-danger me-1"></i>
                Esta compañía no reportó guardia nocturna.
                @if($gnCompania->observaciones)
                    <br><strong>Observación:</strong> {{ $gnCompania->observaciones }}
                @endif
            </p>
        </div>
    @else
        <div class="card-body">
            <div class="row g-4">

                {{-- Columna izquierda --}}
                <div class="col-md-6">
                    <h6 class="fw-bold mb-2">
                        <i class="bi bi-shield me-1 text-primary"></i>Oficial o Vol a cargo
                    </h6>
                    <p class="mb-3">{{ $gnCompania->oficialACargo->nombre ?? '—' }}</p>

                    <h6 class="fw-bold mb-2">
                        <i class="bi bi-person-gear me-1 text-info"></i>Cuartelero de turno
                    </h6>
                    <p class="mb-0">{{ $gnCompania->cuartelero?->nombre ?? '—' }}</p>
                </div>

                {{-- Columna derecha --}}
                <div class="col-md-6">
                    <h6 class="fw-bold mb-2">
                        <i class="bi bi-people me-1 text-success"></i>
                        Voluntarios en guardia
                        <span class="badge bg-success ms-1">{{ $gnCompania->voluntarios->count() }}</span>
                    </h6>
                    @if($gnCompania->voluntarios->isNotEmpty())
                        <ul class="list-group list-group-flush mb-2">
                            @foreach($gnCompania->voluntarios as $gnVol)
                                <li class="list-group-item px-0 py-1 border-0 d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="bi bi-person-fill text-success me-1"></i>
                                        {{ $gnVol->voluntario->nombre ?? '—' }}
                                    </span>
                                    @php
                                        $horaIngreso = $gnVol->hora_ingreso
                                            ? \Carbon\Carbon::parse($gnVol->hora_ingreso)->format('H:i')
                                            : '01:00';
                                        $esTardio = $gnVol->hora_ingreso !== null;
                                    @endphp
                                    <span class="badge {{ $esTardio ? 'bg-warning text-dark' : 'bg-secondary' }} ms-2">
                                        <i class="bi bi-clock me-1"></i>{{ $horaIngreso }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted small mb-2">Sin voluntarios registrados.</p>
                    @endif

                    {{-- Botones solo para operadores --}}
                    @if(!auth()->user()->esAdmin() && !auth()->user()->esComandante())
                        <button type="button"
                                class="btn btn-sm btn-outline-success"
                                onclick="abrirModalAgregar({{ $gnCompania->compania_id }}, '{{ $gnCompania->compania->nombre }}')">
                            <i class="bi bi-person-plus me-1"></i>Agregar voluntario
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary ms-1"
                                onclick="abrirModalObservacion({{ $gnCompania->id }}, '{{ $gnCompania->compania->nombre }}', '{{ addslashes($gnCompania->observaciones ?? '') }}')">
                            <i class="bi bi-chat-text me-1"></i>
                            {{ $gnCompania->observaciones ? 'Editar observaciones' : 'Observaciones' }}
                        </button>
                    @endif
                </div>

                {{-- Unidades — fila completa --}}
                <div class="col-12">
                    <h6 class="fw-bold mb-2">
                        <i class="bi bi-truck-front me-1 text-danger"></i>
                        Unidades en servicio
                        <span class="badge bg-danger ms-1">{{ $gnCompania->unidades->count() }}</span>
                    </h6>
                    @if($gnCompania->unidades->isNotEmpty())
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
                                    </td>
                                    <td>{{ $u->maquinista?->nombre ?? $u->cuartelero?->nombre ?? '—' }}</td>
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
                        <p class="text-muted small mb-0">Sin unidades registradas.</p>
                    @endif
                </div>

                {{-- Observaciones --}}
                @if($gnCompania->observaciones)
                <div class="col-12">
                    <h6 class="fw-bold mb-1">
                        <i class="bi bi-chat-text me-1 text-secondary"></i>Observaciones
                    </h6>
                    <p class="mb-0 text-muted" style="white-space: pre-wrap;">
                        {{ $gnCompania->observaciones }}
                    </p>
                </div>
                @endif

            </div>
        </div>
    @endif
</div>
@endforeach

@if($guardia->companias->isEmpty())
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        No se registró ninguna compañía en esta guardia nocturna.
    </div>
@endif

{{-- Modales solo para operadores --}}
@if(!auth()->user()->esAdmin() && !auth()->user()->esComandante())

    {{-- Modal observación --}}
    <div class="modal fade" id="modalObservacion" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white py-2">
                    <h6 class="modal-title mb-0" id="modalObservacionTitulo">
                        <i class="bi bi-chat-text me-2"></i>Observación
                    </h6>
                    <button type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal"></button>
                </div>
                <form id="formObservacion" method="POST">
                    @csrf
                    <div class="modal-body">
                        <label class="form-label fw-bold">Observación</label>
                        <textarea name="observaciones" id="textoObservacion"
                                  class="form-control" rows="4"
                                  placeholder="Escribe la observación..."></textarea>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-secondary btn-sm">
                            <i class="bi bi-floppy me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal agregar voluntario --}}
    <div class="modal fade" id="modalAgregarVoluntario" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white py-2">
                    <h6 class="modal-title mb-0" id="modalAgregarTitulo">
                        <i class="bi bi-person-plus me-2"></i>Agregar voluntario
                    </h6>
                    <button type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('guardias-nocturnas.agregar-voluntario', $guardia) }}"
                      method="POST">
                    @csrf
                    <input type="hidden" name="compania_id" id="modalAgregarCompaniaId">
                    <div class="modal-body">
                        <label class="form-label fw-bold">Voluntario</label>
                        <select name="voluntario_id" id="selectAgregarVoluntario"
                                class="form-select" required>
                            <option value="">Buscar voluntario...</option>
                        </select>
                        <div class="mt-3">
                            <label class="form-label fw-bold">
                                Hora de ingreso
                                <span class="text-muted fw-normal small">
                                    (opcional — dejar vacío si ingresó antes del cierre)
                                </span>
                            </label>
                            <input type="time" name="hora_ingreso" id="inputHoraIngreso"
                                   class="form-control">
                            <div class="form-text">
                                Si el voluntario se integró después del cierre,
                                registra la hora exacta. De lo contrario déjalo vacío
                                y se asumirá ingreso a las 01:00.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-person-plus me-1"></i>Agregar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endif

@push('scripts')
<script>
@if(!auth()->user()->esAdmin() && !auth()->user()->esComandante())

const voluntariosPorCompania = {
    @foreach($guardia->companias as $gnComp)
    {{ $gnComp->compania_id }}: [
        @foreach($gnComp->compania->voluntarios->where('activo', true) as $vol)
            { id: {{ $vol->id }}, nombre: '{{ addslashes($vol->nombre) }}' },
        @endforeach
    ],
    @endforeach
};

let tsAgregar = null;

function abrirModalAgregar(companiaId, companiaNombre) {
    document.getElementById('modalAgregarCompaniaId').value = companiaId;
    document.getElementById('modalAgregarTitulo').innerHTML =
        `<i class="bi bi-person-plus me-2"></i>Agregar voluntario — ${companiaNombre}`;

    if (tsAgregar) {
        tsAgregar.destroy();
        tsAgregar = null;
    }

    const select = document.getElementById('selectAgregarVoluntario');
    select.innerHTML = '<option value="">Buscar voluntario...</option>';

    const voluntarios = voluntariosPorCompania[companiaId] ?? [];
    voluntarios.forEach(v => {
        const opt = document.createElement('option');
        opt.value = v.id;
        opt.textContent = v.nombre;
        select.appendChild(opt);
    });

    tsAgregar = new TomSelect(select, {
        allowEmptyOption: true,
        placeholder: 'Buscar voluntario...',
    });

    new bootstrap.Modal(document.getElementById('modalAgregarVoluntario')).show();
}

function abrirModalObservacion(gnCompaniaId, companiaNombre, observacionActual) {
    document.getElementById('modalObservacionTitulo').innerHTML =
        `<i class="bi bi-chat-text me-2"></i>Observación — ${companiaNombre}`;

    document.getElementById('textoObservacion').value = observacionActual;

    document.getElementById('formObservacion').action =
        `/guardias-nocturnas/{{ $guardia->id }}/observacion/${gnCompaniaId}`;

    new bootstrap.Modal(document.getElementById('modalObservacion')).show();
}

@endif
</script>
@endpush

@endsection