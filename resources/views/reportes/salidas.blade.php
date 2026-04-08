@extends('layouts.app')
@section('title', 'Reporte de Salidas')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-file-earmark-ruled me-2"></i>Reporte de Salidas de Unidades</h4>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-funnel me-2"></i>Filtros
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reportes.salidas') }}">
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label fw-bold">Desde</label>
                    <input type="date" name="desde" class="form-control" value="{{ request('desde') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Hasta</label>
                    <input type="date" name="hasta" class="form-control" value="{{ request('hasta') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Compañía</label>
                    <select name="compania_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($companias as $compania)
                            <option value="{{ $compania->id }}" {{ request('compania_id') == $compania->id ? 'selected' : '' }}>
                                {{ $compania->numero }} - {{ $compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Unidad</label>
                    <select name="unidad_id" class="form-select">
                        <option value="">Todas</option>
                        @foreach($unidades as $unidad)
                            <option value="{{ $unidad->id }}" {{ request('unidad_id') == $unidad->id ? 'selected' : '' }}>
                                {{ $unidad->nombre }} — {{ $unidad->compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Clave</label>
                    <select name="clave_salida_id" class="form-select">
                        <option value="">Todas las claves</option>
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
                <div class="col-md-4">
                    <label class="form-label fw-bold">Oficial autorizante</label>
                    <select name="oficial_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($oficiales as $oficial)
                            <option value="{{ $oficial->id }}" {{ request('oficial_id') == $oficial->id ? 'selected' : '' }}>
                                {{ $oficial->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Conductor</label>
                    <select name="conductor_id" id="selectMaquinista" class="form-select">
                        <option value="">Todos</option>
                        <optgroup label="Maquinistas">
                            @foreach($maquinistas as $maquinista)
                                <option value="v_{{ $maquinista->id }}"
                                    {{ request('conductor_id') == 'v_'.$maquinista->id ? 'selected' : '' }}>
                                    {{ $maquinista->nombre }} — {{ $maquinista->compania->nombre }}
                                </option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Cuarteleros">
                            @foreach($cuarteleros as $cuartelero)
                                <option value="c_{{ $cuartelero->id }}"
                                    {{ request('conductor_id') == 'c_'.$cuartelero->id ? 'selected' : '' }}>
                                    {{ $cuartelero->nombre }} — {{ $cuartelero->compania->nombre }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                {{-- Filtro Al Mando --}}
                <div class="col-md-4">
                    <label class="form-label fw-bold">Voluntario al Mando</label>
                    <select name="al_mando_id" id="selectAlMando" class="form-select">
                        <option value="">Todos</option>
                        @foreach($voluntariosAlMando as $voluntario)
                            <option value="{{ $voluntario->id }}" {{ request('al_mando_id') == $voluntario->id ? 'selected' : '' }}>
                                {{ $voluntario->nombre }} — {{ $voluntario->compania->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-danger flex-grow-1">
                        <i class="bi bi-search me-1"></i>Buscar
                    </button>
                    @if($buscando)
                    <a href="{{ route('reportes.salidas') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    @endif
                </div>

            </div>
        </form>
    </div>
</div>

@if(!$buscando)
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Selecciona al menos un filtro y presiona <strong>Buscar</strong> para ver el reporte.
</div>

@elseif($salidas->isEmpty())
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
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-bold fs-5">{{ $salidas->count() }} salida(s) encontradas</div>
            <div class="text-muted small">
                @if(request('desde') || request('hasta'))
                    {{ request('desde') ? \Carbon\Carbon::parse(request('desde'))->format('d/m/Y') : '—' }}
                    al
                    {{ request('hasta') ? \Carbon\Carbon::parse(request('hasta'))->format('d/m/Y') : 'hoy' }}
                @endif
            </div>
        </div>
        <div class="d-flex gap-4 align-items-center">
            <div class="text-center">
                <div class="text-muted small">Km totales recorridos</div>
                <div class="fw-bold fs-4 text-primary">{{ number_format($totalKm, 0, ',', '.') }} km</div>
            </div>
            <div class="text-center">
                <div class="text-muted small">Tiempo total</div>
                <div class="fw-bold fs-4 text-danger">{{ $totalHoras }}h {{ $totalMins }}min</div>
            </div>
            <a href="{{ route('reportes.salidas.exportar', request()->query()) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i>Exportar Excel
            </a>
        </div>
    </div>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-bordered mb-0">
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
                    <th>Km recorridos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salidas as $salida)
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
                            {{ Str::limit($salida->claveSalida->descripcion, 35) }}
                        </div>
                    </td>
                    <td>{{ $salida->direccion }}</td>
                    <td>{{ $salida->conductor_nombre }}</td>
                    <td>{{ $salida->alMando?->nombre ?? '—' }}</td>
                    <td>{{ $salida->oficial?->nombre ?? '—' }}</td>
                    <td>{{ $salida->salida_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $salida->llegada_at->format('d/m/Y H:i') }}</td>
                    <td><span class="badge bg-secondary">{{ $salida->tiempo_formateado }}</span></td>
                    <td>{{ formatKm($salida->km_recorrido) }}</td>
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