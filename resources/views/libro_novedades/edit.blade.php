@extends('layouts.app')

@section('title', 'Libro de Novedades — ' . $libro->turno_label)

@section('content')

{{-- Cabecera --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-journal-text me-2"></i>
            Libro de Novedades —
            @if($libro->turno === 'dia')
                <span class="badge bg-warning text-dark fs-6"><i class="bi bi-sun me-1"></i>Turno Día</span>
            @else
                <span class="badge bg-dark fs-6"><i class="bi bi-moon-stars me-1"></i>Turno Noche</span>
            @endif
        </h4>
        <small class="text-muted">{{ $libro->fecha->format('d/m/Y') }}</small>
    </div>
    <a href="{{ route('libro-novedades.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>


<form action="{{ route('libro-novedades.update', $libro) }}" method="POST" id="formLibro">
    @csrf @method('PUT')

    {{-- ── BLOQUE 1: DATOS DEL TURNO ──────────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-clock me-2"></i>Datos del Turno
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Fecha</label>
                    <input type="text" class="form-control" value="{{ $libro->fecha->format('d/m/Y') }}" disabled>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Turno</label>
                    <input type="text" class="form-control"
                           value="{{ $libro->turno === 'dia' ? 'Día' : 'Noche' }}" disabled>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Hora inicio <span class="text-danger">*</span></label>
                    <input type="time" name="hora_inicio" class="form-control @error('hora_inicio') is-invalid @enderror"
                           value="{{ old('hora_inicio', substr($libro->hora_inicio, 0, 5)) }}" required>
                    @error('hora_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Puede ajustarse si el turno varió del horario estándar.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Hora fin <span class="text-danger">*</span></label>
                    <input type="time" name="hora_fin" class="form-control @error('hora_fin') is-invalid @enderror"
                           value="{{ old('hora_fin', substr($libro->hora_fin, 0, 5)) }}" required>
                    @error('hora_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Operador en turno</label>
                    <input type="text" class="form-control"
                           value="{{ $libro->operador->nombre ?? '—' }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Operador turno anterior</label>
                    <input type="text" class="form-control" value="{{ $libro->operador_turno_anterior ?? '—' }}" disabled>
                </div>
            </div>
        </div>
    </div>

    {{-- ── BLOQUE 2: ESTADO AL RECIBIR EL TURNO ──────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-box-arrow-in-right me-2"></i>Estado al recibir el turno
            <span class="text-muted fw-normal small">(generado automáticamente al iniciar)</span>
        </div>
        <div class="card-body">
            <div class="row g-4">

                {{-- Maquinistas --}}
                <div class="col-md-6">
                    <h6 class="fw-bold mb-2"><i class="bi bi-person-badge me-1"></i>Maquinistas en servicio</h6>
                    @php $maquinistas = $libro->maquinistas_al_recibir ?? []; @endphp
                    @if(count($maquinistas) > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($maquinistas as $m)
                            <li class="list-group-item px-0 py-1">
                                <strong>{{ $m['nombre'] }}</strong>
                                @if(!empty($m['unidades']))
                                    — {{ collect($m['unidades'])->pluck('nombre')->implode(', ') }}
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted small mb-0">Sin maquinistas en servicio al recibir.</p>
                    @endif
                </div>

                {{-- Cuarteleros --}}
                <div class="col-md-6">
                    <h6 class="fw-bold mb-2"><i class="bi bi-person-badge me-1"></i>Cuarteleros en servicio</h6>
                    @php $cuarteleros = $libro->cuarteleros_al_recibir ?? []; @endphp
                    @if(count($cuarteleros) > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($cuarteleros as $c)
                            <li class="list-group-item px-0 py-1">
                                <strong>{{ $c['nombre'] }}</strong>
                                @if(!empty($c['unidades']))
                                    — {{ collect($c['unidades'])->pluck('nombre')->implode(', ') }}
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted small mb-0">Sin cuarteleros en servicio al recibir.</p>
                    @endif
                </div>

                {{-- Unidades fuera de servicio al recibir --}}
                <div class="col-12">
                    <h6 class="fw-bold mb-2"><i class="bi bi-truck-front me-1 text-danger"></i>Unidades fuera de servicio al recibir</h6>
                    @php $fueraRecibir = $libro->unidades_fuera_servicio_al_recibir ?? []; @endphp
                    @if(count($fueraRecibir) > 0)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($fueraRecibir as $u)
                                <span class="badge bg-danger">
                                    {{ $u['nombre'] }}
                                    @if($u['patente']) ({{ $u['patente'] }}) @endif
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small mb-0">Ninguna unidad fuera de servicio al recibir el turno.</p>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- ── BLOQUE 3: NOTAS DEL TURNO ──────────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-pencil-square me-2"></i>Novedades del turno
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label fw-bold">Cronológico de novedades del turno</label>
                    <textarea name="novedades_cronologicas" class="form-control" rows="5"
                              placeholder="Registra cronológicamente las novedades ocurridas durante el turno...">{{ old('novedades_cronologicas', $libro->novedades_cronologicas) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Observaciones en telecomunicaciones</label>
                    <textarea name="observaciones_telecomunicaciones" class="form-control" rows="4"
                              placeholder="Observaciones sobre las telecomunicaciones del turno...">{{ old('observaciones_telecomunicaciones', $libro->observaciones_telecomunicaciones) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Novedades del VIPER</label>
                    <textarea name="novedades_viper" class="form-control" rows="4"
                              placeholder="Novedades del sistema VIPER...">{{ old('novedades_viper', $libro->novedades_viper) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ── BOTONES ─────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-floppy me-1"></i>Guardar cambios
        </button>

        <button type="button" class="btn btn-danger"
                data-bs-toggle="modal" data-bs-target="#modalCerrar">
            <i class="bi bi-lock me-1"></i>Cerrar turno y generar libro
        </button>
    </div>
</form>

{{-- ── MODAL CONFIRMACIÓN CIERRE ──────────────────────────────────────── --}}
<div class="modal fade" id="modalCerrar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-lock me-2"></i>Cerrar turno</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Al cerrar el turno se tomarán los <strong>snapshots finales</strong> de:</p>
                <ul>
                    <li>Maquinistas y cuarteleros en servicio al entregar</li>
                    <li>Unidades fuera de servicio al entregar</li>
                    <li>Salidas administrativas y de emergencia del turno</li>
                    <li>Puestas en servicio realizadas durante el turno</li>
                </ul>
                <p class="text-danger mb-0"><strong>Esta acción no se puede deshacer.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('libro-novedades.cerrar', $libro) }}" method="POST" id="formCerrar">
                    @csrf
                    {{-- Pasa las horas actuales del formulario principal al cerrar --}}
                    <input type="hidden" name="hora_inicio" id="cerrar_hora_inicio">
                    <input type="hidden" name="hora_fin" id="cerrar_hora_fin">
                    <input type="hidden" name="novedades_cronologicas" id="cerrar_novedades_cronologicas">
                    <input type="hidden" name="observaciones_telecomunicaciones" id="cerrar_observaciones_telecomunicaciones">
                    <input type="hidden" name="novedades_viper" id="cerrar_novedades_viper">
                    <input type="hidden" name="operador_turno_anterior" id="cerrar_operador_turno_anterior">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-lock me-1"></i>Confirmar cierre
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')

<script>
    document.getElementById('modalCerrar').addEventListener('show.bs.modal', function () {
        document.getElementById('cerrar_hora_inicio').value = 
            document.querySelector('input[name="hora_inicio"]').value;
        document.getElementById('cerrar_hora_fin').value = 
            document.querySelector('input[name="hora_fin"]').value;

        // ── Copiar también los campos de texto ──────────────────────
        document.getElementById('cerrar_novedades_cronologicas').value = 
            document.querySelector('textarea[name="novedades_cronologicas"]').value;
        document.getElementById('cerrar_observaciones_telecomunicaciones').value = 
            document.querySelector('textarea[name="observaciones_telecomunicaciones"]').value;
        document.getElementById('cerrar_novedades_viper').value = 
            document.querySelector('textarea[name="novedades_viper"]').value;
        document.getElementById('cerrar_operador_turno_anterior').value =
            document.querySelector('input[name="operador_turno_anterior"]') 
                ? document.querySelector('input[name="operador_turno_anterior"]').value 
                : '';
    });
</script>

@endpush