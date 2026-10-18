{{-- Selector de período (30 / 60 / 90 días). Requiere $tab y $cobertura --}}
<form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2">
    <input type="hidden" name="tab" value="{{ $tab }}">
    @if(request('mes_emergencias'))
        <input type="hidden" name="mes_emergencias" value="{{ request('mes_emergencias') }}">
    @endif
    <label class="small text-muted text-nowrap mb-0" for="periodo-{{ $tab }}">Período:</label>
    <select name="periodo_cobertura" id="periodo-{{ $tab }}"
            class="form-select form-select-sm" style="width:auto"
            onchange="this.form.submit()">
        @foreach([30 => 'Últimos 30 días', 60 => 'Últimos 60 días', 90 => 'Últimos 90 días'] as $valor => $etiqueta)
            <option value="{{ $valor }}" {{ $cobertura['dias'] == $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
        @endforeach
    </select>
</form>