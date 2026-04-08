@extends('layouts.app')

@section('title', 'Boletines')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-megaphone me-2"></i>Boletines</h4>

    @if($limiteDiario)
        <button class="btn btn-danger" disabled
                data-bs-toggle="tooltip"
                title="Ya se generaron los 2 boletines de hoy">
            <i class="bi bi-plus-circle me-1"></i>Generar boletín
        </button>
    @else
        <a href="{{ route('boletines.create') }}" class="btn btn-danger">
            <i class="bi bi-plus-circle me-1"></i>Generar boletín
        </a>
    @endif
</div>

{{-- Alerta si ya hay dos boletines hoy --}}
@if($limiteDiario)
<div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <div>
        <strong>Ya se generaron los 2 boletines del día de hoy</strong> (AM y PM).
        Si hubo un error en alguno, puedes eliminarlo con el botón
        <i class="bi bi-trash"></i> y volver a generarlo.
    </div>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Turno</th>
                    <th>Cuarteleros</th>
                    <th>Maquinistas</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($boletines as $boletin)
                <tr>
                    <td class="fw-bold">
                        {{ \Carbon\Carbon::parse($boletin->fecha)->format('d/m/Y') }}
                    </td>
                    <td>
                        @if($boletin->tipo === 'am')
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-sun me-1"></i>AM
                            </span>
                        @else
                            <span class="badge bg-dark">
                                <i class="bi bi-moon-stars me-1"></i>PM
                            </span>
                        @endif
                    </td>
                    <td class="text-muted small">
                        {{ $boletin->cuarteleros->pluck('nombre')->implode(', ') ?: '—' }}
                    </td>
                    <td class="text-muted small">
                        {{ $boletin->maquinistas->map(fn($m) => $m->voluntario->nombre)->implode(', ') ?: '—' }}
                    </td>
                    <td class="text-end">
                        <a href="{{ route('boletines.show', $boletin) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>Ver
                        </a>
                        <form action="{{ route('boletines.destroy', $boletin) }}" method="POST"
                              class="d-inline"
                              onsubmit="return confirm('¿Eliminar este boletín? Podrás volver a generarlo.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        No hay boletines generados aún.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $boletines->links() }}
</div>

@push('scripts')
<script>
// Activar tooltips de Bootstrap
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
});
</script>
@endpush

@endsection