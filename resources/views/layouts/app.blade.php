<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Central de Alarmas — @yield('title', 'Dashboard')</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

<style>
    body { background-color: #f8f9fa; }
    .sidebar { min-height:100vh; background:#1a1a2e; color:white; }
    .sidebar .nav-link { color:#adb5bd; padding:10px 20px; border-radius:6px; margin:2px 8px; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background:#e63946; color:white; }
    .sidebar .brand { padding:20px; border-bottom:1px solid #2d2d44; font-size:1.1rem; font-weight:bold; }
    .main-content { padding:30px; }
    .card { border:none; box-shadow:0 2px 10px rgba(0,0,0,0.08); }
    .pagination { font-size:0.85rem; }
    .pagination .page-link { padding:0.25rem 0.6rem; }
</style>
</head>

<body>
<div class="container-fluid">
<div class="row">

{{-- Sidebar --}}
<div class="col-md-2 sidebar px-0">

    <!-- <div class="brand">Administración Central</div> -->
    <div class="brand text-center">
        <img src="{{ asset('images/logo2.png') }}" alt="Logo Bomberos"
            style="width:70px; height:70px; object-fit:contain; margin-bottom:8px; display:block; margin-left:auto; margin-right:auto;">
        <div style="font-size:0.85rem; font-weight:bold; line-height:1.3;">
            Administración Central
        </div>
    </div>

    <nav class="nav flex-column mt-3">

        {{-- Inicio --}}
        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 me-2"></i> Inicio
        </a>

        {{-- Comandante --}}
        @if(auth()->user()->esComandante())
            <a href="{{ route('voluntarios.index') }}"
               class="nav-link {{ request()->is('voluntarios*') ? 'active' : '' }}">
                <i class="bi bi-people me-2"></i> Voluntarios
            </a>
            <a href="{{ route('cuarteleros.index') }}"
               class="nav-link {{ request()->is('cuarteleros*') ? 'active' : '' }}">
                <i class="bi bi-person-gear me-2"></i> Cuarteleros
            </a>
            <a href="{{ route('unidades.index') }}"
               class="nav-link {{ request()->is('unidades*') ? 'active' : '' }}">
                <i class="bi bi-truck-front me-2"></i> Unidades
            </a>
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

        {{-- ── OPERACIONES (solo operadores) ────────────────────── --}}
        @if(!auth()->user()->esAdmin() && !auth()->user()->esComandante())
            <hr style="border-color:#2d2d44;margin:8px 16px;">
            <div style="padding:4px 20px;font-size:0.7rem;color:#6c757d;text-transform:uppercase;letter-spacing:1px;">
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
        <hr style="border-color:#2d2d44;margin:8px 16px;">
        <div style="padding:4px 20px;font-size:0.7rem;color:#6c757d;text-transform:uppercase;letter-spacing:1px;">
            Reportes
        </div>

        <a href="{{ route('reportes.index') }}"
           class="nav-link ps-4 {{ request()->is('reportes') || request()->is('reportes?*') ? 'active' : '' }}">
            <i class="bi bi-person-badge me-2"></i> Maquinistas
        </a>
        <a href="{{ route('reportes.salidas') }}"
           class="nav-link ps-4 {{ request()->is('reportes/salidas*') ? 'active' : '' }}">
            <i class="bi bi-arrow-up-right-circle me-2"></i> Salidas
        </a>

        @if(auth()->user()->esAdmin() || auth()->user()->esComandante())
            <a href="{{ route('reportes.combustible') }}"
                class="nav-link ps-4 {{ request()->is('reportes/combustible*') ? 'active' : '' }}">
                <i class="bi bi-fuel-pump me-2"></i> Estadísticas Combustible
            </a>
            <a href="{{ route('estadisticas.index') }}"
                class="nav-link ps-4 {{ request()->is('estadisticas*') ? 'active' : '' }}">
                <i class="bi bi-trophy me-2"></i> Estadísticas Maquinistas
            </a>
            <a href="{{ route('reportes.guardias-nocturnas') }}"
                class="nav-link ps-4 {{ request()->is('reportes/guardias-nocturnas*') ? 'active' : '' }}">
                <i class="bi bi-moon-stars me-2"></i> Guardias Nocturnas
            </a>
        @endif

        {{-- ── ADMINISTRACIÓN (solo admin) ───────────────────────── --}}
        @if(auth()->user()->esAdmin())
            <hr style="border-color:#2d2d44;margin:8px 16px;">
            <div style="padding:4px 20px;font-size:0.7rem;color:#6c757d;text-transform:uppercase;letter-spacing:1px;">
                Administración
            </div>
            <a href="{{ route('usuarios.index') }}"
               class="nav-link {{ request()->is('usuarios*') ? 'active' : '' }}">
                <i class="bi bi-person-lock me-2"></i> Usuarios
            </a>
        @endif

    </nav>

    <hr style="border-color:#2d2d44;margin:8px 16px;">

    <div class="px-3 pb-3">
        <div class="text-muted small mb-1">
            <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->nombre }}
            <span class="badge bg-secondary ms-1">{{ auth()->user()->rol }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
            </button>
        </form>
    </div>

</div>

{{-- CONTENIDO --}}
<div class="col-md-10 main-content">
    @yield('content')
</div>

</div>
</div>

{{-- TOASTS --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1100">
    @if(session('success'))
        <div class="toast text-bg-success border-0 show-toast">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
                <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="toast text-bg-danger border-0 show-toast">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
                <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    @endif
    @if(session('warning'))
        <div class="toast text-bg-warning border-0 show-toast">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                </div>
                <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function(){
        document.querySelectorAll('.show-toast').forEach(function(el){
            new bootstrap.Toast(el, { delay: 4000 }).show();
        });
    });
</script>

@stack('scripts')

</body>
</html>