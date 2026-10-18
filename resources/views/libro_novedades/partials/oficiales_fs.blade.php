@php $ofs = $oficiales ?? null; @endphp
<h6 class="fw-bold text-warning-emphasis">
    <i class="bi bi-person-dash me-1"></i>Oficiales fuera de servicio (8-1) {{ $momento }}
</h6>
@if(is_null($ofs))
    <p class="text-muted small">Sin registro (libro anterior a esta funcionalidad).</p>
@elseif(count($ofs))
    <table class="table table-sm table-bordered mb-0">
        <thead class="table-light">
            <tr><th>Clave</th><th>Nombre</th><th>Compañía</th><th>Desde</th><th>Hasta</th><th>Motivo</th></tr>
        </thead>
        <tbody>
            @foreach($ofs as $o)
            <tr>
                <td>{{ $o['clave'] ?? '—' }}</td>
                <td class="fw-bold">{{ $o['nombre'] }}</td>
                <td>{{ $o['compania'] ?? '—' }}</td>
                <td>{{ \Carbon\Carbon::parse($o['fecha_inicio'])->format('d/m/Y') }}</td>
                <td>{{ $o['fecha_fin'] ? \Carbon\Carbon::parse($o['fecha_fin'])->format('d/m/Y') : 'Indefinido' }}</td>
                <td>{{ $o['motivo'] ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p class="text-muted small">Ningún oficial fuera de servicio.</p>
@endif