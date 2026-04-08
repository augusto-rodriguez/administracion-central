@extends('layouts.app')
@section('title', 'Usuarios')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-person-lock me-2"></i>Usuarios del Sistema</h4>
    <a href="{{ route('usuarios.create') }}" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Nuevo Usuario
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Voluntario vinculado</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usuario)
                <tr>
                    <td class="fw-bold">{{ $usuario->nombre }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>
                        @if($usuario->rol === 'admin')
                            <span class="badge bg-danger">Administrador</span>
                        @elseif($usuario->rol === 'operador')
                            <span class="badge bg-primary">Operador</span>
                        @else
                            <span class="badge bg-secondary">Comandante</span>
                        @endif
                    </td>
                    <td>
                        @if($usuario->voluntario)
                            {{ $usuario->voluntario->nombre }}
                            <div class="text-muted small">{{ $usuario->voluntario->compania->nombre }}</div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($usuario->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-secondary">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No hay usuarios registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection