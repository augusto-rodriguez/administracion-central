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
                        <button type="button"
                                class="btn btn-sm btn-outline-primary btn-ver-boletin"
                                data-id="{{ $boletin->id }}"
                                data-url="{{ route('boletines.show', $boletin) }}">
                            <i class="bi bi-eye me-1"></i>Ver
                        </button>
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

{{-- Modal de lectura rápida desde el listado --}}
<div class="modal fade" id="modalLecturaRapida" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" style="margin-top: 2rem;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white py-2">
                <div>
                    <h6 class="modal-title mb-0">
                        <i class="bi bi-broadcast me-2"></i>Lectura del boletín
                    </h6>
                    <small class="opacity-75" id="modalLecturaSubtitle"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalLecturaBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-danger" role="status"></div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
});

document.querySelectorAll('.btn-ver-boletin').forEach(btn => {
    btn.addEventListener('click', function () {
        const url      = this.dataset.url;
        const modal    = new bootstrap.Modal(document.getElementById('modalLecturaRapida'));
        const body     = document.getElementById('modalLecturaBody');
        const subtitle = document.getElementById('modalLecturaSubtitle');

        body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-danger" role="status"></div></div>';
        subtitle.textContent = '';
        modal.show();

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                const parser = new DOMParser();
                const doc    = parser.parseFromString(html, 'text/html');

                const subEl = doc.querySelector('#modalLectura .modal-header small');
                if (subEl) subtitle.innerHTML = subEl.innerHTML;

                const bodyEl = doc.querySelector('#modalLectura .modal-body');
                if (bodyEl) body.innerHTML = bodyEl.innerHTML;
            })
            .catch(() => {
                body.innerHTML = '<p class="text-danger text-center py-4">Error al cargar el boletín.</p>';
            });
    });
});
</script>
@endpush

@endsection