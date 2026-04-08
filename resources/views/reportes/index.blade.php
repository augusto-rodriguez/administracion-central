@extends('layouts.app')
@section('title', 'Reportes')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-file-earmark-bar-graph me-2"></i>Reportes de Turnos</h4>
</div>

{{-- Pestañas --}}
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'compania' ? 'active' : '' }}"
           href="{{ route('reportes.index', ['tab' => 'compania']) }}">
            <i class="bi bi-building me-1"></i>Por Compañía
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'voluntario' ? 'active' : '' }}"
           href="{{ route('reportes.index', ['tab' => 'voluntario']) }}">
            <i class="bi bi-person-badge me-1"></i>Por Maquinista
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'cuartelero' ? 'active' : '' }}"
           href="{{ route('reportes.index', ['tab' => 'cuartelero']) }}">
            <i class="bi bi-person-gear me-1"></i>Por Cuartelero
        </a>
    </li>
</ul>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-funnel me-2"></i>Filtros
    </div>
    <div class="card-body">

        @if($tab === 'compania')
        <form method="GET" action="{{ route('reportes.index') }}">
            <input type="hidden" name="tab" value="compania">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Compañía <span class="text-danger">*</span></label>
                    <select name="compania_id" class="form-select" required>
                        <option value="">Seleccionar compañía...</option>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}"
                                {{ request('compania_id') == $compania->id ? 'selected' : '' }}>
                                {{ $compania->numero }} - {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Año <span class="text-danger">*</span></label>
                    <select name="anio" class="form-select" required>
                        <option value="">Seleccionar año...</option>
                        @foreach($anios as $anio)
                            <option value="{{ $anio }}" {{ request('anio') == $anio ? 'selected' : '' }}>
                                {{ $anio }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Mes <span class="text-muted small">(opcional)</span></label>
                    <select name="mes" class="form-select">
                        <option value="">Todos los meses</option>
                        @foreach($meses as $num => $nombre)
                            <option value="{{ $num }}" {{ request('mes') == $num ? 'selected' : '' }}>
                                {{ $nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-search me-1"></i>Generar
                    </button>
                </div>
            </div>
        </form>

        @elseif($tab === 'voluntario')
        <form method="GET" action="{{ route('reportes.index') }}">
            <input type="hidden" name="tab" value="voluntario">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Maquinista <span class="text-danger">*</span></label>
                    <select name="voluntario_id" id="selectMaquinista" class="form-select" required>
                        <option value="">Seleccionar maquinista...</option>
                        @foreach($voluntarios as $voluntario)
                            <option value="{{ $voluntario->id }}"
                                {{ request('voluntario_id') == $voluntario->id ? 'selected' : '' }}>
                                {{ $voluntario->nombre }} — {{ $voluntario->compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Desde <span class="text-danger">*</span></label>
                    <input type="date" name="desde" class="form-control"
                           value="{{ request('desde') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Hasta <span class="text-danger">*</span></label>
                    <input type="date" name="hasta" class="form-control"
                           value="{{ request('hasta') }}" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-search me-1"></i>Generar
                    </button>
                </div>
            </div>
        </form>

        @else
        <form method="GET" action="{{ route('reportes.index') }}">
            <input type="hidden" name="tab" value="cuartelero">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Cuartelero <span class="text-danger">*</span></label>
                    <select name="cuartelero_id" class="form-select" required>
                        <option value="">Seleccionar cuartelero...</option>
                        @foreach($cuarteleros as $cuartelero)
                            <option value="{{ $cuartelero->id }}"
                                {{ request('cuartelero_id') == $cuartelero->id ? 'selected' : '' }}>
                                {{ $cuartelero->nombre }} — {{ $cuartelero->compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Desde <span class="text-danger">*</span></label>
                    <input type="date" name="desde" class="form-control"
                           value="{{ request('desde') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Hasta <span class="text-danger">*</span></label>
                    <input type="date" name="hasta" class="form-control"
                           value="{{ request('hasta') }}" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-search me-1"></i>Generar
                    </button>
                </div>
            </div>
        </form>
        @endif

    </div>
</div>

{{-- RESULTADOS --}}
@if($turnos->isNotEmpty())
@php
    $horas      = intdiv($totalMinutos, 60);
    $minutos    = $totalMinutos % 60;
    $compania   = $tab === 'compania'   ? $companias->find(request('compania_id'))     : null;
    $voluntario = $tab === 'voluntario' ? $voluntarios->find(request('voluntario_id')) : null;
    $cuartelero = $tab === 'cuartelero' ? $cuarteleros->find(request('cuartelero_id')) : null;
@endphp

<div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            @if($tab === 'compania')
                <div class="fw-bold fs-5">{{ $compania->nombre }}</div>
                <div class="text-muted">
                    @if(request('mes')) {{ $meses[request('mes')] }} @endif
                    {{ request('anio') }} — {{ $turnos->count() }} turno(s)
                </div>
            @elseif($tab === 'voluntario')
                <div class="fw-bold fs-5">{{ $voluntario->nombre }}</div>
                <div class="text-muted">
                    {{ \Carbon\Carbon::parse(request('desde'))->format('d/m/Y') }}
                    al {{ \Carbon\Carbon::parse(request('hasta'))->format('d/m/Y') }}
                    — {{ $turnos->count() }} turno(s)
                </div>
            @else
                <div class="fw-bold fs-5">{{ $cuartelero->nombre }}</div>
                <div class="text-muted">
                    {{ \Carbon\Carbon::parse(request('desde'))->format('d/m/Y') }}
                    al {{ \Carbon\Carbon::parse(request('hasta'))->format('d/m/Y') }}
                    — {{ $turnos->count() }} turno(s)
                </div>
            @endif
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="text-end">
                <div class="text-muted small">Total horas en servicio</div>
                <div class="fw-bold fs-4 text-danger">{{ $horas }}h {{ $minutos }}min</div>
            </div>
            @if($tab === 'compania')
                <a href="{{ route('reportes.exportar', request()->query()) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i>Exportar Excel
                </a>
            @elseif($tab === 'voluntario')
                <a href="{{ route('reportes.exportar-voluntario', request()->query()) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i>Exportar Excel
                </a>
            @else
                <a href="{{ route('reportes.exportar-cuartelero', request()->query()) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i>Exportar Excel
                </a>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>{{ $tab === 'cuartelero' ? 'Cuartelero' : 'Voluntario' }}</th>
                    <th>Unidades</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Tiempo en Servicio</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($turnos as $turno)
                <tr>
                    <td class="fw-bold">
                        @if($tab === 'cuartelero')
                            {{ $turno->cuartelero->nombre }}
                        @else
                            {{ $turno->voluntario->nombre }}
                        @endif
                    </td>
                    <td>
                        @foreach($turno->unidades as $unidad)
                            <span class="badge bg-primary me-1">{{ $unidad->nombre }}</span>
                        @endforeach
                    </td>
                    <td>{{ $turno->entrada_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $turno->salida_at->format('d/m/Y H:i') }}</td>
                    <td><span class="badge bg-secondary">{{ $turno->tiempo_formateado }}</span></td>
                    <td>{{ $turno->observaciones ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <td colspan="4" class="fw-bold text-end">Total general:</td>
                    <td colspan="2" class="fw-bold">{{ $horas }}h {{ $minutos }}min</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@elseif(request()->hasAny(['compania_id', 'voluntario_id', 'cuartelero_id']))
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>No hay turnos registrados para este período.
</div>
@endif

@endsection

@push('scripts')
<script>
    // Inicializar Tom Select en select
    const selectMaquinista = new TomSelect('#selectMaquinista', {
        placeholder: 'Buscar maquinista...',
        searchField: ['text'],
        maxOptions: 50,
        onChange: function(value) {
            // Disparar el evento change original para que funcionen las unidades
            document.getElementById('selectMaquinista').dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush