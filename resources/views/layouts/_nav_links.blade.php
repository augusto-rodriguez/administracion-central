{{-- ── Links de navegación compartidos (sidebar + offcanvas) ── --}}

{{-- Inicio --}}
<a href="{{ route('dashboard') }}"
   class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2 me-2"></i> Inicio
</a>

{{-- Comandante + Admin + Capitán Cía --}}
@if(auth()->user()->esComandante() || auth()->user()->esAdmin() || auth()->user()->esCapitanCia())
    <a href="{{ route('voluntarios.index') }}"
       class="nav-link {{ request()->is('voluntarios*') ? 'active' : '' }}">
        <i class="bi bi-people me-2"></i> Voluntarios
    </a>
@endif

{{-- Comandante + Admin (no Capitán Cía) --}}
@if(auth()->user()->esComandante() || auth()->user()->esAdmin())
    <a href="{{ route('cuarteleros.index') }}"
       class="nav-link {{ request()->is('cuarteleros*') ? 'active' : '' }}">
        <i class="bi bi-person-gear me-2"></i> Cuarteleros
    </a>
    <a href="{{ route('cargos.index') }}"
        class="nav-link {{ request()->is('cargos*') ? 'active' : '' }}">
        <i class="bi bi-award me-2"></i> Cargos
    </a>
@endif

{{-- Comandante + Admin + Capitán Cía --}}
@if(auth()->user()->esComandante() || auth()->user()->esAdmin() || auth()->user()->esCapitanCia())
    <a href="{{ route('unidades.index') }}"
       class="nav-link {{ request()->is('unidades*') ? 'active' : '' }}">
        <i class="bi bi-truck-front me-2"></i> Unidades
    </a>
@endif

{{-- Comandante + Admin (no Capitán Cía) --}}
@if(auth()->user()->esComandante() || auth()->user()->esAdmin())
    <a href="{{ route('claves-salida.index') }}"
       class="nav-link {{ request()->is('claves-salida*') ? 'active' : '' }}">
        <i class="bi bi-tag me-2"></i> Claves de Salida
    </a>
@endif

{{-- Admin --}}
@if(auth()->user()->esAdmin())
    <a href="{{ route('companias.index') }}"
       class="nav-link {{ request()->is('companias*') ? 'active' : '' }}">
        <i class="bi bi-building me-2"></i> Compañías
    </a>
@endif

{{-- ── OPERACIONES (operadores + admin) ─────────────────── --}}
@if(!auth()->user()->esComandante() && !auth()->user()->esCapitanCia() || auth()->user()->esAdmin())
    <hr class="nav-divider" style="border-color:#2d2d44;margin:8px 16px;">
    <div class="nav-section-label" style="padding:4px 20px;font-size:0.7rem;color:#6c757d;text-transform:uppercase;letter-spacing:1px;">
        Operaciones
    </div>

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
@endif

{{-- ── REPORTES ───────────────────────────────────────────── --}}
<hr class="nav-divider" style="border-color:#2d2d44;margin:8px 16px;">
<div class="nav-section-label" style="padding:4px 20px;font-size:0.7rem;color:#6c757d;text-transform:uppercase;letter-spacing:1px;">
    Reportes
</div>

{{-- Maquinistas y Salidas: todos los roles --}}
<a href="{{ route('reportes.index') }}"
   class="nav-link ps-4 {{ request()->is('reportes') || request()->is('reportes?*') ? 'active' : '' }}">
    <i class="bi bi-person-badge me-2"></i> Maquinistas
</a>
<a href="{{ route('reportes.salidas') }}"
   class="nav-link ps-4 {{ request()->is('reportes/salidas*') ? 'active' : '' }}">
    <i class="bi bi-arrow-up-right-circle me-2"></i> Salidas
</a>

{{-- Reportes extendidos: Admin + Comandante + Capitán Cía --}}
@if(auth()->user()->esAdmin() || auth()->user()->esComandante() || auth()->user()->esCapitanCia())
    <a href="{{ route('estadisticas.index') }}"
        class="nav-link ps-4 {{ request()->is('estadisticas*') ? 'active' : '' }}">
        <i class="bi bi-trophy me-2"></i> Estadísticas Maquinistas
    </a>
@endif

{{-- Reportes solo Admin + Comandante --}}
@if(auth()->user()->esAdmin() || auth()->user()->esComandante())
    <a href="{{ route('reportes.combustible') }}"
        class="nav-link ps-4 {{ request()->is('reportes/combustible*') ? 'active' : '' }}">
        <i class="bi bi-fuel-pump me-2"></i> Estadísticas Combustible
    </a>
    <a href="{{ route('reportes.guardias-nocturnas') }}"
        class="nav-link ps-4 {{ request()->is('reportes/guardias-nocturnas*') ? 'active' : '' }}">
        <i class="bi bi-moon-stars me-2"></i> Guardias Nocturnas
    </a>
@endif

{{-- ── ADMINISTRACIÓN (solo admin) ───────────────────────── --}}
@if(auth()->user()->esAdmin())
    <hr class="nav-divider" style="border-color:#2d2d44;margin:8px 16px;">
    <div class="nav-section-label" style="padding:4px 20px;font-size:0.7rem;color:#6c757d;text-transform:uppercase;letter-spacing:1px;">
        Administración
    </div>
    <a href="{{ route('usuarios.index') }}"
       class="nav-link {{ request()->is('usuarios*') ? 'active' : '' }}">
        <i class="bi bi-person-lock me-2"></i> Usuarios
    </a>
      <a href="{{ route('login-logs.index') }}"
       class="nav-link {{ request()->is('login-logs*') ? 'active' : '' }}">
        <i class="bi bi-shield-lock me-2"></i> Registro de Accesos
    </a>
@endif