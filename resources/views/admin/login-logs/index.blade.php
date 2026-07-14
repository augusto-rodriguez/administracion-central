@extends('layouts.app')
@section('title', 'Registro de Accesos')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Registro de Accesos</h4>
    <button type="button" class="btn btn-success btn-sm" title="Exportar CSV con filtros actuales"
            onclick="exportarConFiltros()">
        <i class="bi bi-file-earmark-excel me-1"></i>Exportar
    </button>
</div>

{{-- Tarjetas de estadísticas --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3 col-xl">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-success">{{ number_format($stats['logins_hoy']) }}</div>
                <div class="text-muted small"><i class="bi bi-box-arrow-in-right me-1"></i>Logins hoy</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-danger">{{ number_format($stats['fallidos_hoy']) }}</div>
                <div class="text-muted small"><i class="bi bi-x-circle me-1"></i>Fallidos hoy</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-info">{{ number_format($stats['logouts_hoy']) }}</div>
                <div class="text-muted small"><i class="bi bi-box-arrow-right me-1"></i>Logouts hoy</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-primary">{{ number_format($stats['usuarios_unicos']) }}</div>
                <div class="text-muted small"><i class="bi bi-people me-1"></i>Usuarios únicos</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold {{ $stats['ips_sospechosas'] > 0 ? 'text-warning' : 'text-muted' }}">
                    {{ number_format($stats['ips_sospechosas']) }}
                </div>
                <div class="text-muted small"><i class="bi bi-exclamation-triangle me-1"></i>IPs sospechosas</div>
            </div>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('login-logs.index') }}" method="GET"
              class="row g-2 align-items-end" id="formFiltros">
            <div class="col-md-2">
                <label class="form-label fw-bold mb-1 small">Evento</label>
                <select name="evento" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="login"   {{ request('evento') == 'login'   ? 'selected' : '' }}>Inicio de sesión</option>
                    <option value="logout"  {{ request('evento') == 'logout'  ? 'selected' : '' }}>Cierre de sesión</option>
                    <option value="failed"  {{ request('evento') == 'failed'  ? 'selected' : '' }}>Intento fallido</option>
                    <option value="lockout" {{ request('evento') == 'lockout' ? 'selected' : '' }}>Bloqueo</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold mb-1 small">Usuario</label>
                <select name="user_id" class="form-select form-select-sm" id="filtroUsuario">
                    <option value="">Todos</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold mb-1 small">IP</label>
                <input type="text" name="ip_filter" class="form-control form-control-sm"
                       value="{{ request('ip_filter') }}" placeholder="Ej: 192.168.1.1">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold mb-1 small">Desde</label>
                <input type="date" name="desde" class="form-control form-control-sm"
                       value="{{ request('desde') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold mb-1 small">Hasta</label>
                <input type="date" name="hasta" class="form-control form-control-sm"
                       value="{{ request('hasta') }}">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-danger btn-sm flex-fill">
                    <i class="bi bi-search me-1"></i>Filtrar
                </button>
                <a href="{{ route('login-logs.index') }}"
                   class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabla de registros --}}
<div class="card">
    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-ul me-2"></i>Historial de Accesos</span>
        @if(request()->hasAny(['evento', 'user_id', 'ip_filter', 'desde', 'hasta']))
            <span class="badge bg-danger">Filtro activo</span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha / Hora</th>
                        <th>Evento</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>IP</th>
                        <th>Navegador</th>
                        <th>Plataforma</th>
                        <th>Dispositivo</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="{{ !$log->exitoso ? 'table-danger bg-opacity-10' : '' }}">
                        <td class="text-nowrap">
                            <div>{{ $log->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                        </td>
                        <td>
                            @switch($log->evento)
                                @case('login')
                                    <span class="badge bg-success"><i class="bi bi-box-arrow-in-right me-1"></i>Login</span>
                                    @break
                                @case('logout')
                                    <span class="badge bg-info"><i class="bi bi-box-arrow-right me-1"></i>Logout</span>
                                    @break
                                @case('failed')
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Fallido</span>
                                    @break
                                @case('lockout')
                                    <span class="badge bg-dark"><i class="bi bi-lock me-1"></i>Bloqueado</span>
                                    @break
                            @endswitch
                        </td>
                        <td>{{ $log->user?->nombre ?? '—' }}</td>
                        <td><small>{{ $log->email ?? '—' }}</small></td>
                        <td>
                            <code class="text-dark">{{ $log->ip }}</code>
                        </td>
                        <td>
                            @if($log->navegador)
                                <small>{{ $log->navegador }}</small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td>
                            @if($log->plataforma)
                                <small>
                                    @switch($log->plataforma)
                                        @case('Windows')  <i class="bi bi-windows me-1"></i> @break
                                        @case('macOS')    <i class="bi bi-apple me-1"></i> @break
                                        @case('Linux')    <i class="bi bi-ubuntu me-1"></i> @break
                                        @case('Android')  <i class="bi bi-android2 me-1"></i> @break
                                        @case('iOS')
                                        @case('iPadOS')   <i class="bi bi-phone me-1"></i> @break
                                    @endswitch
                                    {{ $log->plataforma }}
                                </small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td>
                            @if($log->dispositivo)
                                @switch($log->dispositivo)
                                    @case('desktop') <i class="bi bi-pc-display text-secondary"></i> @break
                                    @case('mobile')  <i class="bi bi-phone text-primary"></i> @break
                                    @case('tablet')  <i class="bi bi-tablet text-info"></i> @break
                                @endswitch
                            @endif
                        </td>
                        <td>
                            @if($log->motivo_fallo)
                                <small class="text-danger">
                                    @switch($log->motivo_fallo)
                                        @case('credenciales_invalidas')
                                            <i class="bi bi-key me-1"></i>Credenciales inválidas
                                            @break
                                        @case('cuenta_inactiva')
                                            <i class="bi bi-person-slash me-1"></i>Cuenta inactiva
                                            @break
                                        @default
                                            {{ $log->motivo_fallo }}
                                    @endswitch
                                </small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-shield-check fs-3 d-block mb-2"></i>
                            No hay registros de acceso
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 d-flex justify-content-center">
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
new TomSelect('#filtroUsuario', {
    placeholder: 'Buscar usuario...',
    searchField: ['text'],
    allowEmptyOption: true,
});

function exportarConFiltros() {
    const form      = document.getElementById('formFiltros');
    const params    = new URLSearchParams(new FormData(form));
    const exportUrl = "{{ route('login-logs.exportar') }}" + '?' + params.toString();
    window.location.href = exportUrl;
}
</script>
@endpush