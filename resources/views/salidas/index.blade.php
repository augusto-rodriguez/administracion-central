@extends('layouts.app')
@section('title', 'Salidas de Unidades')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-arrow-up-right-circle me-2"></i>Salidas de Unidades</h4>
    <button class="btn btn-danger btn-lg" data-bs-toggle="modal" data-bs-target="#modalNuevaSalida">
        <i class="bi bi-arrow-up-right-circle me-1"></i>Registrar Salida
    </button>
</div>

{{-- MODAL SALIDA --}}
<div class="modal fade" id="modalNuevaSalida" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    <h5 class="modal-title mb-0">
                        <i class="bi bi-arrow-up-right-circle me-2"></i>Registrar Nueva Salida
                    </h5>
                    {{-- Toggle individual / conjunta --}}
                    <div class="btn-group btn-group-sm ms-2" role="group" id="toggleModoSalida">
                        <input type="radio" class="btn-check" name="modoSalida" id="modoIndividual" autocomplete="off" checked>
                        <label class="btn btn-outline-light" for="modoIndividual">
                            <i class="bi bi-truck me-1"></i>Individual
                        </label>
                        <input type="radio" class="btn-check" name="modoSalida" id="modoConjunta" autocomplete="off">
                        <label class="btn btn-outline-light" for="modoConjunta">
                            <i class="bi bi-collection me-1"></i>Conjunta
                        </label>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('salidas.store') }}" method="POST" id="formSalida">
                @csrf
                <input type="hidden" name="salida_at" id="salidaAtAjustada">

                <div class="modal-body">

                    {{-- ══════════════════════════════════════════════════════
                         DATOS COMPARTIDOS (siempre visibles)
                    ══════════════════════════════════════════════════════ --}}
                    <div class="row g-3">

                        {{-- Clave --}}
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Clave <span class="text-danger">*</span></label>
                            <select name="clave_salida_id" id="selectClave"
                                    class="form-select @error('clave_salida_id') is-invalid @enderror" required>
                                <option value="">Seleccionar clave...</option>
                                <optgroup label="🚨 Emergencias">
                                    @foreach($claves->where('tipo', 'emergencia') as $clave)
                                        <option value="{{ $clave->id }}" data-tipo="emergencia"
                                                {{ old('clave_salida_id') == $clave->id ? 'selected' : '' }}>
                                            {{ $clave->codigo }} — {{ $clave->descripcion }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="⚙️ Administrativas">
                                    @foreach($claves->where('tipo', 'administrativa') as $clave)
                                        <option value="{{ $clave->id }}" data-tipo="administrativa"
                                                {{ old('clave_salida_id') == $clave->id ? 'selected' : '' }}>
                                            {{ $clave->codigo }} — {{ $clave->descripcion }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('clave_salida_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Oficial autorizante (solo para administrativas) --}}
                        <div class="col-md-4" id="bloqueOficial" style="display:none">
                            <label class="form-label fw-bold">
                                Oficial autorizante <span class="text-danger">*</span>
                            </label>
                            <select name="oficial_id" id="selectOficial" class="form-select">
                                <option value="">Seleccionar oficial...</option>
                                @foreach($oficiales as $oficial)
                                    <option value="{{ $oficial->id }}" {{ old('oficial_id') == $oficial->id ? 'selected' : '' }}>
                                        {{ $oficial->nombre }} — {{ $oficial->compania->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Dirección --}}
                        <div class="col-md-10">
                            <label class="form-label fw-bold">Dirección / Lugar <span class="text-danger">*</span></label>
                            <input type="text" name="direccion"
                                   class="form-control @error('direccion') is-invalid @enderror"
                                   value="{{ old('direccion') }}" placeholder="Ej: Av. Principal 123" required>
                            @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Hora de salida --}}
                        <div class="col-md-2">
                            <label class="form-label fw-bold">
                                <i class="bi bi-clock me-1"></i>Hora de salida
                            </label>
                            <input type="time" id="inputHoraSalida" class="form-control" max="">
                            <div class="text-muted small mt-1" id="horaIndicador" style="font-size:11px">
                                <i class="bi bi-arrow-repeat me-1"></i>Actualizando...
                            </div>
                        </div>

                    </div>{{-- /row datos compartidos --}}

                    <hr class="my-3">

                    {{-- ══════════════════════════════════════════════════════
                         MODO INDIVIDUAL
                    ══════════════════════════════════════════════════════ --}}
                    <div id="seccionIndividual">
                        <div class="row g-3">

                            {{-- Unidad --}}
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Unidad <span class="text-danger">*</span></label>
                                <select name="unidad_id" id="selectUnidad"
                                        class="form-select @error('unidad_id') is-invalid @enderror" required>
                                    <option value="">Seleccionar unidad...</option>
                                    @foreach($unidades as $unidad)
                                        <option value="{{ $unidad->id }}" {{ old('unidad_id') == $unidad->id ? 'selected' : '' }}>
                                            {{ $unidad->nombre }} — {{ $unidad->compania->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unidad_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Aviso sin conductor en turno --}}
                            <div class="col-12 d-none" id="alertaSinConductor">
                                <div class="alert alert-warning py-2 mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Sin conductor en turno activo.</strong>
                                    Selecciona un conductor autorizado para esta unidad.
                                </div>
                            </div>

                            {{-- Selector de conductor autorizado (sin turno) --}}
                            <div class="col-md-5 d-none" id="bloqueConductorAutorizado">
                                <label class="form-label fw-bold">
                                    Conductor autorizado
                                    <span class="badge bg-warning text-dark ms-1" style="font-size:10px">Sin turno activo</span>
                                </label>
                                <select id="selectConductorAutorizado" class="form-select">
                                    <option value="">Cargando...</option>
                                </select>
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-info-circle me-1"></i>Conductores habilitados para esta unidad aunque no estén en turno.
                                </div>
                            </div>

                            {{-- Conductor en turno (modo normal) --}}
                            <div class="col-md-5" id="bloqueConductorTurno">
                                <label class="form-label fw-bold">Conductor</label>
                                <select name="conductor_id" id="selectConductor" class="form-select">
                                    <option value="">Sin asignar...</option>
                                    @foreach($conductores as $conductor)
                                        <option value="{{ $conductor['id'] }}"
                                                class="{{ $conductor['tipo'] === 'cuartelero' ? 'text-primary' : '' }}">
                                            {{ $conductor['nombre'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Input oculto que recibe el conductor_id cuando se usa el selector de autorizados --}}
                            <input type="hidden" id="conductorAutorizadoId">
                            {{-- Al Mando --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Voluntario al Mando
                                </label>
                                <select name="al_mando_id" id="selectOficialAlMando" class="form-select @error('al_mando_id') is-invalid @enderror">
                                    <option value="">— Sin voluntario al mando (cuartelero solo) —</option>
                                    @foreach($voluntariosAlMando as $voluntario)
                                        <option value="{{ $voluntario->id }}" {{ old('al_mando_id') == $voluntario->id ? 'selected' : '' }}>
                                            {{ $voluntario->nombre }} — {{ $voluntario->compania->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('al_mando_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            {{-- Km Salida --}}
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Km Salida</label>
                                <input type="number" name="km_salida" id="kmSalida" step="1"
                                       class="form-control @error('km_salida') is-invalid @enderror"
                                       value="{{ old('km_salida') }}" placeholder="Se cargará automático">
                                <div class="text-muted small mt-1" id="kmReferenciaTexto"></div>
                                @error('km_salida') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Personal --}}
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Cantidad Personal</label>
                                <input type="number" name="cantidad_personal" class="form-control"
                                       value="{{ old('cantidad_personal') }}" placeholder="Opcional" min="1">
                            </div>

                            {{-- Observaciones --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Observaciones</label>
                                <input type="text" name="observaciones" class="form-control"
                                       value="{{ old('observaciones') }}" placeholder="Opcional...">
                            </div>

                        </div>
                    </div>{{-- /seccionIndividual --}}

                    {{-- ══════════════════════════════════════════════════════
                         MODO CONJUNTA
                    ══════════════════════════════════════════════════════ --}}
                    <div id="seccionConjunta" style="display:none">

                        <div class="alert alert-info py-2 mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Clave, dirección y hora son compartidas. Agrega una fila por cada unidad despachada.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-2" id="tablaUnidadesConjunta">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:180px">Unidad <span class="text-danger">*</span></th>
                                        <th style="width:200px">Conductor</th>
                                        <th style="width:190px">Al Mando <span class="text-danger">*</span></th>
                                        <th style="width:100px">Km Salida</th>
                                        <th style="width:90px">Personal</th>
                                        <th style="width:160px">Observaciones</th>
                                        <th style="width:44px"></th>
                                    </tr>
                                </thead>
                                <tbody id="filasUnidades">
                                    {{-- Las filas se agregan por JS --}}
                                </tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarUnidad">
                            <i class="bi bi-plus-circle me-1"></i>Agregar unidad
                        </button>

                        <div class="text-muted small mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Conductores disponibles son los que tienen turno activo en cada unidad.
                        </div>

                    </div>{{-- /seccionConjunta --}}

                </div>{{-- /modal-body --}}

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="btnSubmitSalida">
                        <i class="bi bi-arrow-up-right-circle me-1"></i>
                        <span id="labelSubmitSalida">Registrar Salida</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal retomar turno cuartelero --}}
@if(session('sugerir_retomar') && session('retomar_turno_cuartelero'))
@php $retomar = session('retomar_turno_cuartelero'); @endphp
<div class="modal fade" id="modalRetornarTurno" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-primary">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat me-2"></i>¿Retomar turno del cuartelero?
                </h5>
            </div>
            <div class="modal-body">
                <p>El cuartelero ha regresado. Las siguientes unidades están siendo conducidas por maquinistas:</p>
                <ul class="mb-3">
                    @foreach($retomar['conflictos'] as $conflicto)
                    <li>
                        Unidad <strong>{{ $conflicto['unidad_nombre'] }}</strong>
                        — Maquinista: <strong>{{ $conflicto['maquinista'] }}</strong>
                    </li>
                    @endforeach
                </ul>
                <p class="mb-0 text-muted small">Al aceptar, se liberarán estas unidades de los maquinistas y el cuartelero retomará su turno completo.</p>
            </div>
            <div class="modal-footer">
                <form action="{{ route('salidas.retornar-turno-cuartelero') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-repeat me-1"></i>Sí, retomar turno
                    </button>
                </form>
                <form action="{{ route('salidas.retornar-turno-cuartelero-cancelar') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">
                        No, mantener como está
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('modalRetornarTurno')).show();
    });
</script>
@endif

{{-- ══════════════════════════════════════════════════════════════════
     SALIDAS ACTIVAS
══════════════════════════════════════════════════════════════════ --}}
@if($salidasActivas->count())
<div class="card mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-activity text-danger me-2"></i>Unidades Fuera del Cuartel
        <span class="badge bg-danger ms-1">{{ $salidasActivas->count() }}</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Unidad</th>
                    <th>Clave / Destino actual</th>
                    <th>Conductor</th>
                    <th>Al Mando</th>
                    <th>Salida</th>
                    <th>Tiempo</th>
                    <th>Km salida</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($salidasActivas as $salida)
                @php
                    // $salida YA ES el tramo activo (raíz o sobresalida con llegada_at = null).
                    // Si es sobresalida, obtenemos la raíz para mostrar datos originales.
                    $raiz = $salida->esSobresalida() ? $salida->salidaPadre : $salida;
                    $esCadena = $salida->esSobresalida() || $salida->sobresalidas->count() > 0;
                    // Número de tramo: 1 = raíz, 2+ = sobresalidas
                    $numTramo = $salida->esSobresalida()
                        ? ($raiz->sobresalidas->count() + 1)
                        : 1;
                    // km_salida efectivo: el del tramo activo o el heredado de la raíz
                    $kmSalidaEfectivo = $salida->km_salida ?? ($salida->esSobresalida() ? $raiz->km_salida : null);
                @endphp
                <tr>
                    {{-- Unidad --}}
                    <td class="fw-bold">
                        {{ $salida->unidad->nombre }}
                        <div class="text-muted small">{{ $salida->unidad->compania->nombre }}</div>
                    </td>

                    {{-- Clave y destino del tramo activo --}}
                    <td>
                        @if($salida->claveSalida->tipo === 'emergencia')
                            <span class="badge bg-danger">{{ $salida->claveSalida->codigo }}</span>
                        @else
                            <span class="badge bg-primary">{{ $salida->claveSalida->codigo }}</span>
                        @endif
                        <div class="text-muted small" style="font-size:11px">
                            {{ Str::limit($salida->direccion, 35) }}
                        </div>
                        @if($esCadena)
                        <div class="mt-1">
                            <span class="badge bg-warning text-dark" style="font-size:10px">
                                <i class="bi bi-arrow-right-circle me-1"></i>
                                Tramo {{ $numTramo }} — cadena desde {{ $raiz->salida_at->format('H:i') }}
                            </span>
                        </div>
                        @endif
                    </td>

                    {{-- Conductor --}}
                    <td>{{ $salida->conductor_nombre }}</td>

                    {{-- Al mando del tramo activo --}}
                    <td>{{ $salida->alMando?->nombre ?? '—' }}</td>

                    {{-- Hora de salida de la raíz (cronómetro total desde que salió) --}}
                    <td>{{ $raiz->salida_at->format('d/m/Y H:i') }}</td>

                    {{-- Cronómetro desde la raíz --}}
                    <td>
                        <span class="badge bg-warning text-dark cronometro"
                              data-salida="{{ $raiz->salida_at->timestamp }}">
                            Calculando...
                        </span>
                    </td>

                    {{-- Km del tramo activo --}}
                    <td>{{ formatKm($salida->km_salida) }}</td>

                    {{-- Acciones --}}
                    <td class="text-nowrap">
                        {{-- Llegada única: cierra el tramo activo y la unidad vuelve al cuartel --}}
                        <button class="btn btn-sm btn-success"
                                data-bs-toggle="modal"
                                data-bs-target="#modalLlegada{{ $salida->id }}">
                            <i class="bi bi-arrow-down-left-circle me-1"></i>Llegada
                        </button>

                        {{-- Sobresalida: deriva al nuevo destino sin regresar --}}
                        <a href="{{ route('salidas.sobresalida.create', $salida) }}"
                           class="btn btn-sm btn-warning"
                           title="Derivar a otro destino sin regresar al cuartel">
                            <i class="bi bi-arrow-right-circle me-1"></i>Sobresalida
                        </a>

                        {{-- Ver detalle de la raíz --}}
                        <a href="{{ route('salidas.show', $raiz) }}"
                           class="btn btn-sm btn-outline-secondary"
                           title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>

                {{-- Modal de Llegada — único por tramo activo, cierra solo ese tramo --}}
                <div class="modal fade" id="modalLlegada{{ $salida->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-arrow-down-left-circle me-2"></i>
                                    Registrar Llegada — {{ $salida->unidad->nombre }}
                                    @if($esCadena)
                                        <span class="badge bg-warning text-dark ms-2" style="font-size:11px">
                                            Tramo {{ $numTramo }}
                                        </span>
                                    @endif
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('salidas.llegada', $salida) }}" method="POST">
                                @csrf
                                <input type="hidden" name="llegada_at" class="llegada-at-oculto"
                                       data-salida-id="{{ $salida->id }}">
                                <div class="modal-body">

                                    {{-- ── Línea de tiempo de tramos (solo si hay cadena) ── --}}
                                    @if($esCadena)
                                    <div class="mb-3">

                                        {{-- Tramo raíz --}}
                                        <div class="d-flex align-items-start gap-2 mb-2">
                                            <div class="text-center flex-shrink-0" style="width:36px">
                                                <span class="badge bg-danger rounded-pill">{{ $raiz->claveSalida->codigo }}</span>
                                            </div>
                                            <div class="flex-grow-1 border rounded p-2 bg-light">
                                                <div class="fw-bold fs-5 lh-1">{{ $raiz->salida_at->format('H:i') }}</div>
                                                <div class="text-muted small">{{ $raiz->salida_at->format('d/m/Y') }}</div>
                                                <div class="text-muted small mt-1">{{ Str::limit($raiz->direccion, 40) }}</div>
                                            </div>
                                        </div>

                                        {{-- Tramos intermedios (sobresalidas cerradas, todas excepto la actual) --}}
                                        @foreach($raiz->sobresalidas->where('id', '!=', $salida->id)->sortBy('salida_at') as $tramo)
                                        <div class="d-flex align-items-start gap-2 mb-2 ms-2">
                                            <div class="text-center flex-shrink-0" style="width:36px">
                                                <div style="width:2px; height:8px; background:#dee2e6; margin: 0 auto 4px;"></div>
                                                <span class="badge bg-warning text-dark rounded-pill" style="font-size:10px">{{ $tramo->claveSalida->codigo }}</span>
                                            </div>
                                            <div class="flex-grow-1 border rounded p-2" style="font-size:13px">
                                                <div class="fw-bold">{{ $tramo->salida_at->format('H:i') }}</div>
                                                <div class="text-muted">{{ Str::limit($tramo->direccion, 40) }}</div>
                                            </div>
                                        </div>
                                        @endforeach

                                        {{-- Tramo actual (el que se está cerrando) --}}
                                        <div class="d-flex align-items-start gap-2 ms-2">
                                            <div class="text-center flex-shrink-0" style="width:36px">
                                                <div style="width:2px; height:8px; background:#dee2e6; margin: 0 auto 4px;"></div>
                                                <span class="badge bg-warning text-dark rounded-pill" style="font-size:10px">{{ $salida->claveSalida->codigo }}</span>
                                            </div>
                                            <div class="flex-grow-1 border border-success rounded p-2 bg-success bg-opacity-10" style="font-size:13px">
                                                <div class="fw-bold">{{ $salida->salida_at->format('H:i') }}
                                                    <span class="badge bg-success ms-1" style="font-size:10px">tramo actual</span>
                                                </div>
                                                <div class="text-muted">{{ Str::limit($salida->direccion, 40) }}</div>
                                            </div>
                                        </div>

                                    </div>
                                    @else
                                    {{-- Sin cadena: cuadros originales hora salida / hora llegada --}}
                                    <div class="alert alert-info py-2 mb-3">
                                        <strong>Destino:</strong> {{ $salida->direccion }}<br>
                                        <strong>Clave:</strong> {{ $salida->claveSalida->codigo }} — {{ $salida->claveSalida->descripcion }}
                                    </div>
                                    @endif

                                    @if($esCadena)
                                    {{-- Con cadena: solo el input de llegada (la línea de tiempo ya muestra las horas) --}}
                                    <div class="mb-3">
                                        <div class="border rounded p-2 bg-success bg-opacity-10 border-success">
                                            <div class="text-muted small mb-1 text-center">
                                                <i class="bi bi-arrow-down-left-circle me-1"></i>Hora llegada al cuartel
                                            </div>
                                            <input type="time"
                                                   class="form-control form-control-sm fw-bold text-center llegada-time-input"
                                                   id="inputLlegada{{ $salida->id }}"
                                                   data-salida-id="{{ $salida->id }}"
                                                   max="">
                                            <div class="text-muted text-center mt-1 llegada-indicador"
                                                 id="llegadaIndicador{{ $salida->id }}"
                                                 style="font-size:10px">
                                                <i class="bi bi-arrow-repeat me-1"></i>Hora actual
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    {{-- Sin cadena: cuadros originales hora salida | hora llegada --}}
                                    <div class="row g-2 mb-3">
                                        <div class="col-5">
                                            <div class="border rounded p-2 text-center bg-light">
                                                <div class="text-muted small mb-1">
                                                    <i class="bi bi-arrow-up-right-circle me-1"></i>Hora salida
                                                </div>
                                                <div class="fw-bold fs-5">
                                                    {{ $salida->salida_at->format('H:i') }}
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $salida->salida_at->format('d/m/Y') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-7">
                                            <div class="border rounded p-2 bg-success bg-opacity-10 border-success">
                                                <div class="text-muted small mb-1 text-center">
                                                    <i class="bi bi-arrow-down-left-circle me-1"></i>Hora llegada
                                                </div>
                                                <input type="time"
                                                       class="form-control form-control-sm fw-bold text-center llegada-time-input"
                                                       id="inputLlegada{{ $salida->id }}"
                                                       data-salida-id="{{ $salida->id }}"
                                                       max="">
                                                <div class="text-muted text-center mt-1 llegada-indicador"
                                                     id="llegadaIndicador{{ $salida->id }}"
                                                     style="font-size:10px">
                                                    <i class="bi bi-arrow-repeat me-1"></i>Hora actual
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    @if(!$kmSalidaEfectivo)
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            Km Salida <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" name="km_salida" step="1"
                                               class="form-control km-salida-input"
                                               data-salida-id="{{ $salida->id }}"
                                               placeholder="Solo números sin puntos ni guiones" required>
                                        <div class="text-muted small mt-1">
                                            <i class="bi bi-info-circle me-1"></i>Ingresa solo números, sin puntos ni guiones
                                        </div>
                                    </div>
                                    @else
                                    <div class="mb-3">
                                        <div class="alert alert-secondary py-2">
                                            <strong>Km salida:</strong> {{ formatKm($kmSalidaEfectivo) }}
                                            @if($salida->esSobresalida() && !$salida->km_salida)
                                                <span class="text-muted small ms-1">(heredado de la raíz)</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endif

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            Km Llegada <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" name="km_llegada" step="1"
                                               class="form-control form-control-lg km-llegada-input"
                                               data-salida-id="{{ $salida->id }}"
                                               data-km-salida="{{ $kmSalidaEfectivo }}"
                                               min="{{ $kmSalidaEfectivo }}"
                                               placeholder="Solo números sin puntos ni guiones" required>
                                        <div class="text-muted small mt-1">
                                            <i class="bi bi-info-circle me-1"></i>Ingresa solo números, sin puntos ni guiones
                                        </div>
                                        <div class="mt-2 d-none km-resumen" id="kmResumen{{ $salida->id }}">
                                            <div class="alert py-2 mb-0" id="kmResumenAlert{{ $salida->id }}">
                                                <i class="bi bi-signpost-2 me-1"></i>
                                                Km recorridos: <strong id="kmRecorridos{{ $salida->id }}">—</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Observaciones</label>
                                        <textarea name="observaciones" class="form-control" rows="2"
                                                  placeholder="Opcional...">{{ $salida->observaciones }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-lg me-1"></i>Confirmar Llegada
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════
     HISTORIAL
══════════════════════════════════════════════════════════════════ --}}
<div class="card">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-list-ul me-2"></i>Historial de Salidas
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Unidad</th>
                    <th>Clave</th>
                    <th>Dirección</th>
                    <th>Conductor</th>
                    <th>Al Mando</th>
                    <th>Salida</th>
                    <th>Llegada</th>
                    <th>Tiempo</th>
                    <th>Km recorridos</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($historial as $salida)
                <tr>
                    <td class="fw-bold">
                        {{ $salida->unidad->nombre }}
                        <div class="text-muted small">{{ $salida->unidad->compania->nombre }}</div>
                    </td>
                    <td>
                        @if($salida->claveSalida->tipo === 'emergencia')
                            <span class="badge bg-danger">{{ $salida->claveSalida->codigo }}</span>
                        @else
                            <span class="badge bg-primary">{{ $salida->claveSalida->codigo }}</span>
                        @endif
                        @if($salida->sobresalidas->count() > 0)
                        <div class="mt-1">
                            <span class="badge bg-warning text-dark" style="font-size:10px">
                                <i class="bi bi-arrow-right-circle me-1"></i>
                                {{ $salida->sobresalidas->count() + 1 }} tramos
                            </span>
                        </div>
                        @endif
                    </td>
                    <td>{{ $salida->direccion }}</td>
                    <td>{{ $salida->conductor_nombre }}</td>
                    <td>{{ $salida->alMando?->nombre ?? '—' }}</td>
                    <td>{{ $salida->salida_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $salida->llegada_at->format('d/m/Y H:i') }}</td>
                    <td><span class="badge bg-secondary">{{ $salida->tiempo_formateado }}</span></td>
                    <td>
                        @if($salida->km_recorrido !== null)
                            {{ formatKm($salida->km_recorrido) }}
                            @if($salida->sobresalidas->count() > 0)
                                <div class="text-muted" style="font-size:10px">total cadena</div>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('salidas.show', $salida) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if($salida->esEditable())
                        <a href="{{ route('salidas.edit', $salida) }}" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">No hay salidas registradas</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-3 d-flex justify-content-center">
            {{ $historial->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const conductorPorUnidad = @json($conductorPorUnidad);
const opcionesUnidades    = @json($unidades->map(fn($u) => ['id' => $u->id, 'nombre' => $u->nombre . ' — ' . $u->compania->nombre]));
const opcionesVoluntarios = @json($voluntariosAlMando->map(fn($v) => ['id' => $v->id, 'nombre' => $v->nombre . ' — ' . $v->compania->nombre]));
const opcionesConductores = @json($conductores);

// ── Toggle modo individual / conjunta ──
const modoIndividualRadio = document.getElementById('modoIndividual');
const modoConjuntaRadio   = document.getElementById('modoConjunta');
const seccionIndividual   = document.getElementById('seccionIndividual');
const seccionConjunta     = document.getElementById('seccionConjunta');
const labelSubmit         = document.getElementById('labelSubmitSalida');

function aplicarModo() {
    const esConjunta = modoConjuntaRadio.checked;

    seccionIndividual.style.display = esConjunta ? 'none' : '';
    seccionConjunta.style.display   = esConjunta ? ''     : 'none';
    labelSubmit.textContent         = esConjunta ? 'Registrar Salida Conjunta' : 'Registrar Salida';

    document.getElementById('selectUnidad').required         = !esConjunta;
    document.getElementById('selectOficialAlMando').required = !esConjunta;

    if (esConjunta && document.querySelectorAll('#filasUnidades tr').length === 0) {
        agregarFilaUnidad();
        agregarFilaUnidad();
    }
}

modoIndividualRadio.addEventListener('change', aplicarModo);
modoConjuntaRadio.addEventListener('change', aplicarModo);

// ── Oficial autorizante: solo visible para salidas administrativas ──
const selectClave   = document.getElementById('selectClave');
const bloqueOficial = document.getElementById('bloqueOficial');
const selectOficial = document.getElementById('selectOficial');

function actualizarOficial() {
    const option = selectClave.options[selectClave.selectedIndex];
    const tipo   = option?.dataset.tipo;

    if (tipo === 'administrativa') {
        bloqueOficial.style.display = '';
        selectOficial.setAttribute('required', 'required');
    } else {
        bloqueOficial.style.display = 'none';
        selectOficial.removeAttribute('required');
        selectOficial.value = '';
    }
}

selectClave.addEventListener('change', actualizarOficial);
actualizarOficial();

// ── Autocompletar conductor según unidad (modo individual) ──
document.getElementById('selectUnidad').addEventListener('change', function() {
    const unidadId               = this.value;
    const kmInput                = document.getElementById('kmSalida');
    const kmTexto                = document.getElementById('kmReferenciaTexto');
    const selectConductor        = document.getElementById('selectConductor');
    const btnSubmit              = document.querySelector('.modal-footer .btn-danger');
    const alertaConductor        = document.getElementById('alertaSinConductor');
    const bloqueAutorizado       = document.getElementById('bloqueConductorAutorizado');
    const selectAutorizado       = document.getElementById('selectConductorAutorizado');
    const conductorAutorizadoId  = document.getElementById('conductorAutorizadoId');
    const bloqueTurno            = document.getElementById('bloqueConductorTurno');

    // Reset estado
    conductorAutorizadoId.name = '';  // desvincular del form hasta que aplique

    if (unidadId && conductorPorUnidad[unidadId]) {
        // ── Hay conductor en turno: autocarga y bloquea el selector ──
        const conductor = conductorPorUnidad[unidadId];
        const optionId  = conductor.tipo === 'maquinista'
            ? 'v_' + conductor.id
            : 'c_' + conductor.id;

        selectConductor.value               = optionId;
        selectConductor.style.pointerEvents = 'none';
        selectConductor.style.opacity       = '0.7';
        selectConductor.removeAttribute('disabled');
        selectConductor.name                = 'conductor_id';  // campo activo
        conductorAutorizadoId.name          = '';              // inactivo

        alertaConductor.classList.add('d-none');
        bloqueAutorizado.classList.add('d-none');
        bloqueTurno.classList.remove('d-none');
        btnSubmit.disabled = false;

    } else if (unidadId) {
        // ── Sin conductor en turno: ocultar selector de turno, mostrar autorizados ──
        selectConductor.value               = '';
        selectConductor.style.pointerEvents = 'none';
        selectConductor.style.opacity       = '0.5';
        selectConductor.name                = '';  // desactivar para no enviar vacío
        conductorAutorizadoId.name          = 'conductor_id';  // este pasa al back

        alertaConductor.classList.remove('d-none');
        bloqueAutorizado.classList.remove('d-none');
        bloqueTurno.classList.add('d-none');  // ocultar el selector de turno
        btnSubmit.disabled = false;

        // Cargar conductores autorizados vía AJAX
        selectAutorizado.innerHTML = '<option value="">Cargando...</option>';
        fetch(`/salidas/conductores-autorizados/${unidadId}`)
            .then(r => r.json())
            .then(data => {
                if (data.conductores && data.conductores.length > 0) {
                    selectAutorizado.innerHTML = '<option value="">— Sin conductor —</option>';
                    data.conductores.forEach(c => {
                        const opt   = document.createElement('option');
                        opt.value   = c.id;
                        opt.text    = c.nombre;
                        opt.classList.toggle('text-primary', c.tipo === 'cuartelero');
                        selectAutorizado.appendChild(opt);
                    });
                } else {
                    selectAutorizado.innerHTML = '<option value="">Sin conductores autorizados para esta unidad</option>';
                }
                // Sincronizar al input oculto que va al backend
                selectAutorizado.addEventListener('change', function() {
                    conductorAutorizadoId.value = this.value;
                });
            })
            .catch(() => {
                selectAutorizado.innerHTML = '<option value="">Error al cargar conductores</option>';
            });

    } else {
        // ── Sin unidad seleccionada: estado neutro ──
        selectConductor.style.pointerEvents = '';
        selectConductor.style.opacity       = '';
        selectConductor.name                = 'conductor_id';
        selectConductor.removeAttribute('disabled');
        conductorAutorizadoId.name          = '';

        alertaConductor.classList.add('d-none');
        bloqueAutorizado.classList.add('d-none');
        bloqueTurno.classList.remove('d-none');
        btnSubmit.disabled = false;
    }

    if (!unidadId) {
        kmInput.value   = '';
        kmTexto.textContent = '';
        return;
    }

    fetch(`/salidas/ultimo-km/${unidadId}`)
        .then(r => r.json())
        .then(data => {
            if (data.km) {
                const km = Math.round(data.km);
                kmInput.value     = km;
                kmTexto.innerHTML = `<i class="bi bi-info-circle me-1"></i>Último km: <strong>${km.toLocaleString('es-CL')} km</strong> (${data.fecha})`;
            } else {
                kmInput.value     = '';
                kmTexto.innerHTML = '<i class="bi bi-exclamation-circle me-1 text-warning"></i>Sin historial de km';
            }
        });
});

const selectOficialAlMando = new TomSelect('#selectOficialAlMando', {
    placeholder: 'Buscar voluntario...',
    searchField: ['text'],
    maxOptions: 50,
    onChange: function(value) {
        document.getElementById('selectOficialAlMando').dispatchEvent(new Event('change'));
    }
});

// ── Cálculo km recorridos en tiempo real para cada modal de llegada ──
document.querySelectorAll('.km-llegada-input').forEach(function(inputLlegada) {
    const salidaId = inputLlegada.dataset.salidaId;

    function getKmSalida() {
        const kmSalidaFijo = parseFloat(inputLlegada.dataset.kmSalida);
        if (kmSalidaFijo) return kmSalidaFijo;
        const inputKmSalida = document.querySelector(
            `#modalLlegada${salidaId} .km-salida-input`
        );
        return inputKmSalida ? parseFloat(inputKmSalida.value) : null;
    }

    inputLlegada.addEventListener('input', function() {
        const kmLlegada = parseFloat(this.value);
        const kmSalida  = getKmSalida();
        const resumen   = document.getElementById(`kmResumen${salidaId}`);
        const alerta    = document.getElementById(`kmResumenAlert${salidaId}`);
        const texto     = document.getElementById(`kmRecorridos${salidaId}`);

        if (!kmSalida || isNaN(kmLlegada) || this.value === '') {
            resumen.classList.add('d-none');
            return;
        }

        const recorridos = kmLlegada - kmSalida;
        resumen.classList.remove('d-none');

        if (recorridos < 0) {
            alerta.className = 'alert alert-danger py-2 mb-0';
            texto.textContent = '⚠ El km de llegada no puede ser menor al de salida';
        } else if (recorridos === 0) {
            alerta.className = 'alert alert-warning py-2 mb-0';
            texto.textContent = '0 km (sin desplazamiento)';
        } else if (recorridos > 500) {
            alerta.className = 'alert alert-warning py-2 mb-0';
            texto.textContent = `${recorridos.toLocaleString('es-CL')} km ⚠ Verifica el valor`;
        } else {
            alerta.className = 'alert alert-success py-2 mb-0';
            texto.textContent = `${recorridos.toLocaleString('es-CL')} km`;
        }
    });

    const inputKmSalida = document.querySelector(
        `#modalLlegada${salidaId} .km-salida-input`
    );
    if (inputKmSalida) {
        inputKmSalida.addEventListener('input', function() {
            inputLlegada.dispatchEvent(new Event('input'));
        });
    }
});

// ── Cronómetros ──
function actualizarCronometros() {
    document.querySelectorAll('.cronometro').forEach(el => {
        const salida   = parseInt(el.dataset.salida);
        const ahora    = Math.floor(Date.now() / 1000);
        const diff     = ahora - salida;
        const horas    = Math.floor(diff / 3600);
        const minutos  = Math.floor((diff % 3600) / 60);
        const segundos = diff % 60;
        const pad = n => String(n).padStart(2, '0');
        el.textContent = `${pad(horas)}:${pad(minutos)}:${pad(segundos)}`;
        if (diff > 10800) {
            el.className = 'badge bg-danger cronometro';
        } else if (diff > 3600) {
            el.className = 'badge bg-warning text-dark cronometro';
        } else {
            el.className = 'badge bg-success cronometro';
        }
    });
}
actualizarCronometros();
setInterval(actualizarCronometros, 1000);

// ── Hora de salida ──
const inputHoraSalida  = document.getElementById('inputHoraSalida');
const salidaAtOculto   = document.getElementById('salidaAtAjustada');
const horaIndicador    = document.getElementById('horaIndicador');

let horaModificadaManualmente = false;

function horaActualLocal() {
    const ahora = new Date();
    const hh = String(ahora.getHours()).padStart(2, '0');
    const mm = String(ahora.getMinutes()).padStart(2, '0');
    return `${hh}:${mm}`;
}

function fechaHoyLocal() {
    const hoy = new Date();
    const yyyy = hoy.getFullYear();
    const mm   = String(hoy.getMonth() + 1).padStart(2, '0');
    const dd   = String(hoy.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
}

function sincronizarCampoOculto(hora) {
    salidaAtOculto.value = `${fechaHoyLocal()} ${hora}:00`;
}

function tickHora() {
    if (!horaModificadaManualmente) {
        const ahora = horaActualLocal();
        inputHoraSalida.value = ahora;
        sincronizarCampoOculto(ahora);
        horaIndicador.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Hora actual (se actualiza sola)';
    }
}

tickHora();
const intervaloHora = setInterval(tickHora, 30000);

inputHoraSalida.addEventListener('change', function () {
    const horaElegida = this.value;
    const ahora = horaActualLocal();

    if (horaElegida > ahora) {
        this.value = ahora;
        sincronizarCampoOculto(ahora);
        horaIndicador.innerHTML = '<i class="bi bi-exclamation-triangle me-1 text-warning"></i>No puedes seleccionar una hora futura';
        horaModificadaManualmente = false;
        return;
    }

    horaModificadaManualmente = true;
    sincronizarCampoOculto(horaElegida);

    if (horaElegida === ahora) {
        horaIndicador.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Hora actual (se actualiza sola)';
        horaModificadaManualmente = false;
    } else {
        horaIndicador.innerHTML = `<i class="bi bi-clock-history me-1 text-warning"></i><strong>Hora ajustada: ${horaElegida}</strong>`;
    }
});

document.getElementById('modalNuevaSalida').addEventListener('show.bs.modal', function () {
    horaModificadaManualmente = false;
    tickHora();
});

// ── Filas dinámicas para salida conjunta ──
let filaIndex = 0;

function buildSelect(name, opts, placeholder, required = false) {
    const req = required ? 'required' : '';
    let html = `<select name="${name}" class="form-select form-select-sm" ${req}>`;
    html += `<option value="">${placeholder}</option>`;
    opts.forEach(o => {
        html += `<option value="${o.id}">${o.nombre}</option>`;
    });
    html += '</select>';
    return html;
}

function buildConductorSelect(idx, unidadId) {
    let opts = [];
    if (unidadId && conductorPorUnidad[unidadId]) {
        const c = conductorPorUnidad[unidadId];
        const val = (c.tipo === 'maquinista' ? 'v_' : 'c_') + c.id;
        opts = [{ id: val, nombre: c.nombre }];
    }
    let html = `<select name="unidades[${idx}][conductor_id]" class="form-select form-select-sm cj-conductor">`;
    html += `<option value="">— sin conductor —</option>`;
    opts.forEach(o => { html += `<option value="${o.id}" selected>${o.nombre}</option>`; });
    html += '</select>';
    return html;
}

// Carga asíncrona de conductores autorizados para una celda de fila conjunta
function cargarConductoresAutorizadosCJ(idx, unidadId, cell) {
    fetch(`/salidas/conductores-autorizados/${unidadId}`)
        .then(r => r.json())
        .then(data => {
            const select = cell.querySelector('.cj-conductor');
            if (!select) return;
            if (data.conductores && data.conductores.length > 0) {
                // Agregar opciones de autorizados con indicador visual
                const sep = document.createElement('option');
                sep.disabled = true;
                sep.text = '── Sin turno activo ──';
                select.appendChild(sep);
                data.conductores.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.text  = c.nombre;
                    select.appendChild(opt);
                });
            }
        })
        .catch(() => { /* sin autorizados: el campo libre ya está */ });
}

function agregarFilaUnidad() {
    const idx   = filaIndex++;
    const tbody = document.getElementById('filasUnidades');
    const tr    = document.createElement('tr');
    tr.dataset.idx = idx;

    let selectUnidadHtml = `<select name="unidades[${idx}][unidad_id]" class="form-select form-select-sm cj-unidad" required>`;
    selectUnidadHtml += '<option value="">Seleccionar...</option>';
    opcionesUnidades.forEach(u => {
        selectUnidadHtml += `<option value="${u.id}">${u.nombre}</option>`;
    });
    selectUnidadHtml += '</select>';
    selectUnidadHtml += '<div class="text-muted small mt-1 cj-km-ref" style="font-size:11px"></div>';

    let selectMandoHtml = `<select name="unidades[${idx}][al_mando_id]" class="form-select form-select-sm cj-mando" required>`;
    selectMandoHtml += '<option value="">Seleccionar...</option>';
    opcionesVoluntarios.forEach(v => {
        selectMandoHtml += `<option value="${v.id}">${v.nombre}</option>`;
    });
    selectMandoHtml += '</select>';

    tr.innerHTML = `
        <td>${selectUnidadHtml}</td>
        <td class="cj-conductor-cell"><span class="text-muted small">Selecciona unidad primero</span></td>
        <td>${selectMandoHtml}</td>
        <td><input type="number" name="unidades[${idx}][km_salida]" class="form-control form-control-sm cj-km" step="1" placeholder="Automático"></td>
        <td><input type="number" name="unidades[${idx}][cantidad_personal]" class="form-control form-control-sm" min="1" placeholder="—"></td>
        <td><input type="text" name="unidades[${idx}][observaciones]" class="form-control form-control-sm" placeholder="Opcional"></td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm cj-quitar" title="Quitar fila">
                <i class="bi bi-x-lg"></i>
            </button>
        </td>`;

    tbody.appendChild(tr);

    tr.querySelector('.cj-unidad').addEventListener('change', function() {
        const unidadId = this.value;
        const cell     = tr.querySelector('.cj-conductor-cell');
        const kmInput  = tr.querySelector('.cj-km');
        const kmRef    = tr.querySelector('.cj-km-ref');

        cell.innerHTML = buildConductorSelect(idx, unidadId);

        if (unidadId && !conductorPorUnidad[unidadId]) {
            cell.insertAdjacentHTML('afterbegin',
                '<div class="text-warning small mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Sin turno — elige de los autorizados</div>');
            // Cargar autorizados asincrónicamente en el select de esta fila
            cargarConductoresAutorizadosCJ(idx, unidadId, cell);
        }

        if (unidadId) {
            fetch(`/salidas/ultimo-km/${unidadId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.km) {
                        const km = Math.round(data.km);
                        kmInput.value   = km;
                        kmRef.innerHTML = `<i class="bi bi-info-circle me-1"></i>Último: <strong>${km.toLocaleString('es-CL')} km</strong> (${data.fecha})`;
                    } else {
                        kmInput.value   = '';
                        kmRef.innerHTML = '<i class="bi bi-exclamation-circle me-1 text-warning"></i>Sin historial';
                    }
                });
        } else {
            kmInput.value   = '';
            kmRef.innerHTML = '';
        }
    });

    tr.querySelector('.cj-quitar').addEventListener('click', function() {
        const filas = document.querySelectorAll('#filasUnidades tr');
        if (filas.length <= 2) {
            alert('La salida conjunta requiere al menos 2 unidades.');
            return;
        }
        tr.remove();
    });
}

document.getElementById('btnAgregarUnidad').addEventListener('click', agregarFilaUnidad);

// ── Reset al cerrar el modal ──
document.getElementById('modalNuevaSalida').addEventListener('hidden.bs.modal', function() {
    horaModificadaManualmente = false;
    tickHora();
    modoIndividualRadio.checked = true;
    aplicarModo();
    document.getElementById('filasUnidades').innerHTML = '';
    filaIndex = 0;
});

// ── Hora de llegada — input editable en cada modal ──
(function() {
    const estadoLlegada = {};

    function hhMM() {
        const d = new Date();
        return String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
    }

    function fechaHoy() {
        const d = new Date();
        return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    }

    function sincronizarLlegada(salidaId, hora) {
        const oculto = document.querySelector(`#modalLlegada${salidaId} .llegada-at-oculto`);
        if (oculto) oculto.value = `${fechaHoy()} ${hora}:00`;
    }

    function tickLlegada(salidaId) {
        if (estadoLlegada[salidaId]?.modificada) return;
        const ahora = hhMM();
        const input = document.getElementById('inputLlegada' + salidaId);
        const ind   = document.getElementById('llegadaIndicador' + salidaId);
        if (input) input.value = ahora;
        if (ind)   ind.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Hora actual (se actualiza sola)';
        sincronizarLlegada(salidaId, ahora);
    }

    document.querySelectorAll('.modal[id^="modalLlegada"]').forEach(function(modal) {
        const salidaId = modal.id.replace('modalLlegada', '');
        estadoLlegada[salidaId] = { modificada: false, intervalo: null };

        modal.addEventListener('show.bs.modal', function() {
            estadoLlegada[salidaId].modificada = false;
            tickLlegada(salidaId);
            clearInterval(estadoLlegada[salidaId].intervalo);
            estadoLlegada[salidaId].intervalo = setInterval(() => tickLlegada(salidaId), 30000);
        });

        modal.addEventListener('hidden.bs.modal', function() {
            clearInterval(estadoLlegada[salidaId].intervalo);
            estadoLlegada[salidaId].modificada = false;
        });

        const input = document.getElementById('inputLlegada' + salidaId);
        const ind   = document.getElementById('llegadaIndicador' + salidaId);
        if (!input) return;

        input.addEventListener('change', function() {
            const ahora = hhMM();
            if (this.value > ahora) {
                this.value = ahora;
                if (ind) ind.innerHTML = '<i class="bi bi-exclamation-triangle me-1 text-warning"></i>No puedes seleccionar una hora futura';
                estadoLlegada[salidaId].modificada = false;
                sincronizarLlegada(salidaId, ahora);
                return;
            }
            estadoLlegada[salidaId].modificada = true;
            sincronizarLlegada(salidaId, this.value);
            if (this.value === ahora) {
                if (ind) ind.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Hora actual (se actualiza sola)';
                estadoLlegada[salidaId].modificada = false;
            } else {
                if (ind) ind.innerHTML = `<i class="bi bi-clock-history me-1 text-warning"></i><strong>Hora ajustada: ${this.value}</strong>`;
            }
        });
    });
})();

@if($errors->any())
    var modal = new bootstrap.Modal(document.getElementById('modalNuevaSalida'));
    modal.show();
@endif
</script>
@endpush