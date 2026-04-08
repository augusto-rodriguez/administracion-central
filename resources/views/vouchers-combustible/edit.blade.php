@extends('layouts.app')
@section('title', 'Editar Voucher')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-pencil me-2"></i>Editar Voucher #{{ $voucherCombustible->numero_voucher }}</h4>
    <a href="{{ route('vouchers-combustible.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="card">
    <div class="card-header bg-danger text-white fw-bold">
        <i class="bi bi-fuel-pump me-2"></i>Editar Voucher de Combustible
    </div>
    <div class="card-body">
        <form action="{{ route('vouchers-combustible.update', $voucherCombustible) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">

                <div class="col-md-3">
                    <label class="form-label fw-bold">Fecha de Carga <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_carga"
                           class="form-control @error('fecha_carga') is-invalid @enderror"
                           value="{{ old('fecha_carga', $voucherCombustible->fecha_carga->format('Y-m-d')) }}" required>
                    @error('fecha_carga') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">N° Voucher <span class="text-danger">*</span></label>
                    <input type="text" name="numero_voucher"
                           class="form-control @error('numero_voucher') is-invalid @enderror"
                           value="{{ old('numero_voucher', $voucherCombustible->numero_voucher) }}" required>
                    @error('numero_voucher') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Unidad <span class="text-danger">*</span></label>
                    <select name="unidad_id" id="selectUnidadEdit"
                            class="form-select @error('unidad_id') is-invalid @enderror" required>
                        <option value="">Seleccionar unidad...</option>
                        @foreach($unidades->groupBy('compania.nombre') as $compania => $uds)
                            <optgroup label="{{ $compania }}">
                                @foreach($uds as $unidad)
                                    <option value="{{ $unidad->id }}"
                                        {{ old('unidad_id', $voucherCombustible->unidad_id) == $unidad->id ? 'selected' : '' }}>
                                        {{ $unidad->nombre }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('unidad_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Km al momento de carga <span class="text-danger">*</span></label>
                    <input type="number" name="km_carga" step="1" min="0"
                           class="form-control @error('km_carga') is-invalid @enderror"
                           value="{{ old('km_carga', $voucherCombustible->km_carga) }}"
                           placeholder="Sin puntos ni guiones" required>
                    @error('km_carga') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-bold">Conductor <span class="text-danger">*</span></label>
                    <select name="conductor_nombre" id="selectConductorEdit"
                            class="form-select @error('conductor_nombre') is-invalid @enderror" required>
                        <option value="">Seleccionar conductor...</option>
                        @foreach($conductores as $conductor)
                            <option value="{{ $conductor['nombre'] }}"
                                {{ old('conductor_nombre', $voucherCombustible->conductor_nombre) == $conductor['nombre'] ? 'selected' : '' }}>
                                {{ $conductor['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('conductor_nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Litros <span class="text-danger">*</span></label>
                    <input type="number" name="litros" id="editLitros"
                           step="0.001" min="0.001"
                           class="form-control @error('litros') is-invalid @enderror"
                           value="{{ old('litros', $voucherCombustible->litros) }}" required>
                    <div class="text-muted small mt-1">Usar punto como decimal (Ej: 48.190)</div>
                    @error('litros') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Valor Unitario ($/L) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="valor_unitario" id="editValorUnitario"
                               step="1" min="1"
                               class="form-control @error('valor_unitario') is-invalid @enderror"
                               value="{{ old('valor_unitario', $voucherCombustible->valor_unitario) }}" required>
                    </div>
                    @error('valor_unitario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Total</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" id="editTotalCalculado" class="form-control fw-bold bg-light" readonly
                               value="{{ number_format($voucherCombustible->total, 0, ',', '.') }}">
                    </div>
                    <div class="text-muted small mt-1" id="editTotalDetalle"></div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Observaciones</label>
                    <input type="text" name="observaciones" class="form-control"
                           value="{{ old('observaciones', $voucherCombustible->observaciones) }}"
                           placeholder="Opcional...">
                </div>

            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-check-lg me-1"></i>Guardar Cambios
                </button>
                <a href="{{ route('vouchers-combustible.index') }}" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const tomConductor = new TomSelect('#selectConductorEdit', {
    searchField: ['text'],
    maxOptions: 100
});
tomConductor.setValue("{{ old('conductor_nombre', $voucherCombustible->conductor_nombre) }}");

const tomUnidad = new TomSelect('#selectUnidadEdit', {
    searchField: ['text'],
    maxOptions: 100
});
tomUnidad.setValue("{{ old('unidad_id', $voucherCombustible->unidad_id) }}");

function calcularTotalEdit() {
    const litros    = parseFloat(document.getElementById('editLitros').value) || 0;
    const valorUnit = parseInt(document.getElementById('editValorUnitario').value) || 0;
    const total     = Math.round(litros * valorUnit);
    const totalEl   = document.getElementById('editTotalCalculado');
    const detalleEl = document.getElementById('editTotalDetalle');

    if (litros > 0 && valorUnit > 0) {
        totalEl.value = total.toLocaleString('es-CL');
        detalleEl.innerHTML = `<i class="bi bi-calculator me-1"></i>${litros.toLocaleString('es-CL', {minimumFractionDigits:3})} L × $${valorUnit.toLocaleString('es-CL')}`;
    } else {
        totalEl.value = '';
        detalleEl.textContent = '';
    }
}

document.getElementById('editLitros').addEventListener('input', calcularTotalEdit);
document.getElementById('editValorUnitario').addEventListener('input', calcularTotalEdit);
calcularTotalEdit();
</script>
@endpush