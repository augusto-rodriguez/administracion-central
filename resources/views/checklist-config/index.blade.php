@extends('layouts.app')

@section('title', 'Configuración Checklist')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-sliders me-2"></i>Configuración del Checklist</h4>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <form action="{{ route('checklist-config.update') }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Tiempo --}}
            <div class="card mb-3">
                <div class="card-header py-2"><strong><i class="bi bi-clock me-1"></i> Tiempo</strong></div>
                <div class="card-body">
                    @php $horasConfig = $configuraciones->firstWhere('clave', 'horas_para_completar'); @endphp
                    @if($horasConfig)
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ $horasConfig->descripcion }}</label>
                            <div class="input-group" style="max-width: 200px;">
                                <input type="number" name="config[horas_para_completar]" class="form-control"
                                       value="{{ old('config.horas_para_completar', $horasConfig->valor) }}" min="1" max="48">
                                <span class="input-group-text">horas</span>
                            </div>
                        </div>
                    @endif

                    @php $maxConfig = $configuraciones->firstWhere('clave', 'max_inspecciones_unidad_dia'); @endphp
                    @if($maxConfig)
                        <div class="mb-0">
                            <label class="form-label fw-bold">{{ $maxConfig->descripcion }}</label>
                            <div class="input-group" style="max-width: 200px;">
                                <input type="number" name="config[max_inspecciones_unidad_dia]" class="form-control"
                                       value="{{ old('config.max_inspecciones_unidad_dia', $maxConfig->valor) }}" min="1" max="10">
                                <span class="input-group-text">por día</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Notificaciones --}}
            <div class="card mb-3">
                <div class="card-header py-2"><strong><i class="bi bi-envelope me-1"></i> Notificaciones por correo</strong></div>
                <div class="card-body">
                    @php $emailsConfig = $configuraciones->firstWhere('clave', 'emails_notificacion_hallazgos'); @endphp
                    @if($emailsConfig)
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ $emailsConfig->descripcion }}</label>
                            <textarea name="config[emails_notificacion_hallazgos]" class="form-control" rows="3"
                                      placeholder="oficial1@email.com, capitan@email.com">{{ old('config.emails_notificacion_hallazgos', $emailsConfig->valor) }}</textarea>
                            <div class="form-text">
                                Ejemplo: <code>capitan@bomberos.cl, comandante@bomberos.cl</code>
                            </div>
                        </div>
                    @endif

                    @php $soloConfig = $configuraciones->firstWhere('clave', 'notificar_solo_criticos'); @endphp
                    @if($soloConfig)
                        <div class="mb-0">
                            <label class="form-label fw-bold">{{ $soloConfig->descripcion }}</label>
                            <select name="config[notificar_solo_criticos]" class="form-select" style="max-width: 200px;">
                                <option value="no" {{ $soloConfig->valor === 'no' ? 'selected' : '' }}>No — notificar todos</option>
                                <option value="si" {{ $soloConfig->valor === 'si' ? 'selected' : '' }}>Sí — solo críticos</option>
                            </select>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Gestión de hallazgos --}}
            <div class="card mb-3">
                <div class="card-header py-2"><strong><i class="bi bi-people me-1"></i> Gestión de hallazgos</strong></div>
                <div class="card-body">
                    @php $rolesConfig = $configuraciones->firstWhere('clave', 'roles_gestion_hallazgos'); @endphp
                    @if($rolesConfig)
                        @php
                            $rolesActivos = array_map('trim', explode(',', $rolesConfig->valor));
                            $todosRoles = [
                                'admin' => 'Administrador',
                                'comandante' => 'Comandante',
                                'capitan_cia' => 'Capitán de Compañía',
                                'operador' => 'Operador',
                                'cuartelero' => 'Cuartelero',
                            ];
                        @endphp
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ $rolesConfig->descripcion }}</label>
                            <div class="row g-2">
                                @foreach($todosRoles as $rolKey => $rolNombre)
                                    <div class="col-md-4 col-6">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input rol-checkbox"
                                                   value="{{ $rolKey }}" id="rol_{{ $rolKey }}"
                                                   {{ in_array($rolKey, $rolesActivos) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="rol_{{ $rolKey }}">{{ $rolNombre }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="config[roles_gestion_hallazgos]" id="rolesHidden" value="{{ $rolesConfig->valor }}">
                        </div>
                    @endif

                    @php $notifAsigConfig = $configuraciones->firstWhere('clave', 'notificar_asignacion_hallazgo'); @endphp
                    @if($notifAsigConfig)
                        <div class="mb-0">
                            <label class="form-label fw-bold">{{ $notifAsigConfig->descripcion }}</label>
                            <select name="config[notificar_asignacion_hallazgo]" class="form-select" style="max-width: 200px;">
                                <option value="no" {{ $notifAsigConfig->valor === 'no' ? 'selected' : '' }}>No</option>
                                <option value="si" {{ $notifAsigConfig->valor === 'si' ? 'selected' : '' }}>Sí — enviar correo</option>
                            </select>
                        </div>
                    @endif
                </div>
            </div>

            <button type="submit" class="btn btn-danger">
                <i class="bi bi-save me-1"></i>Guardar configuración
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.rol-checkbox');
    const hidden = document.getElementById('rolesHidden');

    function actualizarRoles() {
        const seleccionados = [];
        checkboxes.forEach(cb => {
            if (cb.checked) seleccionados.push(cb.value);
        });
        hidden.value = seleccionados.join(',');
    }

    checkboxes.forEach(cb => cb.addEventListener('change', actualizarRoles));
});
</script>
@endpush
@endsection