<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #1a1a2e;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header h1 { font-size: 15px; color: #1a1a2e; text-transform: uppercase; letter-spacing: 1px; }
        .header .sub { font-size: 10px; color: #555; margin-top: 3px; }
        .header .meta { margin-top: 6px; font-size: 9px; color: #777; }

        .bloque {
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .bloque-header {
            background: #1a1a2e;
            color: white;
            padding: 5px 10px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 3px 3px 0 0;
        }
        .bloque-body { padding: 10px; }

        .grid-2 { width: 100%; margin-bottom: 8px; border-collapse: collapse; }
        .grid-2 td { width: 50%; vertical-align: top; padding-right: 12px; }
        .grid-4 { width: 100%; margin-bottom: 8px; border-collapse: collapse; }
        .grid-4 td { width: 25%; vertical-align: top; padding-right: 8px; }

        .lbl { font-size: 8px; font-weight: bold; text-transform: uppercase; color: #777; letter-spacing: 0.5px; margin-bottom: 2px; }
        .val { font-size: 10px; margin-bottom: 6px; }

        .tabla { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 9px; }
        .tabla th { background: #f5f5f5; padding: 4px 6px; text-align: left; font-weight: bold; border: 1px solid #ddd; font-size: 8px; text-transform: uppercase; }
        .tabla td { padding: 3px 6px; border: 1px solid #eee; }
        .tabla tr:nth-child(even) td { background: #fafafa; }

        .badge-bueno { display: inline-block; background: #2e8b57; color: white; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-regular { display: inline-block; background: #d4a843; color: white; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-malo { display: inline-block; background: #c8352e; color: white; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-na { display: inline-block; background: #6c757d; color: white; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-ok { display: inline-block; background: #2e8b57; color: white; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-vencido { display: inline-block; background: #c8352e; color: white; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }

        .badge-critico { display: inline-block; background: #c8352e; color: white; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-atencion { display: inline-block; background: #d4a843; color: white; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-info { display: inline-block; background: #0dcaf0; color: white; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }

        .critico-icon { color: #c8352e; font-weight: bold; }
        .texto-libre { font-size: 9px; white-space: pre-wrap; color: #333; line-height: 1.5; }
        .sin-dato { font-size: 9px; color: #aaa; font-style: italic; }

        .estado-row-malo { background-color: #fde8e8; }
        .estado-row-regular { background-color: #fef3cd; }

        .footer { margin-top: 18px; border-top: 1px solid #ddd; padding-top: 8px; text-align: center; font-size: 8px; color: #999; }

        .foto-grid { margin-top: 6px; }
        .foto-grid img { width: 80px; height: 80px; object-fit: cover; border: 1px solid #ddd; border-radius: 3px; margin: 2px; }
    </style>
</head>
<body>

{{-- ── CABECERA ── --}}
<div class="header">
    <h1>Inspección de Material Mayor</h1>
    <div class="sub">Cuerpo de Bomberos de San Pedro de la Paz</div>
    <div class="meta">
        Fecha: <strong>{{ $inspeccion->fecha->format('d/m/Y') }}</strong>
        &nbsp;|&nbsp;
        Unidad: <strong>{{ $inspeccion->unidad->nombre }}</strong>
        @if($inspeccion->unidad->patente)
            ({{ $inspeccion->unidad->patente }})
        @endif
        &nbsp;|&nbsp;
        Compañía: <strong>{{ $inspeccion->unidad->compania->nombre ?? '—' }}</strong>
    </div>
</div>

{{-- ── BLOQUE 1: DATOS DE LA INSPECCIÓN ── --}}
<div class="bloque">
    <div class="bloque-header">1. Datos de la inspección</div>
    <div class="bloque-body">
        <table class="grid-2">
            <tr>
                <td>
                    <div class="lbl">Cuartelero</div>
                    <div class="val">{{ $inspeccion->cuartelero->nombre ?? '—' }}</div>
                </td>
                <td>
                    <div class="lbl">Plantilla utilizada</div>
                    <div class="val">{{ $inspeccion->plantilla->nombre }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="lbl">Estado</div>
                    <div class="val">{{ $inspeccion->estado === 'completado' ? 'Completado' : 'Borrador' }}</div>
                </td>
                <td>
                    <div class="lbl">Completado</div>
                    <div class="val">{{ $inspeccion->completado_at?->format('d/m/Y H:i') ?? '—' }}</div>
                </td>
            </tr>
        </table>
    </div>
</div>

{{-- ── BLOQUE 2: DATOS OPERATIVOS ── --}}
<div class="bloque">
    <div class="bloque-header">2. Datos operativos de la unidad</div>
    <div class="bloque-body">
        <table class="grid-4">
            <tr>
                <td>
                    <div class="lbl">Kilometraje</div>
                    <div class="val">{{ $inspeccion->kilometraje ?? '—' }}</div>
                </td>
                @if($inspeccion->plantilla->tipo_unidad === 'liviano')
                    <td>
                        <div class="lbl">Próxima mantención</div>
                        <div class="val">{{ $inspeccion->proxima_mantencion ?? '—' }}</div>
                    </td>
                    <td></td>
                    <td></td>
                @else
                    <td>
                        <div class="lbl">Hora Motor</div>
                        <div class="val">{{ $inspeccion->hora_motor ?? '—' }}</div>
                    </td>
                    <td>
                        <div class="lbl">Hora Bomba</div>
                        <div class="val">{{ $inspeccion->hora_bomba ?? '—' }}</div>
                    </td>
                    <td>
                        <div class="lbl">Últ. cambio aceite</div>
                        <div class="val">{{ $inspeccion->hora_ultimo_cambio_aceite ?? '—' }}</div>
                    </td>
                @endif
            </tr>
        </table>
    </div>
</div>

{{-- ── BLOQUES DE SECCIONES ── --}}
@foreach($inspeccion->plantilla->secciones as $sIndex => $seccion)
    <div class="bloque">
        <div class="bloque-header">{{ $sIndex + 3 }}. {{ $seccion->nombre }}</div>
        <div class="bloque-body">
            @if($seccion->descripcion)
                <div style="font-size: 8px; color: #666; margin-bottom: 6px; font-style: italic;">
                    {{ $seccion->descripcion }}
                </div>
            @endif

            <table class="tabla">
                <thead>
                    <tr>
                        <th style="width: 50%;">Ítem</th>
                        <th style="width: 30%;">Respuesta</th>
                        <!-- <th style="width: 20%;">Crítico</th> -->
                    </tr>
                </thead>
                <tbody>
                    @foreach($seccion->items as $item)
                        @php
                            $respuesta = $respuestasMap[$item->id] ?? null;
                            $valor = $respuesta->valor ?? null;

                            $badgeClass = match($valor) {
                                'bueno', 'full', '3/4', 'ok' => 'badge-bueno',
                                'regular', '1/2'             => 'badge-regular',
                                'malo', 'vencido', '1/4'     => 'badge-malo',
                                'no_aplica'                  => 'badge-na',
                                default                      => 'badge-na',
                            };

                            $valorDisplay = match($valor) {
                                'bueno'     => 'Bueno',
                                'regular'   => 'Regular',
                                'malo'      => 'Malo',
                                'full'      => 'FULL',
                                'no_aplica' => 'N/A',
                                'ok'        => 'OK',
                                'vencido'   => 'Vencido',
                                '1/4'       => '1/4',
                                '1/2'       => '1/2',
                                '3/4'       => '3/4',
                                default     => 'Sin respuesta',
                            };

                            $rowClass = match($valor) {
                                'malo', 'vencido'    => 'estado-row-malo',
                                'regular', '1/4'     => 'estado-row-regular',
                                default              => '',
                            };
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td>{{ $item->nombre }}</td>
                            <td><span class="{{ $badgeClass }}">{{ $valorDisplay }}</span></td>
                            <!-- <td>{{ $item->es_critico ? '⚠ Sí' : '—' }}</td> -->
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach

{{-- ── OBSERVACIONES ── --}}
@if($inspeccion->observaciones)
    <div class="bloque">
        <div class="bloque-header">Observaciones</div>
        <div class="bloque-body">
            <div class="texto-libre">{{ $inspeccion->observaciones }}</div>
        </div>
    </div>
@endif

{{-- ── HALLAZGOS DETECTADOS ── --}}
@if($inspeccion->hallazgos->isNotEmpty())
    <div class="bloque">
        <div class="bloque-header" style="background: #c8352e;">
            Hallazgos detectados ({{ $inspeccion->hallazgos->count() }})
        </div>
        <div class="bloque-body">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Severidad</th>
                        <th>Sección</th>
                        <th>Ítem</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inspeccion->hallazgos->sortByDesc('severidad') as $hallazgo)
                        @php
                            $sevBadge = match($hallazgo->severidad) {
                                'critico' => 'badge-critico',
                                'atencion' => 'badge-atencion',
                                'info' => 'badge-info',
                                default => 'badge-na',
                            };
                            $estadoLabel = match($hallazgo->estado) {
                                'abierto'               => 'Abierto',
                                'en_revision'           => 'En revisión',
                                'en_reparacion'         => 'En reparación',
                                'resuelto_verificado'   => 'Resuelto y Verificado',
                                default                 => $hallazgo->estado,
                            };
                        @endphp
                        <tr>
                            <td><span class="{{ $sevBadge }}">{{ strtoupper($hallazgo->severidad) }}</span></td>
                            <td>{{ $hallazgo->item->seccion->nombre }}</td>
                            <td><strong>{{ $hallazgo->item->nombre }}</strong></td>
                            <td>{{ $estadoLabel }}</td>
                        </tr>
                        @if($hallazgo->fotos->isNotEmpty())
                            <tr>
                                <td colspan="4">
                                    <div class="foto-grid">
                                        @foreach($hallazgo->fotos as $foto)
                                            <img src="{{ public_path('storage/' . $foto->ruta) }}" alt="Foto hallazgo">
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- ── PIE ── --}}
<div class="footer">
    Generado el {{ now()->format('d/m/Y H:i') }} — Central de Alarmas · Cuerpo de Bomberos de San Pedro de la Paz
</div>

</body>
</html>