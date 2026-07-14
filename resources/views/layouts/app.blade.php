<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
{{-- Favicon --}}
<link rel="icon" type="image/png" href="{{ asset('images/logo_SanPedroDeLaPaz.png') }}">
<link rel="shortcut icon" type="image/png" href="{{ asset('images/logo_SanPedroDeLaPaz.png') }}">
<title>Central de Alarmas — @yield('title', 'Dashboard')</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

<style>
    body { background-color: #f8f9fa; }

    /* ── Sidebar desktop ──────────────────────────── */
    .sidebar { min-height:100vh; background:#1a1a2e; color:white; }
    .sidebar .nav-link { color:#adb5bd; padding:10px 20px; border-radius:6px; margin:2px 8px; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background:#e63946; color:white; }
    .sidebar .brand { padding:20px; border-bottom:1px solid #2d2d44; font-size:1.1rem; font-weight:bold; }
    .main-content { padding:30px; }
    .card { border:none; box-shadow:0 2px 10px rgba(0,0,0,0.08); }
    .pagination { font-size:0.85rem; }
    .pagination .page-link { padding:0.25rem 0.6rem; }

    /* ── Navbar móvil ─────────────────────────────── */
    .mobile-navbar {
        background: #1a1a2e;
        padding: 10px 16px;
        z-index: 1050;
    }

    /* ── Offcanvas móvil ──────────────────────────── */
    .offcanvas-mobile {
        background: #1a1a2e;
        color: white;
        max-width: 280px;
    }
    .offcanvas-mobile .offcanvas-header {
        border-bottom: 1px solid #2d2d44;
        padding: 16px;
    }
    .offcanvas-mobile .btn-close {
        filter: invert(1);
    }
    .offcanvas-mobile .nav-link {
        color: #adb5bd;
        padding: 9px 16px;
        border-radius: 6px;
        margin: 2px 8px;
        font-size: 0.9rem;
    }
    .offcanvas-mobile .nav-link:hover,
    .offcanvas-mobile .nav-link.active {
        background: #e63946;
        color: white;
    }
    .offcanvas-mobile .nav-section-label {
        padding: 4px 16px;
        font-size: 0.7rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .offcanvas-mobile .nav-divider {
        border-color: #2d2d44;
        margin: 8px 16px;
    }

    /* ── Responsive ───────────────────────────────── */
    @media (max-width: 767.98px) {
        .main-content {
            padding: 16px;
            padding-top: 20px;
        }
    }
</style>
</head>

<body>

{{-- ══ NAVBAR MÓVIL (solo visible en < md) ══════════════════════════ --}}
<div class="mobile-navbar d-md-none d-flex align-items-center justify-content-between sticky-top">
    <div class="d-flex align-items-center gap-2">
        <img src="{{ asset('images/logo2.png') }}" alt="Logo" style="width:36px; height:36px; object-fit:contain;">
        <span class="text-white fw-bold" style="font-size:0.85rem;">Administración Central</span>
    </div>
    <button class="btn btn-outline-light btn-sm" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
        <i class="bi bi-list fs-5"></i>
    </button>
</div>

{{-- ══ OFFCANVAS MÓVIL ══════════════════════════════════════════════ --}}
<div class="offcanvas offcanvas-start offcanvas-mobile d-md-none" tabindex="-1" id="offcanvasMenu">
    <div class="offcanvas-header">
        <div class="d-flex align-items-center gap-2">
            <img src="{{ asset('images/logo2.png') }}" alt="Logo" style="width:40px; height:40px; object-fit:contain;">
            <span class="fw-bold" style="font-size:0.85rem;">Administración Central</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
        <nav class="nav flex-column flex-grow-1 pt-2">
            @include('layouts._nav_links')
        </nav>

        <hr class="nav-divider">
        <div class="px-3 pb-3">
            <div class="small mb-1" style="color: #e9ecef;">
                <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->nombre }}
                <span class="badge bg-secondary ms-1">{{ auth()->user()->rol }}</span>
            </div>
            <button type="button"
                    class="btn btn-outline-secondary btn-sm w-100 mb-2"
                    data-bs-toggle="modal" data-bs-target="#modalMiUsuario">
                <i class="bi bi-person-gear me-1"></i>Mi usuario
            </button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                    <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</div>

<div class="container-fluid">
<div class="row">

{{-- ══ SIDEBAR DESKTOP (oculto en < md) ═════════════════════════════ --}}
<div class="col-md-2 sidebar px-0 d-none d-md-block">

    <div class="brand text-center">
        <img src="{{ asset('images/logo2.png') }}" alt="Logo Bomberos"
            style="width:70px; height:70px; object-fit:contain; margin-bottom:8px; display:block; margin-left:auto; margin-right:auto;">
        <div style="font-size:0.85rem; font-weight:bold; line-height:1.3;">
            Administración Central
        </div>
    </div>

    <nav class="nav flex-column mt-3">
        @include('layouts._nav_links')
    </nav>

    <hr style="border-color:#2d2d44;margin:8px 16px;">

    <div class="px-3 pb-3">
        <div class="small mb-1" style="color: #e9ecef;">
            <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->nombre }}
            <span class="badge bg-secondary ms-1">{{ auth()->user()->rol }}</span>
        </div>
        <button type="button"
                class="btn btn-outline-secondary btn-sm w-100 mb-2"
                data-bs-toggle="modal" data-bs-target="#modalMiUsuario">
            <i class="bi bi-person-gear me-1"></i>Mi usuario
        </button>
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

{{-- ── MODAL MI USUARIO ────────────────────────────────────────────── --}}
<div class="modal fade" id="modalMiUsuario" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-circle me-2"></i>Mi usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- Info del usuario --}}
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                             style="width:56px;height:56px;">
                            <i class="bi bi-person-fill fs-3 text-dark"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-6">{{ auth()->user()->nombre }}</div>
                            <div class="text-muted small">{{ auth()->user()->email }}</div>
                            <span class="badge bg-secondary mt-1">{{ auth()->user()->rol }}</span>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Formulario cambio de contraseña --}}
                <form action="{{ route('mi-usuario.password') }}" method="POST" id="formCambioPassword">
                    @csrf
                    @method('PUT')

                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-key me-1"></i>Cambiar contraseña
                    </h6>

                    @if(session('password_error'))
                        <div class="alert alert-danger py-2 small">
                            <i class="bi bi-exclamation-circle me-1"></i>{{ session('password_error') }}
                        </div>
                    @endif
                    @if(session('password_success'))
                        <div class="alert alert-success py-2 small">
                            <i class="bi bi-check-circle me-1"></i>{{ session('password_success') }}
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Contraseña actual</label>
                        <input type="password" name="password_actual"
                               class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nueva contraseña</label>
                        <input type="password" name="password_nuevo"
                               class="form-control form-control-sm" required minlength="8">
                        <div class="form-text">Mínimo 8 caracteres.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Confirmar nueva contraseña</label>
                        <input type="password" name="password_nuevo_confirmation"
                               class="form-control form-control-sm" required>
                    </div>

                    <button type="submit" class="btn btn-dark btn-sm w-100">
                        <i class="bi bi-shield-lock me-1"></i>Actualizar contraseña
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function(){
        document.querySelectorAll('.show-toast').forEach(function(el){
            new bootstrap.Toast(el, { delay: 4000 }).show();
        });

        // Reabrir modal si hay respuesta del servidor
        @if(session('abrir_modal_usuario') || session('password_error') || session('password_success'))
            new bootstrap.Modal(document.getElementById('modalMiUsuario')).show();
        @endif

        // Cerrar offcanvas al hacer click en un link
        const offcanvasEl = document.getElementById('offcanvasMenu');
        if (offcanvasEl) {
            offcanvasEl.querySelectorAll('a.nav-link').forEach(function(link) {
                link.addEventListener('click', function() {
                    var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                    if (offcanvas) offcanvas.hide();
                });
            });
        }
    });
</script>

@stack('scripts')

</body>
</html>