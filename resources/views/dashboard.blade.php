@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h4>
    <div class="d-flex align-items-center gap-3">

        {{-- Comandante de guardia --}}
        <div class="d-flex align-items-center gap-2">
            @if($guardiaActual)
                <span class="badge bg-dark d-flex align-items-center gap-1 py-2 px-3">
                    <i class="bi bi-shield-fill me-1"></i>
                    Cdte. Guardia:
                    <strong class="ms-1">
                        {{ $guardiaActual->voluntario->nombre }}
                        ({{ $guardiaActual->voluntario->roles->firstWhere('rol', 'comandante')?->rango }}°)
                    </strong>
                </span>
            @else
                <span class="badge bg-warning text-dark d-flex align-items-center gap-1 py-2 px-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Sin comandante de guardia
                </span>
            @endif
            @if(!auth()->user()->esAdmin() && !auth()->user()->esComandante())
                <button class="btn btn-sm btn-outline-dark"
                        data-bs-toggle="modal" data-bs-target="#modalGuardia"
                        title="Cambiar comandante de guardia">
                    <i class="bi bi-shield-lock me-1"></i>Guardia
                </button>
            @endif
        </div>

        <span class="text-muted">{{ now()->format('d/m/Y H:i') }}</span>
    </div>
</div>

{{-- ── MODAL GUARDIA COMANDANTE ─────────────────────────────────────── --}}
@if(!auth()->user()->esAdmin() && !auth()->user()->esComandante())
<div class="modal fade" id="modalGuardia" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title mb-0">
                    <i class="bi bi-shield-lock me-2"></i>Comandante de guardia
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('dashboard.guardia-comandante') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Semana del {{ \Carbon\Carbon::now()->startOfWeek()->format('d/m') }}
                        al {{ \Carbon\Carbon::now()->endOfWeek()->format('d/m/Y') }}
                    </p>
                    <label class="form-label fw-bold small">Comandante de guardia</label>
                    <select name="voluntario_id" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        @foreach($comandantes as $rol)
                            <option value="{{ $rol->voluntario->id }}"
                                    {{ $guardiaActual?->voluntario_id == $rol->voluntario->id ? 'selected' : '' }}>
                                {{ $rol->rango }}° Cdte. — {{ $rol->voluntario->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark btn-sm">
                        <i class="bi bi-shield-check me-1"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════
     VISTA ADMIN / COMANDANTE
═══════════════════════════════════════════════════════════════════════ --}}
@if(auth()->user()->esAdmin() || auth()->user()->esComandante())

    {{-- Tarjetas resumen --}}
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="bg-danger bg-opacity-10 rounded p-3">
                        <i class="bi bi-building fs-3 text-danger"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Compañías</div>
                        <div class="fs-3 fw-bold">{{ $totalCompanias }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 rounded p-3">
                        <i class="bi bi-truck-front fs-3 text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Unidades</div>
                        <div class="fs-3 fw-bold">{{ $totalUnidades }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 rounded p-3">
                        <i class="bi bi-person-gear fs-3 text-info"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Cuarteleros</div>
                        <div class="fs-3 fw-bold">{{ $totalCuarteleros }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 rounded p-3">
                        <i class="bi bi-person-badge fs-3 text-warning"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Maquinistas en servicio</div>
                        <div class="fs-3 fw-bold">{{ $enServicio }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Maquinistas en servicio --}}
    <div class="mt-4">
        <div class="card">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-activity text-danger me-2"></i>Maquinistas en Servicio
                @if($turnosActivos->count())
                    <span class="badge bg-danger ms-1">{{ $turnosActivos->count() }}</span>
                @endif
            </div>
            <div class="card-body">
                @if($turnosActivos->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-moon fs-3"></i>
                        <p class="mt-2 mb-0">Sin maquinistas en servicio</p>
                    </div>
                @else
                    @php
                        $porCompania = $turnosActivos->groupBy(fn($t) => $t->voluntario->compania->nombre);
                    @endphp
                    <div class="row g-3">
                        @foreach($porCompania as $compania => $turnos)
                        <div class="col-md-6 col-xl-3">
                            <div class="card border h-100">
                                <div class="card-header py-2 bg-danger bg-opacity-10">
                                    <div class="fw-bold text-danger small">
                                        <i class="bi bi-building me-1"></i>{{ $compania }}
                                    </div>
                                    <div class="text-muted" style="font-size:0.75rem">
                                        {{ $turnos->count() }} maquinista(s) en servicio
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-hover table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Maquinista</th>
                                                <th>Unidades</th>
                                                <th>Tiempo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($turnos as $turno)
                                            <tr>
                                                <td>
                                                    <i class="bi bi-person-fill text-success me-1"></i>
                                                    {{ $turno->voluntario->nombre }}
                                                </td>
                                                <td>
                                                    @foreach($turno->unidades as $unidad)
                                                        <span class="badge bg-primary me-1">{{ $unidad->nombre }}</span>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    <span class="badge cronometro"
                                                          data-entrada="{{ $turno->entrada_at->timestamp }}">
                                                        Calculando...
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Cuarteleros en servicio --}}
    <div class="mt-4">
        <div class="card">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-activity text-primary me-2"></i>Cuarteleros en Servicio
                @if($turnosActivosCuarteleros->count())
                    <span class="badge bg-primary ms-1">{{ $turnosActivosCuarteleros->count() }}</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($turnosActivosCuarteleros->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-moon fs-3"></i>
                        <p class="mt-2 mb-0">Sin cuarteleros en servicio</p>
                    </div>
                @else
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Cuartelero</th>
                                <th>Compañía</th>
                                <th>Unidades</th>
                                <th>Tiempo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($turnosActivosCuarteleros as $turno)
                            <tr>
                                <td>
                                    <i class="bi bi-person-gear text-primary me-1"></i>
                                    {{ $turno->cuartelero->nombre }}
                                </td>
                                <td class="text-muted small">{{ $turno->cuartelero->compania->nombre }}</td>
                                <td>
                                    @foreach($turno->unidades as $unidad)
                                        <span class="badge bg-primary me-1">{{ $unidad->nombre }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <span class="badge cronometro"
                                          data-entrada="{{ $turno->entrada_at->timestamp }}">
                                        Calculando...
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    {{-- Salidas activas --}}
    <div class="mt-4">
        <div class="card">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-arrow-up-right-circle text-warning me-2"></i>Salidas Activas
                @if($salidasActivas->count())
                    <span class="badge bg-warning text-dark ms-1">{{ $salidasActivas->count() }}</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($salidasActivas->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-check-circle fs-3"></i>
                        <p class="mt-2 mb-0">Sin salidas activas en este momento</p>
                    </div>
                @else
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Unidad</th>
                                <th>Compañía</th>
                                <th>Clave</th>
                                <th>Conductor</th>
                                <th>Dirección</th>
                                <th>Tiempo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salidasActivas as $salida)
                            <tr>
                                <td>
                                    <span class="badge bg-danger">{{ $salida->unidad->nombre }}</span>
                                </td>
                                <td class="text-muted small">{{ $salida->unidad->compania->nombre }}</td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $salida->claveSalida->codigo }} — {{ $salida->claveSalida->tipo }}
                                    </span>
                                </td>
                                <td>{{ $salida->conductor_nombre }}</td>
                                <td class="small">{{ $salida->direccion }}</td>
                                <td>
                                    <span class="badge cronometro"
                                          data-entrada="{{ $salida->salida_at->timestamp }}">
                                        Calculando...
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

{{-- ══════════════════════════════════════════════════════════════════════
     VISTA OPERADOR — accesos directos
═══════════════════════════════════════════════════════════════════════ --}}
@else

    <p class="text-muted mb-4">Selecciona una operación para comenzar.</p>

    @php
        $accesos = [
            [
                'titulo'   => 'Puestas en Servicio',
                'ruta'     => route('turnos.index'),
                'icono'    => 'bi-clock-history',
                'color'    => 'danger',
                'desc'     => 'Registrar entrada y salida de maquinistas',
            ],
            [
                'titulo'   => 'Registro Salidas',
                'ruta'     => route('salidas.index'),
                'icono'    => 'bi-arrow-up-right-circle',
                'color'    => 'warning',
                'desc'     => 'Registrar salidas administrativas y emergencias',
            ],
            [
                'titulo'   => 'Registro Combustible',
                'ruta'     => route('vouchers-combustible.index'),
                'icono'    => 'bi-fuel-pump',
                'color'    => 'success',
                'desc'     => 'Registrar vouchers y consumo de combustible',
            ],
            [
                'titulo'   => 'Libro de Novedades',
                'ruta'     => route('libro-novedades.index'),
                'icono'    => 'bi-journal-text',
                'color'    => 'primary',
                'desc'     => 'Gestionar el libro de novedades del turno',
            ],
            [
                'titulo'   => 'Citaciones',
                'ruta'     => route('citaciones.index'),
                'icono'    => 'bi-megaphone',
                'color'    => 'info',
                'desc'     => 'Ver y registrar citaciones vigentes',
            ],
            [
                'titulo'   => 'Boletines',
                'ruta'     => route('boletines.index'),
                'icono'    => 'bi-file-earmark-text',
                'color'    => 'dark',
                'desc'     => 'Generar y consultar boletines del turno',
            ],
            [
                'titulo'   => 'Guardias Nocturnas',
                'ruta'     => route('guardias-nocturnas.index'),
                'icono'    => 'bi-moon-stars',
                'color'    => 'dark',
                'desc'     => 'Registrar y consultar guardias nocturnas',
            ],
        ];
    @endphp

    <div class="row g-4">
        @foreach($accesos as $acceso)
        <div class="col-md-4">
            <a href="{{ $acceso['ruta'] }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm acceso-card"
                     style="transition: transform 0.15s ease, box-shadow 0.15s ease; cursor: pointer;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center py-5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-4
                                    bg-{{ $acceso['color'] }} bg-opacity-10"
                             style="width: 80px; height: 80px;">
                            <i class="bi {{ $acceso['icono'] }} text-{{ $acceso['color'] }}"
                               style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">{{ $acceso['titulo'] }}</h5>
                        <p class="text-muted small mb-0">{{ $acceso['desc'] }}</p>
                    </div>
                    <div class="card-footer border-0 bg-{{ $acceso['color'] }} bg-opacity-10 text-center py-2">
                        <small class="text-{{ $acceso['color'] }} fw-bold">
                            <i class="bi bi-arrow-right me-1"></i>Ir al módulo
                        </small>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

@endif

@endsection

@push('scripts')
<script>
// Hover en tarjetas de acceso operador
document.querySelectorAll('.acceso-card').forEach(card => {
    card.addEventListener('mouseenter', () => {
        card.style.transform = 'translateY(-4px)';
        card.style.boxShadow = '0 8px 25px rgba(0,0,0,0.12)';
    });
    card.addEventListener('mouseleave', () => {
        card.style.transform = 'translateY(0)';
        card.style.boxShadow = '0 2px 10px rgba(0,0,0,0.08)';
    });
});

// Cronómetros
function actualizarCronometros() {
    document.querySelectorAll('.cronometro').forEach(el => {
        const entrada  = parseInt(el.dataset.entrada);
        const ahora    = Math.floor(Date.now() / 1000);
        const diff     = ahora - entrada;
        const horas    = Math.floor(diff / 3600);
        const minutos  = Math.floor((diff % 3600) / 60);
        const segundos = diff % 60;
        const pad = n => String(n).padStart(2, '0');
        el.textContent = `${pad(horas)}:${pad(minutos)}:${pad(segundos)}`;
        if (diff > 28800) {
            el.className = 'badge bg-danger cronometro';
        } else if (diff > 14400) {
            el.className = 'badge bg-warning text-dark cronometro';
        } else {
            el.className = 'badge bg-success cronometro';
        }
    });
}
actualizarCronometros();
setInterval(actualizarCronometros, 1000);
</script>
@endpush