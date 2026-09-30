{{-- ── Links de navegación compartidos (sidebar + offcanvas) ──
     Se incluye dos veces, por eso recibe $prefix (ids únicos para los collapse) --}}

@php
    $u        = auth()->user();
    $p        = $prefix ?? 'nav';
    $esAdmin  = $u->esAdmin();
    $esCmd    = $u->esComandante();
    $esCap    = $u->esCapitanCia();

    // Solo el admin usa modo acordeón (una sección abierta a la vez).
    // Los demás roles ven sus secciones abiertas por defecto.
    $acordeon = $esAdmin;
    $abrirTodo = ! $esAdmin;

    // ── Qué secciones ve cada rol ──
    $verGestion     = $esAdmin || $esCmd || $esCap;
    $verOperaciones = (! $esCmd && ! $esCap) || $esAdmin;
    $verChecklist   = $esAdmin || $esCmd || $esCap;
    $verAdmin       = $esAdmin;

    // ── Sección activa según la URL actual ──
    $gestionActiva = request()->is('voluntarios*', 'cuarteleros*', 'cargos*', 'unidades*', 'claves-salida*', 'companias*');
    $operacionesActiva = request()->is('turnos*', 'salidas*', 'libro-novedades*', 'citaciones*', 'boletines*', 'guardias-nocturnas*')
        || request()->routeIs('vouchers-combustible.*');
    $checklistActiva = request()->routeIs('checklist.*', 'hallazgos.*', 'checklist-plantillas.*', 'checklist-config.*');
    $reportesActiva  = request()->is('reportes', 'reportes/*', 'estadisticas*');
    $adminActiva     = request()->is('usuarios*', 'login-logs*');

    $parentAttr = $acordeon ? 'data-bs-parent=#' . $p . '-menu' : '';
@endphp

{{-- Si es cuartelero, solo ve el módulo de checklist --}}
@if($u->esCuartelero())

    <a href="{{ route('checklist.index') }}"
       class="nav-link {{ request()->routeIs('checklist.*') ? 'active' : '' }}">
        <i class="bi bi-clipboard-check me-2"></i> Checklist
    </a>

@else

    {{-- Inicio (siempre visible, fuera de secciones) --}}
    <a href="{{ route('dashboard') }}"
       class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2 me-2"></i> Inicio
    </a>

    {{-- ══ GESTIÓN ═══════════════════════════════════════════ --}}
    @if($verGestion)
        @php $abierta = $gestionActiva || $abrirTodo; @endphp
        <button type="button"
                class="nav-section-toggle {{ $abierta ? '' : 'collapsed' }} {{ $gestionActiva ? 'has-active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#{{ $p }}-gestion"
                aria-expanded="{{ $abierta ? 'true' : 'false' }}">
            <span><i class="bi bi-folder2-open me-2"></i>Gestión</span>
            <i class="bi bi-chevron-down chevron"></i>
        </button>
        <div class="collapse nav-section {{ $abierta ? 'show' : '' }}" id="{{ $p }}-gestion" {{ $parentAttr }}>
            <a href="{{ route('voluntarios.index') }}"
               class="nav-link {{ request()->is('voluntarios*') ? 'active' : '' }}">
                <i class="bi bi-people me-2"></i> Voluntarios
            </a>

            @if($esCmd || $esAdmin)
                <a href="{{ route('cuarteleros.index') }}"
                   class="nav-link {{ request()->is('cuarteleros*') ? 'active' : '' }}">
                    <i class="bi bi-person-gear me-2"></i> Cuarteleros
                </a>
                <a href="{{ route('cargos.index') }}"
                   class="nav-link {{ request()->is('cargos*') ? 'active' : '' }}">
                    <i class="bi bi-award me-2"></i> Cargos
                </a>
            @endif

            <a href="{{ route('unidades.index') }}"
               class="nav-link {{ request()->is('unidades*') ? 'active' : '' }}">
                <i class="bi bi-truck-front me-2"></i> Unidades
            </a>

            @if($esCmd || $esAdmin)
                <a href="{{ route('claves-salida.index') }}"
                   class="nav-link {{ request()->is('claves-salida*') ? 'active' : '' }}">
                    <i class="bi bi-tag me-2"></i> Claves de Salida
                </a>
            @endif

            @if($esAdmin)
                <a href="{{ route('companias.index') }}"
                   class="nav-link {{ request()->is('companias*') ? 'active' : '' }}">
                    <i class="bi bi-building me-2"></i> Compañías
                </a>
            @endif
        </div>
    @endif

    {{-- ══ OPERACIONES ═══════════════════════════════════════ --}}
    @if($verOperaciones)
        @php $abierta = $operacionesActiva || $abrirTodo; @endphp
        <button type="button"
                class="nav-section-toggle {{ $abierta ? '' : 'collapsed' }} {{ $operacionesActiva ? 'has-active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#{{ $p }}-operaciones"
                aria-expanded="{{ $abierta ? 'true' : 'false' }}">
            <span><i class="bi bi-broadcast me-2"></i>Operaciones</span>
            <i class="bi bi-chevron-down chevron"></i>
        </button>
        <div class="collapse nav-section {{ $abierta ? 'show' : '' }}" id="{{ $p }}-operaciones" {{ $parentAttr }}>
            <a href="{{ route('turnos.index') }}"
               class="nav-link {{ request()->is('turnos*') ? 'active' : '' }}">
                <i class="bi bi-clock-history me-2"></i> Puestas en Servicio
            </a>
            <a href="{{ route('salidas.index') }}"
               class="nav-link {{ request()->is('salidas*') ? 'active' : '' }}">
                <i class="bi bi-arrow-up-right-circle me-2"></i> Registro Salidas
            </a>
            <a href="{{ route('vouchers-combustible.index') }}"
               class="nav-link {{ request()->routeIs('vouchers-combustible.*') ? 'active' : '' }}">
                <i class="bi bi-fuel-pump me-2"></i> Registro Combustible
            </a>
            <a href="{{ route('libro-novedades.index') }}"
               class="nav-link {{ request()->is('libro-novedades*') ? 'active' : '' }}">
                <i class="bi bi-journal-text me-2"></i> Libro de Novedades
            </a>
            <a href="{{ route('citaciones.index') }}"
               class="nav-link {{ request()->is('citaciones*') ? 'active' : '' }}">
                <i class="bi bi-megaphone me-2"></i> Citaciones
            </a>
            <a href="{{ route('boletines.index') }}"
               class="nav-link {{ request()->is('boletines*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text me-2"></i> Boletines
            </a>
            <a href="{{ route('guardias-nocturnas.index') }}"
               class="nav-link {{ request()->is('guardias-nocturnas*') ? 'active' : '' }}">
                <i class="bi bi-moon-stars me-2"></i> Guardias Nocturnas
            </a>
        </div>
    @endif

    {{-- ══ CHECKLIST MATERIAL MAYOR ══════════════════════════ --}}
    @if($verChecklist)
        @php $abierta = $checklistActiva || $abrirTodo; @endphp
        <button type="button"
                class="nav-section-toggle {{ $abierta ? '' : 'collapsed' }} {{ $checklistActiva ? 'has-active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#{{ $p }}-checklist"
                aria-expanded="{{ $abierta ? 'true' : 'false' }}">
            <span><i class="bi bi-clipboard-check me-2"></i>Material Mayor</span>
            <i class="bi bi-chevron-down chevron"></i>
        </button>
        <div class="collapse nav-section {{ $abierta ? 'show' : '' }}" id="{{ $p }}-checklist" {{ $parentAttr }}>
            <a href="{{ route('checklist.index') }}"
               class="nav-link {{ request()->routeIs('checklist.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check me-2"></i> Checklist
            </a>
            <a href="{{ route('hallazgos.index') }}"
               class="nav-link {{ request()->routeIs('hallazgos.*') ? 'active' : '' }}">
                <i class="bi bi-exclamation-diamond me-2"></i> Hallazgos
            </a>

            @if($esAdmin || $esCmd)
                <a href="{{ route('checklist-plantillas.index') }}"
                   class="nav-link {{ request()->routeIs('checklist-plantillas.*') ? 'active' : '' }}">
                    <i class="bi bi-gear me-2"></i> Plantillas Checklist
                </a>
                <a href="{{ route('checklist-config.index') }}"
                   class="nav-link {{ request()->routeIs('checklist-config.*') ? 'active' : '' }}">
                    <i class="bi bi-sliders me-2"></i> Config. Checklist
                </a>
            @endif
        </div>
    @endif

    {{-- ══ REPORTES ══════════════════════════════════════════ --}}
    @php $abierta = $reportesActiva || $abrirTodo; @endphp
    <button type="button"
            class="nav-section-toggle {{ $abierta ? '' : 'collapsed' }} {{ $reportesActiva ? 'has-active' : '' }}"
            data-bs-toggle="collapse" data-bs-target="#{{ $p }}-reportes"
            aria-expanded="{{ $abierta ? 'true' : 'false' }}">
        <span><i class="bi bi-bar-chart-line me-2"></i>Reportes</span>
        <i class="bi bi-chevron-down chevron"></i>
    </button>
    <div class="collapse nav-section {{ $abierta ? 'show' : '' }}" id="{{ $p }}-reportes" {{ $parentAttr }}>
        <a href="{{ route('reportes.index') }}"
           class="nav-link {{ request()->is('reportes') || request()->is('reportes?*') ? 'active' : '' }}">
            <i class="bi bi-person-badge me-2"></i> Maquinistas
        </a>
        <a href="{{ route('reportes.salidas') }}"
           class="nav-link {{ request()->is('reportes/salidas*') ? 'active' : '' }}">
            <i class="bi bi-arrow-up-right-circle me-2"></i> Salidas
        </a>

        @if($esAdmin || $esCmd || $esCap)
            <a href="{{ route('estadisticas.index') }}"
               class="nav-link {{ request()->is('estadisticas*') ? 'active' : '' }}">
                <i class="bi bi-trophy me-2"></i> Estadísticas Maquinistas
            </a>
        @endif

        @if($esAdmin || $esCmd)
            <a href="{{ route('reportes.combustible') }}"
               class="nav-link {{ request()->is('reportes/combustible*') ? 'active' : '' }}">
                <i class="bi bi-fuel-pump me-2"></i> Estadísticas Combustible
            </a>
        @endif

        @if($esAdmin || $esCmd || $esCap)
            <a href="{{ route('reportes.guardias-nocturnas') }}"
               class="nav-link {{ request()->is('reportes/guardias-nocturnas*') ? 'active' : '' }}">
                <i class="bi bi-moon-stars me-2"></i> Guardias Nocturnas
            </a>
        @endif
    </div>

    {{-- ══ ADMINISTRACIÓN (solo admin) ═══════════════════════ --}}
    @if($verAdmin)
        @php $abierta = $adminActiva || $abrirTodo; @endphp
        <button type="button"
                class="nav-section-toggle {{ $abierta ? '' : 'collapsed' }} {{ $adminActiva ? 'has-active' : '' }}"
                data-bs-toggle="collapse" data-bs-target="#{{ $p }}-admin"
                aria-expanded="{{ $abierta ? 'true' : 'false' }}">
            <span><i class="bi bi-shield-check me-2"></i>Administración</span>
            <i class="bi bi-chevron-down chevron"></i>
        </button>
        <div class="collapse nav-section {{ $abierta ? 'show' : '' }}" id="{{ $p }}-admin" {{ $parentAttr }}>
            <a href="{{ route('usuarios.index') }}"
               class="nav-link {{ request()->is('usuarios*') ? 'active' : '' }}">
                <i class="bi bi-person-lock me-2"></i> Usuarios
            </a>
            <a href="{{ route('login-logs.index') }}"
               class="nav-link {{ request()->is('login-logs*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock me-2"></i> Registro de Accesos
            </a>
        </div>
    @endif

@endif