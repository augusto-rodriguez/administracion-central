@extends('layouts.app')
@section('title', 'Reportes')
@section('content')

<div class="mb-4">
    <h4 class="mb-0">
        <i class="bi bi-file-earmark-bar-graph me-2"></i>Reporte de Turnos
        @if($esCapitan)
            <span class="text-muted fs-6 fw-normal ms-2">
                — {{ auth()->user()->voluntario?->compania->nombre }}
            </span>
        @endif
    </h4>
</div>

{{-- Pestañas --}}
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'compania' ? 'active' : '' }}"
           href="{{ route('reportes.index', ['tab' => 'compania']) }}">
            <i class="bi bi-building me-1"></i><span class="d-none d-sm-inline">Compañía</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'voluntario' ? 'active' : '' }}"
           href="{{ route('reportes.index', ['tab' => 'voluntario']) }}">
            <i class="bi bi-person-badge me-1"></i><span class="d-none d-sm-inline">Maquinista</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'cuartelero' ? 'active' : '' }}"
           href="{{ route('reportes.index', ['tab' => 'cuartelero']) }}">
            <i class="bi bi-person-gear me-1"></i><span class="d-none d-sm-inline">Cuartelero</span>
        </a>
    </li>
</ul>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-funnel me-2"></i>Filtros
    </div>
    <div class="card-body py-3">

        @if($tab === 'compania')
        <form method="GET" action="{{ route('reportes.index') }}">
            <input type="hidden" name="tab" value="compania">
            @if($esCapitan)
                <input type="hidden" name="compania_id" value="{{ $companiaIdCapitan }}">
            @endif
            <div class="row g-2 align-items-end">
                @if(!$esCapitan)
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold mb-1 small">Compañía <span class="text-danger">*</span></label>
                    <select name="compania_id" class="form-select form-select-sm" required>
                        <option value="">Seleccionar...</option>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}"
                                {{ request('compania_id') == $compania->id ? 'selected' : '' }}>
                                {{ $compania->numero }} - {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold mb-1 small">Año <span class="text-danger">*</span></label>
                    <select name="anio" class="form-select form-select-sm" required>
                        <option value="">Año...</option>
                        @foreach($anios as $anio)
                            <option value="{{ $anio }}" {{ request('anio') == $anio ? 'selected' : '' }}>{{ $anio }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold mb-1 small">Mes</label>
                    <select name="mes" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($meses as $num => $nombre)
                            <option value="{{ $num }}" {{ request('mes') == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="bi bi-search me-1"></i>Generar
                    </button>
                </div>
            </div>
        </form>

        @elseif($tab === 'voluntario')
        <form method="GET" action="{{ route('reportes.index') }}">
            <input type="hidden" name="tab" value="voluntario">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold mb-1 small">Maquinista <span class="text-danger">*</span></label>
                    <select name="voluntario_id" id="selectMaquinista" class="form-select form-select-sm" required>
                        <option value="">Seleccionar...</option>
                        @foreach($voluntarios as $voluntario)
                            <option value="{{ $voluntario->id }}"
                                {{ request('voluntario_id') == $voluntario->id ? 'selected' : '' }}>
                                {{ $voluntario->nombre }}
                                @if(!$esCapitan) — {{ $voluntario->compania->nombre }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold mb-1 small">Desde <span class="text-danger">*</span></label>
                    <input type="date" name="desde" class="form-control form-control-sm"
                           value="{{ request('desde') }}" required>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold mb-1 small">Hasta <span class="text-danger">*</span></label>
                    <input type="date" name="hasta" class="form-control form-control-sm"
                           value="{{ request('hasta') }}" required>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="bi bi-search me-1"></i>Generar
                    </button>
                </div>
            </div>
        </form>

        @else
        <form method="GET" action="{{ route('reportes.index') }}">
            <input type="hidden" name="tab" value="cuartelero">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold mb-1 small">Cuartelero <span class="text-danger">*</span></label>
                    <select name="cuartelero_id" class="form-select form-select-sm" required>
                        <option value="">Seleccionar...</option>
                        @foreach($cuarteleros as $cuartelero)
                            <option value="{{ $cuartelero->id }}"
                                {{ request('cuartelero_id') == $cuartelero->id ? 'selected' : '' }}>
                                {{ $cuartelero->nombre }}
                                @if(!$esCapitan) — {{ $cuartelero->compania->nombre }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold mb-1 small">Desde <span class="text-danger">*</span></label>
                    <input type="date" name="desde" class="form-control form-control-sm"
                           value="{{ request('desde') }}" required>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold mb-1 small">Hasta <span class="text-danger">*</span></label>
                    <input type="date" name="hasta" class="form-control form-control-sm"
                           value="{{ request('hasta') }}" required>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-danger btn-sm w-100">
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
    $compania   = $tab === 'compania'   ? $companias->find(request('compania_id') ?? $companiaIdCapitan) : null;
    $voluntario = $tab === 'voluntario' ? $voluntarios->find(request('voluntario_id'))                   : null;
    $cuartelero = $tab === 'cuartelero' ? $cuarteleros->find(request('cuartelero_id'))                   : null;
@endphp

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
            <div>
                @if($tab === 'compania')
                    <div class="fw-bold fs-5">{{ $compania->nombre }}</div>
                    <div class="text-muted small">
                        @if(request('mes')) {{ $meses[request('mes')] }} @endif
                        {{ request('anio') }} — {{ $turnos->count() }} turno(s)
                    </div>
                @elseif($tab === 'voluntario')
                    <div class="fw-bold fs-5">{{ $voluntario->nombre }}</div>
                    <div class="text-muted small">
                        {{ \Carbon\Carbon::parse(request('desde'))->format('d/m/Y') }}
                        al {{ \Carbon\Carbon::parse(request('hasta'))->format('d/m/Y') }}
                        — {{ $turnos->count() }} turno(s)
                    </div>
                @else
                    <div class="fw-bold fs-5">{{ $cuartelero->nombre }}</div>
                    <div class="text-muted small">
                        {{ \Carbon\Carbon::parse(request('desde'))->format('d/m/Y') }}
                        al {{ \Carbon\Carbon::parse(request('hasta'))->format('d/m/Y') }}
                        — {{ $turnos->count() }} turno(s)
                    </div>
                @endif
            </div>
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <div class="text-center">
                    <div class="text-muted" style="font-size:0.7rem">Total horas</div>
                    <div class="fw-bold fs-5 text-danger">{{ $horas }}h {{ $minutos }}min</div>
                </div>
                @if($tab === 'compania')
                    <a href="{{ route('reportes.exportar', request()->query()) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel
                    </a>
                @elseif($tab === 'voluntario')
                    <a href="{{ route('reportes.exportar-voluntario', request()->query()) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel
                    </a>
                @else
                    <a href="{{ route('reportes.exportar-cuartelero', request()->query()) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">

        {{-- Tabla desktop --}}
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-sm mb-0 d-none d-md-table" style="font-size:0.85rem;">
                <thead class="table-dark">
                    <tr>
                        <th>{{ $tab === 'cuartelero' ? 'Cuartelero' : 'Voluntario' }}</th>
                        <th>Unidades</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Tiempo</th>
                        <th>Obs.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($turnos as $turno)
                    <tr>
                        <td class="fw-bold text-nowrap">
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
                        <td class="text-nowrap">{{ $turno->entrada_at->format('d/m/Y H:i') }}</td>
                        <td class="text-nowrap">{{ $turno->salida_at->format('d/m/Y H:i') }}</td>
                        <td><span class="badge bg-secondary">{{ $turno->tiempo_formateado }}</span></td>
                        <td>{{ $turno->observaciones ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <td colspan="4" class="fw-bold text-end">Total:</td>
                        <td colspan="2" class="fw-bold">{{ $horas }}h {{ $minutos }}min</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Cards móvil --}}
        <div class="d-md-none">
            @foreach($turnos as $turno)
                <div class="border-bottom px-3 py-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="fw-bold">
                                @if($tab === 'cuartelero')
                                    {{ $turno->cuartelero->nombre }}
                                @else
                                    {{ $turno->voluntario->nombre }}
                                @endif
                            </span>
                        </div>
                        <span class="badge bg-secondary flex-shrink-0">{{ $turno->tiempo_formateado }}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-1 mb-1">
                        @foreach($turno->unidades as $unidad)
                            <span class="badge bg-primary">{{ $unidad->nombre }}</span>
                        @endforeach
                    </div>
                    <div class="d-flex flex-wrap gap-2 small text-muted">
                        <span><i class="bi bi-box-arrow-in-right text-success me-1"></i>{{ $turno->entrada_at->format('d/m H:i') }}</span>
                        <span><i class="bi bi-box-arrow-right text-danger me-1"></i>{{ $turno->salida_at->format('d/m H:i') }}</span>
                    </div>
                    @if($turno->observaciones)
                        <div class="small text-muted mt-1"><i class="bi bi-chat-left-text me-1"></i>{{ $turno->observaciones }}</div>
                    @endif
                </div>
            @endforeach

            <div class="px-3 py-2 bg-dark text-white fw-bold small d-flex justify-content-between">
                <span>Total</span>
                <span>{{ $horas }}h {{ $minutos }}min</span>
            </div>
        </div>

    </div>
</div>

@elseif(request()->hasAny(['compania_id', 'voluntario_id', 'cuartelero_id', 'anio']))
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>No hay turnos registrados para este período.
</div>
@endif

@endsection

@push('scripts')
<script>
    const selectMaquinista = new TomSelect('#selectMaquinista', {
        placeholder: 'Buscar maquinista...',
        searchField: ['text'],
        maxOptions: 50,
    });
</script>
@endpush