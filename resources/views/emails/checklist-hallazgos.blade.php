<x-mail::message>
# Hallazgos detectados en inspección

Se completó una inspección con **{{ $totalHallazgos }} hallazgo(s)** que requieren atención.

---

**Unidad:** {{ $inspeccion->unidad->nombre }}
**Cuartelero:** {{ $inspeccion->cuartelero->nombre }}
**Fecha:** {{ $inspeccion->fecha->format('d/m/Y') }}
**Kilometraje:** {{ $inspeccion->kilometraje ?? '—' }}

---

@if($criticos->isNotEmpty())
## 🔴 Críticos ({{ $criticos->count() }})

<x-mail::table>
| Sección | Ítem |
|:--------|:-----|
@foreach($criticos as $h)
| {{ $h->item->seccion->nombre }} | **{{ $h->item->nombre }}** |
@endforeach
</x-mail::table>
@endif

@if($atencion->isNotEmpty())
## 🟡 Atención ({{ $atencion->count() }})

<x-mail::table>
| Sección | Ítem |
|:--------|:-----|
@foreach($atencion as $h)
| {{ $h->item->seccion->nombre }} | {{ $h->item->nombre }} |
@endforeach
</x-mail::table>
@endif

@if($info->isNotEmpty())
## 🔵 Informativos ({{ $info->count() }})

<x-mail::table>
| Sección | Ítem |
|:--------|:-----|
@foreach($info as $h)
| {{ $h->item->seccion->nombre }} | {{ $h->item->nombre }} |
@endforeach
</x-mail::table>
@endif

@if($inspeccion->observaciones)
---
**Observaciones del cuartelero:**
{{ $inspeccion->observaciones }}
@endif

<x-mail::button :url="route('hallazgos.index')">
Ver hallazgos en el sistema
</x-mail::button>

Este correo fue generado automáticamente por el sistema de gestión operativa.
</x-mail::message>