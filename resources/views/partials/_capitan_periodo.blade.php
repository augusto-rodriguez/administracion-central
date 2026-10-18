{{--
    Filtros de cobertura: compañía (solo comandancia), unidad y período.
    Requiere $tab y $cobertura. Opcional: $companiasSelector (colección de compañías).
--}}
<form method="GET" action="{{ url()->current() }}" class="d-flex flex-wrap align-items-center gap-2">
    <input type="hidden" name="tab" value="{{ $tab }}">
    @if(request('mes_emergencias'))
        <input type="hidden" name="mes_emergencias" value="{{ request('mes_emergencias') }}">
    @endif

    @isset($companiasSelector)
        <label class="small text-muted text-nowrap mb-0" for="compania-{{ $tab }}">Compañía:</label>
        <select name="compania_cobertura" id="compania-{{ $tab }}"
                class="form-select form-select-sm" style="width:auto"
                onchange="const u = this.form.querySelector('[name=unidad_cobertura]'); if (u) u.value = ''; this.form.submit()">
            <option value="">Todas las compañías</option>
            @foreach($companiasSelector as $cia)
                <option value="{{ $cia->id }}" {{ (int) request('compania_cobertura') === $cia->id ? 'selected' : '' }}>
                    {{ $cia->nombre }}
                </option>
            @endforeach
        </select>
    @endisset

    @if(!empty($cobertura['todasUnidades']))
        <label class="small text-muted text-nowrap mb-0" for="unidad-{{ $tab }}">Ver:</label>
        <select name="unidad_cobertura" id="unidad-{{ $tab }}"
                class="form-select form-select-sm" style="width:auto"
                onchange="this.form.submit()">
            <option value="">Todas las unidades</option>
            @if($cobertura['panorama'] ?? false)
                {{-- Panorama general: unidades agrupadas por compañía --}}
                @foreach(collect($cobertura['todasUnidades'])->groupBy('compania') as $nombreCia => $uds)
                    <optgroup label="{{ $nombreCia }}">
                        @foreach($uds as $u)
                            <option value="{{ $u['id'] }}" {{ ($cobertura['unidadSeleccionada']['id'] ?? null) == $u['id'] ? 'selected' : '' }}>
                                {{ $u['nombre'] }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            @else
                @foreach($cobertura['todasUnidades'] as $u)
                    <option value="{{ $u['id'] }}" {{ ($cobertura['unidadSeleccionada']['id'] ?? null) == $u['id'] ? 'selected' : '' }}>
                        {{ $u['nombre'] }}
                    </option>
                @endforeach
            @endif
        </select>
    @endif

    <label class="small text-muted text-nowrap mb-0" for="periodo-{{ $tab }}">Período:</label>
    <select name="periodo_cobertura" id="periodo-{{ $tab }}"
            class="form-select form-select-sm" style="width:auto"
            onchange="this.form.submit()">
        @foreach([7 => 'Últimos 7 días', 30 => 'Últimos 30 días', 60 => 'Últimos 60 días', 90 => 'Últimos 90 días'] as $valor => $etiqueta)
            <option value="{{ $valor }}" {{ $cobertura['dias'] == $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
        @endforeach
    </select>
</form>