@extends('layouts.app')
@section('title', 'Reporte de Salidas')
@section('content')

<div class="mb-4">
    <h4 class="mb-0">
        <i class="bi bi-file-earmark-ruled me-2"></i>Reporte de Salidas
        @if($esCapitan)
            <span class="text-muted fs-6 fw-normal ms-2">
                — {{ auth()->user()->voluntario?->compania->nombre }}
            </span>
        @endif
    </h4>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-funnel me-2"></i>Filtros
    </div>
    <div class="card-body py-3">
        <form method="GET" action="{{ route('reportes.salidas') }}">

            @if($esCapitan)
                <input type="hidden" name="compania_id" value="{{ $companiaIdCapitan }}">
            @endif

            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold mb-1 small">Desde</label>
                    <input type="date" name="desde" class="form-control form-control-sm" value="{{ request('desde') }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold mb-1 small">Hasta</label>
                    <input type="date" name="hasta" class="form-control form-control-sm" value="{{ request('hasta') }}">
                </div>

                @if(!$esCapitan)
                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold mb-1 small">Compañía</label>
                    <select name="compania_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}" {{ request('compania_id') == $compania->id ? 'selected' : '' }}>
                                {{ $compania->numero }} - {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold mb-1 small">Unidad</label>
                    <select name="unidad_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        @foreach($unidades as $unidad)
                            <option value="{{ $unidad->id }}" {{ request('unidad_id') == $unidad->id ? 'selected' : '' }}>
                                {{ $unidad->nombre }}
                                @if(!$esCapitan) — {{ $unidad->compania->nombre }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold mb-1 small">Clave</label>
                    <select name="clave_salida_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <optgroup label="🚨 Emergencias">
                            @foreach($claves->where('tipo', 'emergencia') as $clave)
                                <option value="{{ $clave->id }}" {{ request('clave_salida_id') == $clave->id ? 'selected' : '' }}>
                                    {{ $clave->codigo }} — {{ $clave->descripcion }}
                                </option>
                            @endforeach
                        </optgroup>
                        <optgroup label="⚙️ Administrativas">
                            @foreach($claves->where('tipo', 'administrativa') as $clave)
                                <option value="{{ $clave->id }}" {{ request('clave_salida_id') == $clave->id ? 'selected' : '' }}>
                                    {{ $clave->codigo }} — {{ $clave->descripcion }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
                <div class="col-6 col-md-4">
                    <label class="form-label fw-bold mb-1 small">Oficial autorizante</label>
                    <select name="oficial_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($oficiales as $oficial)
                            <option value="{{ $oficial->id }}" {{ request('oficial_id') == $oficial->id ? 'selected' : '' }}>
                                {{ $oficial->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4">
                    <label class="form-label fw-bold mb-1 small">Conductor</label>
                    <select name="conductor_id" id="selectMaquinista" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <optgroup label="Maquinistas">
                            @foreach($maquinistas as $maquinista)
                                <option value="v_{{ $maquinista->id }}"
                                    {{ request('conductor_id') == 'v_'.$maquinista->id ? 'selected' : '' }}>
                                    {{ $maquinista->nombre }}
                                    @if(!$esCapitan) — {{ $maquinista->compania->nombre }} @endif
                                </option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Cuarteleros">
                            @foreach($cuarteleros as $cuartelero)
                                <option value="c_{{ $cuartelero->id }}"
                                    {{ request('conductor_id') == 'c_'.$cuartelero->id ? 'selected' : '' }}>
                                    {{ $cuartelero->nombre }}
                                    @if(!$esCapitan) — {{ $cuartelero->compania->nombre }} @endif
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
                <div class="col-6 col-md-4">
                    <label class="form-label fw-bold mb-1 small">Al Mando</label>
                    <select name="al_mando_id" id="selectAlMando" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($voluntariosAlMando as $voluntario)
                            <option value="{{ $voluntario->id }}" {{ request('al_mando_id') == $voluntario->id ? 'selected' : '' }}>
                                {{ $voluntario->nombre }}
                                @if(!$esCapitan) — {{ $voluntario->compania->nombre }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-4">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-danger btn-sm flex-grow-1">
                            <i class="bi bi-search me-1"></i>Buscar
                        </button>
                        @if($buscando && !$esCapitan)
                        <a href="{{ route('reportes.salidas') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-lg"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if($salidas->isEmpty())
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>No hay salidas registradas para los filtros seleccionados.
</div>

@else
@php
    $totalHoras = intdiv($totalTiempo, 60);
    $totalMins  = $totalTiempo % 60;
@endphp

{{-- Resumen --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <div class="fw-bold fs-5">{{ $salidas->count() }} salida(s)</div>
                <div class="text-muted small">
                    @if(request('desde') || request('hasta'))
                        {{ request('desde') ? \Carbon\Carbon::parse(request('desde'))->format('d/m/Y') : '—' }}
                        al
                        {{ request('hasta') ? \Carbon\Carbon::parse(request('hasta'))->format('d/m/Y') : 'hoy' }}
                    @endif
                </div>
            </div>
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <div class="text-center">
                    <div class="text-muted" style="font-size:0.7rem">Km totales</div>
                    <div class="fw-bold fs-5 text-primary">{{ number_format($totalKm, 0, ',', '.') }} km</div>
                </div>
                <div class="text-center">
                    <div class="text-muted" style="font-size:0.7rem">Tiempo total</div>
                    <div class="fw-bold fs-5 text-danger">{{ $totalHoras }}h {{ $totalMins }}min</div>
                </div>
                <a href="{{ route('reportes.salidas.exportar', request()->query()) }}" class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-excel me-1"></i>Exportar
                </a>
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
                        <th>Unidad</th>
                        <th>Clave</th>
                        <th>Dirección</th>
                        <th>Conductor</th>
                        <th>Al Mando</th>
                        <th>Aut</th>
                        <th>Salida</th>
                        <th>Llegada</th>
                        <th>Tiempo</th>
                        <th>Km</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salidas as $salida)
                    <tr>
                        <td class="fw-bold text-nowrap">
                            {{ $salida->unidad->nombre }}
                            @if(!$esCapitan)
                                <div class="text-muted" style="font-size:0.75rem">{{ $salida->unidad->compania->nombre }}</div>
                            @endif
                        </td>
                        <td>
                            @if($salida->claveSalida->tipo === 'emergencia')
                                <span class="badge bg-danger">{{ $salida->claveSalida->codigo }}</span>
                            @else
                                <span class="badge bg-primary">{{ $salida->claveSalida->codigo }}</span>
                            @endif
                        </td>
                        <td style="max-width:150px">{{ Str::limit($salida->direccion, 30) }}</td>
                        <td>{{ $salida->conductor_nombre }}</td>
                        <td>{{ $salida->alMando?->nombre ?? '—' }}</td>
                        <td>{{ $salida->oficial?->nombre ?? '—' }}</td>
                        <td class="text-nowrap">{{ $salida->salida_at->format('d/m H:i') }}</td>
                        <td class="text-nowrap">{{ $salida->llegada_at->format('d/m H:i') }}</td>
                        <td><span class="badge bg-secondary">{{ $salida->tiempo_formateado }}</span></td>
                        <td class="text-nowrap">{{ formatKm($salida->km_recorrido) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <td colspan="8" class="fw-bold text-end">Totales:</td>
                        <td class="fw-bold">{{ $totalHoras }}h {{ $totalMins }}min</td>
                        <td class="fw-bold">{{ number_format($totalKm, 0, ',', '.') }} km</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Cards móvil --}}
        <div class="d-md-none">
            @foreach($salidas as $salida)
                <div class="border-bottom px-3 py-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="fw-bold">{{ $salida->unidad->nombre }}</span>
                            @if($salida->claveSalida->tipo === 'emergencia')
                                <span class="badge bg-danger ms-1">{{ $salida->claveSalida->codigo }}</span>
                            @else
                                <span class="badge bg-primary ms-1">{{ $salida->claveSalida->codigo }}</span>
                            @endif
                            @if(!$esCapitan)
                                <div class="text-muted small">{{ $salida->unidad->compania->nombre }}</div>
                            @endif
                        </div>
                        <div class="text-end flex-shrink-0 ms-2">
                            <span class="badge bg-secondary">{{ $salida->tiempo_formateado }}</span>
                            <div class="small text-muted">{{ formatKm($salida->km_recorrido) }}</div>
                        </div>
                    </div>
                    <div class="small text-muted mb-1">
                        <i class="bi bi-geo-alt me-1"></i>{{ Str::limit($salida->direccion, 50) }}
                    </div>
                    <div class="d-flex flex-wrap gap-2 small text-muted">
                        <span><i class="bi bi-person me-1"></i>{{ $salida->conductor_nombre }}</span>
                        @if($salida->alMando)
                            <span><i class="bi bi-shield me-1"></i>{{ $salida->alMando->nombre }}</span>
                        @endif
                        <span><i class="bi bi-arrow-up-right-circle text-danger me-1"></i>{{ $salida->salida_at->format('d/m H:i') }}</span>
                        <span><i class="bi bi-arrow-down-left-circle text-success me-1"></i>{{ $salida->llegada_at->format('d/m H:i') }}</span>
                    </div>
                </div>
            @endforeach

            <div class="px-3 py-2 bg-dark text-white fw-bold small d-flex justify-content-between">
                <span>{{ $totalHoras }}h {{ $totalMins }}min</span>
                <span>{{ number_format($totalKm, 0, ',', '.') }} km</span>
            </div>
        </div>

    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    const selectMaquinista = new TomSelect('#selectMaquinista', {
        placeholder: 'Buscar conductor...',
        searchField: ['text'],
        maxOptions: 50,
    });
    const selectAlMando = new TomSelect('#selectAlMando', {
        placeholder: 'Buscar voluntario...',
        searchField: ['text'],
        maxOptions: 100,
    });
</script>
@endpush