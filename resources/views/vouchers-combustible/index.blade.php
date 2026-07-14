@extends('layouts.app')
@section('title', 'Vouchers de Combustible')
@section('content')

{{-- Encabezado --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <h4 class="mb-0"><i class="bi bi-fuel-pump me-2"></i>Vouchers de Combustible</h4>
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalNuevoVoucher">
        <i class="bi bi-plus-circle me-1"></i>Registrar Voucher
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Modal Nuevo Voucher --}}
<div class="modal fade" id="modalNuevoVoucher" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-fuel-pump me-2"></i>Registrar Voucher de Combustible
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('vouchers-combustible.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold small">Fecha de Carga <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_carga"
                                   class="form-control form-control-sm @error('fecha_carga') is-invalid @enderror"
                                   value="{{ old('fecha_carga', date('Y-m-d')) }}" required>
                            @error('fecha_carga') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold small">N° Voucher <span class="text-danger">*</span></label>
                            <input type="text" name="numero_voucher"
                                   class="form-control form-control-sm @error('numero_voucher') is-invalid @enderror"
                                   value="{{ old('numero_voucher') }}"
                                   placeholder="Ej: 00123456" required>
                            @error('numero_voucher') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">Unidad <span class="text-danger">*</span></label>
                            <select name="unidad_id" id="selectUnidadVoucher"
                                    class="form-select form-select-sm @error('unidad_id') is-invalid @enderror" required>
                                <option value="">Seleccionar unidad...</option>
                                @foreach($unidades->groupBy('compania.nombre') as $compania => $uds)
                                    <optgroup label="{{ $compania }}">
                                        @foreach($uds as $unidad)
                                            <option value="{{ $unidad->id }}" {{ old('unidad_id') == $unidad->id ? 'selected' : '' }}>
                                                {{ $unidad->nombre }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('unidad_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label fw-bold small">Km al momento de carga <span class="text-danger">*</span></label>
                            <input type="number" name="km_carga" step="1" min="0"
                                   class="form-control form-control-sm @error('km_carga') is-invalid @enderror"
                                   value="{{ old('km_carga') }}"
                                   placeholder="Sin puntos ni guiones" required>
                            @error('km_carga') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-6 col-md-8">
                            <label class="form-label fw-bold small">Conductor <span class="text-danger">*</span></label>
                            <select name="conductor_nombre" id="selectConductorVoucher"
                                    class="form-select form-select-sm @error('conductor_nombre') is-invalid @enderror" required>
                                <option value="">Seleccionar conductor...</option>
                                @foreach($conductores as $conductor)
                                    <option value="{{ $conductor['nombre'] }}"
                                            {{ old('conductor_nombre') == $conductor['nombre'] ? 'selected' : '' }}>
                                        {{ $conductor['nombre'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('conductor_nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-4 col-md-4">
                            <label class="form-label fw-bold small">Litros <span class="text-danger">*</span></label>
                            <input type="number" name="litros" id="inputLitros"
                                   step="0.001" min="0.001" max="9999.999"
                                   class="form-control form-control-sm @error('litros') is-invalid @enderror"
                                   value="{{ old('litros') }}"
                                   placeholder="48.190" required>
                            @error('litros') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-4 col-md-4">
                            <label class="form-label fw-bold small">$/L <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" name="valor_unitario" id="inputValorUnitario"
                                       step="1" min="1"
                                       class="form-control @error('valor_unitario') is-invalid @enderror"
                                       value="{{ old('valor_unitario') }}"
                                       placeholder="1350" required>
                            </div>
                            @error('valor_unitario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-4 col-md-4">
                            <label class="form-label fw-bold small">Total</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="text" id="totalCalculado" class="form-control fw-bold bg-light"
                                       readonly placeholder="Auto">
                            </div>
                            <div class="text-muted small mt-1 d-none d-md-block" id="totalDetalle"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small">Observaciones</label>
                            <input type="text" name="observaciones"
                                   class="form-control form-control-sm"
                                   value="{{ old('observaciones') }}"
                                   placeholder="Opcional...">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-fuel-pump me-1"></i>Registrar Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Filtros + Exportar --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('vouchers-combustible.index') }}" method="GET"
              class="row g-2 align-items-end" id="formFiltros">
            <div class="col-6 col-md-3">
                <label class="form-label fw-bold mb-1 small">Compañía</label>
                <select name="compania_id" class="form-select form-select-sm" id="filtroCompania">
                    <option value="">Todas</option>
                    @foreach($companias as $compania)
                        <option value="{{ $compania->id }}"
                                {{ request('compania_id') == $compania->id ? 'selected' : '' }}>
                            {{ $compania->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label fw-bold mb-1 small">Unidad</label>
                <select name="unidad_id" class="form-select form-select-sm" id="filtroUnidad">
                    <option value="">Todas</option>
                    @foreach($unidades->groupBy('compania.nombre') as $comp => $uds)
                        <optgroup label="{{ $comp }}">
                            @foreach($uds as $unidad)
                                <option value="{{ $unidad->id }}"
                                        data-compania="{{ $unidad->compania_id }}"
                                        {{ request('unidad_id') == $unidad->id ? 'selected' : '' }}>
                                    {{ $unidad->nombre }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-bold mb-1 small">Mes</label>
                <select name="mes" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('mes') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-bold mb-1 small">Año</label>
                <select name="anio" class="form-select form-select-sm">
                    @foreach(range(now()->year, now()->year - 3) as $y)
                        <option value="{{ $y }}"
                                {{ request('anio', now()->year) == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-danger btn-sm flex-fill">
                        <i class="bi bi-search me-1"></i>Filtrar
                    </button>
                    <a href="{{ route('vouchers-combustible.index') }}"
                       class="btn btn-outline-secondary btn-sm"
                       title="Limpiar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    <button type="button" class="btn btn-success btn-sm"
                            title="Exportar Excel con filtros actuales"
                            onclick="exportarConFiltros()">
                        <i class="bi bi-file-earmark-excel"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Tabla vouchers --}}
<div class="card">
    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-ul me-2"></i>Registro de Vouchers</span>
        @if(request()->hasAny(['compania_id', 'unidad_id', 'mes']))
            <span class="badge bg-danger">Filtro activo</span>
        @endif
    </div>
    <div class="card-body p-0">

        {{-- Vista tabla desktop --}}
        <div class="table-responsive">
            <table class="table table-hover mb-0 d-none d-md-table">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>N° Voucher</th>
                        <th>Unidad</th>
                        <th>Compañía</th>
                        <th>Km Carga</th>
                        <th>Conductor</th>
                        <th>Litros</th>
                        <th>$/L</th>
                        <th>Total</th>
                        <th>Obs.</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $v)
                    <tr>
                        <td class="text-nowrap">{{ $v->fecha_carga->format('d/m/Y') }}</td>
                        <td><span class="badge bg-secondary">{{ $v->numero_voucher }}</span></td>
                        <td class="fw-bold text-nowrap">{{ $v->unidad->nombre }}</td>
                        <td class="text-nowrap">{{ $v->unidad->compania->nombre }}</td>
                        <td class="text-nowrap">{{ number_format($v->km_carga, 0, ',', '.') }} km</td>
                        <td class="text-nowrap">{{ $v->conductor_nombre }}</td>
                        <td>{{ $v->litros_formateados }}</td>
                        <td>{{ $v->valor_unitario_formateado }}</td>
                        <td class="fw-bold text-success text-nowrap">{{ $v->total_formateado }}</td>
                        <td>{{ $v->observaciones ?? '—' }}</td>
                        <td>
                            <a href="{{ route('vouchers-combustible.edit', $v) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">No hay vouchers registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Vista cards móvil --}}
        <div class="d-md-none">
            @forelse($vouchers as $v)
                <div class="border-bottom px-3 py-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="fw-bold">{{ $v->unidad->nombre }}</span>
                            <span class="text-muted small ms-1">{{ $v->unidad->compania->nombre }}</span>
                        </div>
                        <span class="badge bg-secondary">{{ $v->numero_voucher }}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 small text-muted mb-2">
                        <span><i class="bi bi-calendar me-1"></i>{{ $v->fecha_carga->format('d/m/Y') }}</span>
                        <span><i class="bi bi-speedometer me-1"></i>{{ number_format($v->km_carga, 0, ',', '.') }} km</span>
                        <span><i class="bi bi-person me-1"></i>{{ $v->conductor_nombre }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="small">
                            <span>{{ $v->litros_formateados }} × {{ $v->valor_unitario_formateado }}</span>
                            <span class="fw-bold text-success ms-2">{{ $v->total_formateado }}</span>
                        </div>
                        <a href="{{ route('vouchers-combustible.edit', $v) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                    @if($v->observaciones)
                        <div class="small text-muted mt-1">
                            <i class="bi bi-chat-left-text me-1"></i>{{ $v->observaciones }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center text-muted py-4">No hay vouchers registrados</div>
            @endforelse
        </div>

        <div class="p-3 d-flex justify-content-center">
            {{ $vouchers->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
new TomSelect('#selectConductorVoucher', {
    placeholder: 'Buscar conductor...',
    searchField: ['text'],
    maxOptions: 100,
});

new TomSelect('#selectUnidadVoucher', {
    placeholder: 'Buscar unidad...',
    searchField: ['text'],
    maxOptions: 100,
});

// Filtrar unidades del filtro según compañía
document.getElementById('filtroCompania').addEventListener('change', function() {
    const companiaId   = this.value;
    const filtroUnidad = document.getElementById('filtroUnidad');
    const options      = filtroUnidad.querySelectorAll('option[data-compania]');

    options.forEach(opt => {
        if (!companiaId || opt.dataset.compania === companiaId) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
            if (opt.selected) filtroUnidad.value = '';
        }
    });
});

// Exportar tomando los valores actuales del formulario de filtros
function exportarConFiltros() {
    const form       = document.getElementById('formFiltros');
    const params     = new URLSearchParams(new FormData(form));
    const exportUrl  = "{{ route('vouchers-combustible.exportar') }}" + '?' + params.toString();
    window.location.href = exportUrl;
}

// Calcular total en tiempo real
function calcularTotal() {
    const litros    = parseFloat(document.getElementById('inputLitros').value) || 0;
    const valorUnit = parseInt(document.getElementById('inputValorUnitario').value) || 0;
    const total     = Math.round(litros * valorUnit);
    const totalInput   = document.getElementById('totalCalculado');
    const totalDetalle = document.getElementById('totalDetalle');

    if (litros > 0 && valorUnit > 0) {
        totalInput.value = total.toLocaleString('es-CL');
        totalDetalle.innerHTML = `<i class="bi bi-calculator me-1"></i>${litros.toLocaleString('es-CL', {minimumFractionDigits:3})} L × $${valorUnit.toLocaleString('es-CL')}`;
    } else {
        totalInput.value = '';
        totalDetalle.textContent = '';
    }
}

document.getElementById('inputLitros').addEventListener('input', calcularTotal);
document.getElementById('inputValorUnitario').addEventListener('input', calcularTotal);

@if($errors->any())
    new bootstrap.Modal(document.getElementById('modalNuevoVoucher')).show();
@endif
</script>
@endpush