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
                    <th>Rol de sistema</th>
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
                        @elseif($usuario->rol === 'comandante')
                            <span class="badge bg-secondary">Comandante</span>
                        @elseif($usuario->rol === 'capitan_cia')
                            <span class="badge bg-warning text-dark">Capitán Cía</span>
                        @elseif($usuario->rol === 'operador')
                            <span class="badge bg-primary">Operador</span>
                        @else
                            <span class="badge bg-light text-dark">{{ $usuario->rol }}</span>
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
                        @if($usuario->id !== auth()->id() && $usuario->rol !== 'admin')
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger ms-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEliminar"
                                    data-id="{{ $usuario->id }}"
                                    data-nombre="{{ $usuario->nombre }}"
                                    data-es-voluntario="{{ $usuario->voluntario_id ? '1' : '0' }}"
                                    data-nombre-voluntario="{{ $usuario->voluntario->nombre ?? '' }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        @endif
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

{{-- Modal confirmación eliminar --}}
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-trash me-2"></i>Eliminar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar al usuario <strong id="modalNombreUsuario"></strong>?</p>

                <div id="avisoVoluntario" class="alert alert-info gap-2 align-items-center d-none">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div>
                        Este usuario está vinculado al voluntario <strong id="modalNombreVoluntario"></strong>.
                        <strong>Solo se eliminará el acceso al sistema</strong>, el registro del voluntario
                        no será afectado en absoluto.
                    </div>
                </div>

                <p class="text-muted small mb-0">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="formEliminar" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Sí, eliminar usuario
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('modalEliminar').addEventListener('show.bs.modal', function (e) {
    const btn            = e.relatedTarget;
    const id             = btn.dataset.id;
    const nombre         = btn.dataset.nombre;
    const esVoluntario   = btn.dataset.esVoluntario === '1';
    const nombreVol      = btn.dataset.nombreVoluntario;

    document.getElementById('modalNombreUsuario').textContent = nombre;
    document.getElementById('formEliminar').action = `/usuarios/${id}`;

    const aviso = document.getElementById('avisoVoluntario');
    if (esVoluntario) {
        document.getElementById('modalNombreVoluntario').textContent = nombreVol;
        aviso.classList.remove('d-none');
        aviso.classList.add('d-flex');
    } else {
        aviso.classList.add('d-none');
        aviso.classList.remove('d-flex');
    }
});
</script>
@endpush