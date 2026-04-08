@extends('layouts.app')
@section('title', 'Puestas en Servicio')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-clock-history me-2"></i>Puestas en Servicio</h4>
</div>

{{-- Advertencia unidades en uso --}}
@if(session('unidades_en_uso'))
<div class="alert alert-warning border-warning mb-4">
    <h6 class="fw-bold"><i class="bi bi-exclamation-triangle me-2"></i>¡Atención! Las siguientes unidades ya están en servicio:</h6>
    <ul class="mb-3">
        @foreach(session('unidades_en_uso') as $item)
        <li>
            Unidad <strong>{{ $item['unidad_nombre'] }}</strong> está siendo manejada por
            <strong>{{ $item['voluntario_nombre'] }}</strong>
        </li>
        @endforeach
    </ul>
    <p class="mb-2">¿Desea registrar la salida del conductor actual y registrar la entrada del nuevo?</p>
    <form action="{{ route('turnos.confirmar') }}" method="POST">
        @csrf
        <input type="hidden" name="voluntario_id" value="{{ session('form_data.voluntario_id') }}">
        <input type="hidden" name="observaciones" value="{{ session('form_data.observaciones') }}">
        @foreach(session('form_data.unidades', []) as $uid)
            <input type="hidden" name="unidades[]" value="{{ $uid }}">
        @endforeach
        <button type="submit" class="btn btn-warning">
            <i class="bi bi-arrow-left-right me-1"></i>Sí, registrar cambio
        </button>
        <a href="{{ route('turnos.index') }}" class="btn btn-outline-secondary ms-2">Cancelar</a>
    </form>
</div>
@endif

@if(session('unidades_en_uso_cuartelero'))
<div class="alert alert-warning border-warning mb-4">
    <h6 class="fw-bold"><i class="bi bi-exclamation-triangle me-2"></i>¡Atención! Las siguientes unidades ya están en servicio:</h6>
    <ul class="mb-3">
        @foreach(session('unidades_en_uso_cuartelero') as $item)
        <li>
            Unidad <strong>{{ $item['unidad_nombre'] }}</strong> está siendo manejada por
            <strong>{{ $item['voluntario_nombre'] }}</strong>
        </li>
        @endforeach
    </ul>
    <p class="mb-2">¿Desea registrar la salida del conductor actual y registrar la entrada del cuartelero?</p>
    <form action="{{ route('cuarteleros.turnos.confirmar') }}" method="POST">
        @csrf
        <input type="hidden" name="cuartelero_id" value="{{ session('form_data_cuartelero.cuartelero_id') }}">
        <input type="hidden" name="observaciones" value="{{ session('form_data_cuartelero.observaciones') }}">
        @foreach(session('form_data_cuartelero.unidades', []) as $uid)
            <input type="hidden" name="unidades[]" value="{{ $uid }}">
        @endforeach
        <button type="submit" class="btn btn-warning">
            <i class="bi bi-arrow-left-right me-1"></i>Sí, registrar cambio
        </button>
        <a href="{{ route('turnos.index') }}" class="btn btn-outline-secondary ms-2">Cancelar</a>
    </form>
</div>
@endif

{{-- Formulario único --}}
<div class="card mb-4 border-danger">
    <div class="card-header bg-danger text-white fw-bold">
        <i class="bi bi-box-arrow-in-right me-2"></i>Registrar Entrada
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-12">
                <label class="form-label fw-bold">Tipo de conductor</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_conductor"
                               id="tipoMaquinista" value="maquinista" checked>
                        <label class="form-check-label fw-bold text-danger" for="tipoMaquinista">
                            <i class="bi bi-person-badge me-1"></i>Maquinista 
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_conductor"
                               id="tipoCuartelero" value="cuartelero">
                        <label class="form-check-label fw-bold text-primary" for="tipoCuartelero">
                            <i class="bi bi-person-gear me-1"></i>Cuartelero 
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Formulario Maquinista --}}
        <form id="formMaquinista" action="{{ route('turnos.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Voluntario <span class="text-danger">*</span></label>
                    <select name="voluntario_id"
                            class="form-select @error('voluntario_id') is-invalid @enderror"
                            id="selectVoluntario" required>
                        <option value="">Buscar maquinista...</option>
                        @foreach($voluntarios as $voluntario)
                            <option value="{{ $voluntario->id }}"
                                    {{ old('voluntario_id') == $voluntario->id ? 'selected' : '' }}>
                                {{ $voluntario->nombre }} — {{ $voluntario->compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('voluntario_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Unidades <span class="text-danger">*</span></label>
                    <div id="unidadesContainer" class="border rounded p-2 bg-light" style="min-height:42px">
                        <span class="text-muted small">Selecciona primero un voluntario...</span>
                    </div>
                    @error('unidades') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Observaciones</label>
                    <input type="text" name="observaciones" class="form-control"
                           placeholder="Opcional..." value="{{ old('observaciones') }}">
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Registrar Entrada
                </button>
            </div>
        </form>

        {{-- Formulario Cuartelero --}}
        <form id="formCuartelero" action="{{ route('cuarteleros.turnos.store') }}" method="POST" style="display:none">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Cuartelero <span class="text-danger">*</span></label>
                    <select name="cuartelero_id"
                            class="form-select @error('cuartelero_id') is-invalid @enderror"
                            id="selectCuartelero">
                        <option value="">Buscar cuartelero...</option>
                        @foreach($cuarteleros as $cuartelero)
                            <option value="{{ $cuartelero->id }}"
                                    {{ old('cuartelero_id') == $cuartelero->id ? 'selected' : '' }}>
                                {{ $cuartelero->nombre }} — {{ $cuartelero->compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('cuartelero_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Unidades <span class="text-danger">*</span></label>
                    <div id="unidadesCuarteleroContainer" class="border rounded p-2 bg-light" style="min-height:42px">
                        <span class="text-muted small">Selecciona primero un cuartelero...</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Observaciones</label>
                    <input type="text" name="observaciones" class="form-control" placeholder="Opcional...">
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Registrar Entrada
                </button>
            </div>
        </form>
    </div>
</div>

{{-- En Servicio Ahora (unificado) --}}
@php
    $enServicioMaquinistas = \App\Models\RegistroTurno::with(['voluntario.compania', 'unidades'])
        ->whereNull('salida_at')->orderBy('entrada_at', 'desc')->get();
    $enServicioCuarteleros = \App\Models\RegistroTurnoCuartelero::with(['cuartelero.compania', 'unidades'])
        ->whereNull('salida_at')->orderBy('entrada_at', 'desc')->get();
    $totalEnServicio = $enServicioMaquinistas->count() + $enServicioCuarteleros->count();
@endphp

@if($totalEnServicio)
<div class="card mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-activity text-danger me-2"></i>En Servicio Ahora
        <span class="badge bg-danger ms-1">{{ $totalEnServicio }}</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tipo</th>
                    <th>Nombre</th>
                    <th>Compañía</th>
                    <th>Unidades</th>
                    <th>Entrada</th>
                    <th>Tiempo</th>
                    <th>Observaciones</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($enServicioMaquinistas as $turno)
                <tr class="table-danger bg-opacity-25">
                    <td>
                        <span class="badge bg-danger">
                            <i class="bi bi-person-badge me-1"></i>Maquinista
                        </span>
                    </td>
                    <td class="fw-bold">{{ $turno->voluntario->nombre }}</td>
                    <td>{{ $turno->voluntario->compania->nombre }}</td>
                    <td>
                        @foreach($turno->unidades as $unidad)
                            <span class="badge bg-secondary me-1 mb-1"
                                role="button"
                                style="cursor:pointer"
                                data-bs-toggle="modal"
                                data-bs-target="#modalQuitarUnidad{{ $turno->id }}_{{ $unidad->id }}">
                                <i class="bi bi-truck-front me-1"></i>{{ $unidad->nombre }}
                            </span>

                            {{-- Modal quitar unidad --}}
                            <div class="modal fade" id="modalQuitarUnidad{{ $turno->id }}_{{ $unidad->id }}" tabindex="-1">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">
                                        <div class="modal-header py-2">
                                            <h6 class="modal-title">
                                                <i class="bi bi-truck-front me-1"></i>{{ $unidad->nombre }}
                                            </h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body py-2 text-center">
                                            <p class="mb-3 text-muted small">¿Quitar esta unidad del turno activo?</p>
                                            <form action="{{ route('turnos.quitar-unidad', [$turno, $unidad]) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                                    <i class="bi bi-dash-circle me-1"></i>Quitar del turno
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </td>
                    <td>{{ $turno->entrada_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge cronometro" data-entrada="{{ $turno->entrada_at->timestamp }}">
                            Calculando...
                        </span>
                    </td>
                    <td>{{ $turno->observaciones ?? '—' }}</td>
                    <td>
                        <form action="{{ route('turnos.salida', $turno) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-danger" onclick="return confirm('¿Registrar salida?')">
                                <i class="bi bi-box-arrow-right me-1"></i>Salida
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach

                @foreach($enServicioCuarteleros as $turno)
                <tr class="table-primary bg-opacity-25">
                    <td>
                        <span class="badge bg-primary">
                            <i class="bi bi-person-gear me-1"></i>Cuartelero
                        </span>
                    </td>
                    <td class="fw-bold">{{ $turno->cuartelero->nombre }}</td>
                    <td>{{ $turno->cuartelero->compania->nombre }}</td>
                    <td>
                        @foreach($turno->unidades as $unidad)
                            <span class="badge bg-secondary me-1 mb-1"
                                role="button"
                                style="cursor:pointer"
                                data-bs-toggle="modal"
                                data-bs-target="#modalQuitarUnidadC{{ $turno->id }}_{{ $unidad->id }}">
                                <i class="bi bi-truck-front me-1"></i>{{ $unidad->nombre }}
                            </span>

                            {{-- Modal quitar unidad --}}
                            <div class="modal fade" id="modalQuitarUnidadC{{ $turno->id }}_{{ $unidad->id }}" tabindex="-1">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">
                                        <div class="modal-header py-2">
                                            <h6 class="modal-title">
                                                <i class="bi bi-truck-front me-1"></i>{{ $unidad->nombre }}
                                            </h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body py-2 text-center">
                                            <p class="mb-3 text-muted small">¿Quitar esta unidad del turno activo?</p>
                                            <form action="{{ route('cuarteleros.turnos.quitar-unidad', [$turno, $unidad]) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                                    <i class="bi bi-dash-circle me-1"></i>Quitar del turno
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </td>
                    <td>{{ $turno->entrada_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge cronometro" data-entrada="{{ $turno->entrada_at->timestamp }}">
                            Calculando...
                        </span>
                    </td>
                    <td>{{ $turno->observaciones ?? '—' }}</td>
                    <td>
                        <form action="{{ route('cuarteleros.turnos.salida', $turno) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-primary" onclick="return confirm('¿Registrar salida?')">
                                <i class="bi bi-box-arrow-right me-1"></i>Salida
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Historial unificado --}}
<div class="card">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-list-ul me-2"></i>Historial de Turnos
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tipo</th>
                    <th>Nombre</th>
                    <th>Compañía</th>
                    <th>Unidades</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Tiempo</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
        <tbody>
            @forelse($turnos as $item)
            <tr>
                <td>
                    @if($item->tipo === 'maquinista')
                        <span class="badge bg-danger">
                            <i class="bi bi-person-badge me-1"></i>Maquinista
                        </span>
                    @else
                        <span class="badge bg-primary">
                            <i class="bi bi-person-gear me-1"></i>Cuartelero
                        </span>
                    @endif
                </td>
                <td class="fw-bold">
                    {{ $item->tipo === 'maquinista' ? $item->voluntario->nombre : $item->cuartelero->nombre }}
                </td>
                <td>
                    {{ $item->tipo === 'maquinista' ? $item->voluntario->compania->nombre : $item->cuartelero->compania->nombre }}
                </td>
                <td>
                    @foreach($item->unidades as $unidad)
                        <span class="badge bg-secondary me-1">{{ $unidad->nombre }}</span>
                    @endforeach
                </td>
                <td>{{ $item->entrada_at->format('d/m/Y H:i') }}</td>
                <td>{{ $item->salida_at ? $item->salida_at->format('d/m/Y H:i') : '—' }}</td>
                <td><span class="badge bg-secondary">{{ $item->tiempo_formateado }}</span></td>
                <td>{{ $item->observaciones ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-4">No hay turnos registrados</td>
            </tr>
            @endforelse
        </tbody>
        </table>
        <div class="p-3 d-flex justify-content-center">
            {{ $turnos->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const unidadesEnTurno = @json($unidadesEnTurno);
const unidadesEnTurnoCuartelero = @json($unidadesEnTurnoCuartelero);
const unidadesPorVoluntario = {
    @foreach($voluntarios as $voluntario)
    {{ $voluntario->id }}: [
        @foreach($voluntario->unidadesAutorizadas as $unidad)
        { id: {{ $unidad->id }}, nombre: "{{ $unidad->nombre }}", compania: "{{ $unidad->compania->nombre }}" },
        @endforeach
    ],
    @endforeach
};

const unidadesPorCuartelero = {
    @foreach($cuarteleros as $cuartelero)
    {{ $cuartelero->id }}: [
        @foreach($cuartelero->unidadesAutorizadas as $unidad)
        { id: {{ $unidad->id }}, nombre: "{{ $unidad->nombre }}", compania: "{{ $unidad->compania->nombre }}" },
        @endforeach
    ],
    @endforeach
};

// Inicializar popovers de unidades
document.querySelectorAll('.unidad-badge').forEach(el => {
    new bootstrap.Popover(el, { container: 'body' });
});

// Cerrar popovers al hacer clic fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.unidad-badge') && !e.target.closest('.popover')) {
        document.querySelectorAll('.unidad-badge').forEach(el => {
            bootstrap.Popover.getInstance(el)?.hide();
        });
    }
});

// Alternar formularios según tipo
document.querySelectorAll('input[name="tipo_conductor"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('formMaquinista').style.display =
            this.value === 'maquinista' ? 'block' : 'none';
        document.getElementById('formCuartelero').style.display =
            this.value === 'cuartelero' ? 'block' : 'none';
    });
});

// Inicializar Tom Select en ambos selects
const tsVoluntario = new TomSelect('#selectVoluntario', {
    placeholder: 'Buscar voluntario...',
    searchField: ['text'],
    maxOptions: 50,
    onChange: function(value) {
        // Disparar el evento change original para que funcionen las unidades
        document.getElementById('selectVoluntario').dispatchEvent(new Event('change'));
    }
});

const tsCuartelero = new TomSelect('#selectCuartelero', {
    placeholder: 'Buscar cuartelero...',
    searchField: ['text'],
    maxOptions: 50,
    onChange: function(value) {
        document.getElementById('selectCuartelero').dispatchEvent(new Event('change'));
    }
});

document.getElementById('selectVoluntario').addEventListener('change', function() {
    const voluntarioId = tsVoluntario.getValue(); // leer desde Tom Select
    const container = document.getElementById('unidadesContainer');
    const yaEnTurno = unidadesEnTurno[voluntarioId] || [];
    const unidades  = (unidadesPorVoluntario[voluntarioId] || [])
        .filter(u => !yaEnTurno.includes(u.id));

    if (!voluntarioId) {
        container.innerHTML = '<span class="text-muted small">Selecciona primero un voluntario...</span>';
        return;
    }
    if (!unidades.length) {
        container.innerHTML = '<span class="text-muted small text-success"><i class="bi bi-check-circle me-1"></i>Este voluntario ya está en turno con todas sus unidades autorizadas.</span>';
        return;
    }
    container.innerHTML = unidades.map(u => `
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="unidades[]"
                   value="${u.id}" id="unidad_${u.id}">
            <label class="form-check-label" for="unidad_${u.id}">
                <strong>${u.nombre}</strong> <span class="text-muted small">${u.compania}</span>
            </label>
        </div>
    `).join('');
});

document.getElementById('selectCuartelero').addEventListener('change', function() {
    const cuarteleroId = tsCuartelero.getValue(); // leer desde Tom Select
    const container = document.getElementById('unidadesCuarteleroContainer');
    const yaEnTurno = unidadesEnTurnoCuartelero[cuarteleroId] || [];
    const unidades  = (unidadesPorCuartelero[cuarteleroId] || [])
        .filter(u => !yaEnTurno.includes(u.id));

    if (!cuarteleroId) {
        container.innerHTML = '<span class="text-muted small">Selecciona primero un cuartelero...</span>';
        return;
    }
    if (!unidades.length) {
        container.innerHTML = '<span class="text-muted small text-success"><i class="bi bi-check-circle me-1"></i>Este cuartelero ya está en turno con todas sus unidades autorizadas.</span>';
        return;
    }
    container.innerHTML = unidades.map(u => `
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="unidades[]"
                   value="${u.id}" id="cunidad_${u.id}">
            <label class="form-check-label" for="cunidad_${u.id}">
                <strong>${u.nombre}</strong> <span class="text-muted small">${u.compania}</span>
            </label>
        </div>
    `).join('');
});

// Cronómetros
function actualizarCronometros() {
    document.querySelectorAll('.cronometro').forEach(el => {
        const entrada  = parseInt(el.dataset.entrada);
        const ahora    = Math.floor(Date.now() / 1000);
        const diff     = ahora - entrada;
        const horas    = Math.floor(diff / 3600);
        const minutos  = Math.floor((diff % 3600) / 60);
        const segundos = diff % 60;
        const pad = n => String(n).padStart(2, '0');
        el.textContent = `${pad(horas)}:${pad(minutos)}:${pad(segundos)}`;
        if (diff > 28800) {
            el.className = 'badge bg-danger cronometro';
        } else if (diff > 14400) {
            el.className = 'badge bg-warning text-dark cronometro';
        } else {
            el.className = 'badge bg-success cronometro';
        }
    });
}
actualizarCronometros();
setInterval(actualizarCronometros, 1000);
</script>
@endpush