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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-up-right-circle me-2"></i>Registrar Nueva Salida
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('salidas.store') }}" method="POST">
                @csrf
                <div class="modal-body">
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

                        {{-- Alerta sin conductor --}}
                        <div class="col-12 d-none" id="alertaSinConductor">
                            <div class="alert alert-warning py-2 mb-0">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Esta unidad no tiene conductor en turno activo. No se puede registrar la salida.
                            </div>
                        </div>

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

                        {{-- Dirección --}}
                        <div class="col-12">
                            <label class="form-label fw-bold">Dirección / Lugar <span class="text-danger">*</span></label>
                            <input type="text" name="direccion"
                                   class="form-control @error('direccion') is-invalid @enderror"
                                   value="{{ old('direccion') }}" placeholder="Ej: Av. Principal 123" required>
                            @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Conductor --}}
                        <div class="col-md-6">
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
                            <input type="text" name="conductor_libre" id="conductorLibre"
                                class="form-control mt-1 d-none"
                                placeholder="O escribe el nombre del conductor...">
                        </div>

                        {{-- Oficial autorizante — solo visible para salidas administrativas --}}
                        <div class="col-md-6" id="bloqueOficial" style="display:none">
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

                        {{-- Al Mando --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Voluntario al Mando <span class="text-danger">*</span>
                            </label>
                            <select name="al_mando_id" id="selectOficialAlMando" class="form-select @error('al_mando_id') is-invalid @enderror" required>
                                <option value="">Seleccionar voluntario...</option>
                                @foreach($voluntariosAlMando as $voluntario)
                                    <option value="{{ $voluntario->id }}" {{ old('al_mando_id') == $voluntario->id ? 'selected' : '' }}>
                                        {{ $voluntario->nombre }} — {{ $voluntario->compania->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('al_mando_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Km Salida --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Km Salida</label>
                            <input type="number" name="km_salida" id="kmSalida" step="1"
                                   class="form-control @error('km_salida') is-invalid @enderror"
                                   value="{{ old('km_salida') }}" placeholder="Se cargará automático">
                            <div class="text-muted small mt-1" id="kmReferenciaTexto"></div>
                            @error('km_salida') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Personal --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Cantidad Personal</label>
                            <input type="number" name="cantidad_personal" class="form-control"
                                   value="{{ old('cantidad_personal') }}" placeholder="Opcional" min="1">
                        </div>

                        {{-- Observaciones --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Observaciones</label>
                            <input type="text" name="observaciones" class="form-control"
                                   value="{{ old('observaciones') }}" placeholder="Opcional...">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-arrow-up-right-circle me-1"></i>Registrar Salida
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

{{-- Salidas activas --}}
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
                    <th>Clave</th>
                    <th>Dirección</th>
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
                        <div class="text-muted small" style="font-size:11px">
                            {{ Str::limit($salida->claveSalida->descripcion, 30) }}
                        </div>
                    </td>
                    <td>{{ $salida->direccion }}</td>
                    <td>{{ $salida->conductor_nombre }}</td>
                    <td>{{ $salida->alMando?->nombre ?? '—' }}</td>
                    <td>{{ $salida->salida_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge bg-warning text-dark cronometro"
                              data-salida="{{ $salida->salida_at->timestamp }}">
                            Calculando...
                        </span>
                    </td>
                    <td>{{ formatKm($salida->km_salida) }}</td>
                    <td>
                        <button class="btn btn-sm btn-success"
                                data-bs-toggle="modal"
                                data-bs-target="#modalLlegada{{ $salida->id }}">
                            <i class="bi bi-arrow-down-left-circle me-1"></i>Llegada
                        </button>
                        <a href="{{ route('salidas.show', $salida) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>

                {{-- Modal Llegada --}}
                <div class="modal fade" id="modalLlegada{{ $salida->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-arrow-down-left-circle me-2"></i>
                                    Registrar Llegada — {{ $salida->unidad->nombre }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('salidas.llegada', $salida) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="alert alert-info py-2 mb-3">
                                        <strong>Destino:</strong> {{ $salida->direccion }}<br>
                                        <strong>Clave:</strong> {{ $salida->claveSalida->codigo }} — {{ $salida->claveSalida->descripcion }}
                                    </div>

                                    @if(!$salida->km_salida)
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
                                            <strong>Km salida:</strong> {{ formatKm($salida->km_salida) }}
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
                                               data-km-salida="{{ $salida->km_salida }}"
                                               min="{{ $salida->km_salida }}"
                                               placeholder="Solo números sin puntos ni guiones" required>
                                        <div class="text-muted small mt-1">
                                            <i class="bi bi-info-circle me-1"></i>Ingresa solo números, sin puntos ni guiones
                                        </div>
                                        {{-- Resumen km recorridos en tiempo real --}}
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

{{-- Historial --}}
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
                    </td>
                    <td>{{ $salida->direccion }}</td>
                    <td>{{ $salida->conductor_nombre }}</td>
                    <td>{{ $salida->alMando?->nombre ?? '—' }}</td>
                    <td>{{ $salida->salida_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $salida->llegada_at->format('d/m/Y H:i') }}</td>
                    <td><span class="badge bg-secondary">{{ $salida->tiempo_formateado }}</span></td>
                    <td>{{ formatKm($salida->km_recorrido) }}</td>
                    <td>
                        <a href="{{ route('salidas.show', $salida) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
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
actualizarOficial(); // Ejecutar al cargar por si hay old() seleccionado

// ── Autocompletar conductor según unidad ──
document.getElementById('selectUnidad').addEventListener('change', function() {
    const unidadId        = this.value;
    const kmInput         = document.getElementById('kmSalida');
    const kmTexto         = document.getElementById('kmReferenciaTexto');
    const selectConductor = document.getElementById('selectConductor');
    const conductorLibre  = document.getElementById('conductorLibre');
    const btnSubmit       = document.querySelector('.modal-footer .btn-danger');
    const alertaConductor = document.getElementById('alertaSinConductor');

    if (unidadId && conductorPorUnidad[unidadId]) {
        const conductor = conductorPorUnidad[unidadId];
        const optionId  = conductor.tipo === 'maquinista'
            ? 'v_' + conductor.id
            : 'c_' + conductor.id;

        selectConductor.value = optionId;
        selectConductor.style.pointerEvents = 'none';
        selectConductor.style.opacity = '0.7';
        selectConductor.removeAttribute('disabled');
        conductorLibre.classList.add('d-none');
        conductorLibre.value = '';
        btnSubmit.disabled = false;
        alertaConductor.classList.add('d-none');

    } else if (unidadId) {
        selectConductor.value = '';
        selectConductor.style.pointerEvents = 'none';
        selectConductor.style.opacity = '0.7';
        selectConductor.removeAttribute('disabled');
        conductorLibre.classList.add('d-none');
        btnSubmit.disabled = true;
        alertaConductor.classList.remove('d-none');

    } else {
        selectConductor.style.pointerEvents = '';
        selectConductor.style.opacity = '';
        selectConductor.removeAttribute('disabled');
        btnSubmit.disabled = false;
        alertaConductor.classList.add('d-none');
    }

    if (!unidadId) {
        kmInput.value = '';
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

document.getElementById('selectConductor').addEventListener('change', function() {
    const libre = document.getElementById('conductorLibre');
    if (this.value) {
        libre.classList.add('d-none');
        libre.value = '';
    } else {
        libre.classList.remove('d-none');
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

@if($errors->any())
    var modal = new bootstrap.Modal(document.getElementById('modalNuevaSalida'));
    modal.show();
@endif
</script>
@endpush