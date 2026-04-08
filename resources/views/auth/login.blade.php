<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Central de Alarmas — Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #1a1a2e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
<div class="login-card">

    <!-- Logo justo encima del card, sin margen inferior grande -->
    <div class="text-center mb-2">
        <img src="{{ asset('images/logo2.png') }}" alt="Logo" style="max-width: 140px; height: auto;">
    </div>

    <div class="card shadow">
        <div class="card-body p-4">

            <!-- Título y subtítulo dentro del cuadro -->
            <div class="text-center mb-4">
                <h4 class="fw-bold mb-1">Administración Central</h4>
                <p class="text-muted small mb-0">Ingresa tus credenciales para continuar</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" autofocus required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Contraseña</label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-danger w-100 btn-lg mt-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                </button>
            </form>

        </div>
    </div>
</div>
</body>
</html>