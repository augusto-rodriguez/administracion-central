<x-mail::message>
# Se te ha asignado un hallazgo

**{{ $asignadoPor->nombre }}** te ha asignado un hallazgo para su gestión.

---

@php
    $severidadEmoji = match($hallazgo->severidad) {
        'critico' => '🔴',
        'atencion' => '🟡',
        'info' => '🔵',
        default => '⚪',
    };
@endphp

**Severidad:** {{ $severidadEmoji }} {{ strtoupper($hallazgo->severidad) }}
**Ítem:** {{ $hallazgo->item->nombre }}
**Sección:** {{ $hallazgo->item->seccion->nombre }}
**Unidad:** {{ $hallazgo->inspeccion->unidad->nombre }}
**Cuartelero/Maquinista que reportó:** {{ $hallazgo->inspeccion->cuartelero->nombre }}
**Fecha inspección:** {{ $hallazgo->inspeccion->fecha->format('d/m/Y') }}
**Estado actual:** {{ $hallazgo->estado }}

@if($hallazgo->descripcion)
**Descripción:** {{ $hallazgo->descripcion }}
@endif

---

Ingresa al sistema para revisar el hallazgo, cambiar su estado o agregar comentarios.

<x-mail::button :url="route('hallazgos.show', $hallazgo)">
Ver hallazgo en el sistema
</x-mail::button>

Este correo fue generado automáticamente por el sistema de gestión operativa.
</x-mail::message>