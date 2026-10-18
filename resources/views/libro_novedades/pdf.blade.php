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

        /* ── Cabecera ── */
        .header {
            border-bottom: 2px solid #1a1a2e;
            padding-bottom: 10px;
            margin-bottom: 14px;
            width: 100%;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 0; }
        .header-logo-cell { width: 70px; }
        .header-logo { width: 65px; height: 65px; object-fit: contain; }
        .header-texto { text-align: center; }
        .header h1 { font-size: 15px; color: #1a1a2e; text-transform: uppercase; letter-spacing: 1px; }
        .header .sub { font-size: 10px; color: #555; margin-top: 3px; }
        .header .meta { margin-top: 6px; font-size: 9px; color: #777; }

        /* ── Badges de turno ── */
        .badge-turno-dia   { background: #f0a500; color: white; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-turno-noche { background: #1a1a2e; color: white; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }

        /* ── Cards / bloques ── */
        .bloque {
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        /* Bloques que pueden crecer (listas de 8-1): se permite cortarlos entre páginas */
        .bloque-largo { page-break-inside: auto; }
        .bloque-header {
            background: #1a1a2e;
            color: white;
            padding: 5px 10px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 3px 3px 0 0;
        }
        .bloque-body { padding: 10px; }

        /* ── Grid dos columnas ── */
        .grid-2 { width: 100%; margin-bottom: 8px; border-collapse: collapse; }
        .grid-2 td { width: 50%; vertical-align: top; padding-right: 12px; }

        /* ── Etiqueta / valor ── */
        .lbl { font-size: 8px; font-weight: bold; text-transform: uppercase; color: #777; letter-spacing: 0.5px; margin-bottom: 2px; }
        .val { font-size: 10px; margin-bottom: 6px; }

        /* ── Listas ── */
        .lista { list-style: none; padding: 0; margin: 0; }
        .lista li { padding: 2px 0; font-size: 9px; border-bottom: 1px dotted #eee; }
        .lista li:last-child { border-bottom: none; }

        /* ── Badges unidades fuera de servicio ── */
        .badge-peligro { display: inline-block; background: #e74c3c; color: white; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; margin: 2px; }

        /* ── Tablas ── */
        .tabla { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 9px; }
        .tabla th { background: #f5f5f5; padding: 4px 6px; text-align: left; font-weight: bold; border: 1px solid #ddd; font-size: 8px; text-transform: uppercase; }
        .tabla td { padding: 3px 6px; border: 1px solid #eee; }
        .tabla tr:nth-child(even) td { background: #fafafa; }
        .tabla tr { page-break-inside: avoid; }

        .badge-emergencia    { display: inline-block; background: #e74c3c; color: white; padding: 1px 5px; border-radius: 3px; font-size: 8px; }
        .badge-administrativa{ display: inline-block; background: #6c757d; color: white; padding: 1px 5px; border-radius: 3px; font-size: 8px; }
        .badge-maquinista    { display: inline-block; background: #2980b9; color: white; padding: 1px 5px; border-radius: 3px; font-size: 8px; }
        .badge-8-1           { display: inline-block; background: #f39c12; color: white; padding: 1px 5px; border-radius: 3px; font-size: 8px; font-weight: bold; }

        /* ── Texto libre ── */
        .texto-libre { font-size: 9px; white-space: pre-wrap; color: #333; line-height: 1.5; }
        .sin-dato    { font-size: 9px; color: #aaa; font-style: italic; }

        /* ── Pie ── */
        .footer { margin-top: 18px; border-top: 1px solid #ddd; padding-top: 8px; text-align: center; font-size: 8px; color: #999; }
    </style>
</head>
<body>

{{-- ── CABECERA ── --}}
<div class="header">
    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                <img class="header-logo"
                     src="{{ public_path('images/logo_SanPedroDeLaPaz.png') }}"
                     alt="Logo Bomberos">
            </td>
            <td class="header-texto">
                <h1>Libro de Novedades</h1>
                <div class="sub">Cuerpo de Bomberos de San Pedro de la Paz</div>
                <div class="meta">
                    Fecha: <strong>{{ $libro->fecha->format('d/m/Y') }}</strong>
                    &nbsp;|&nbsp;
                    Turno:
                    @if($libro->turno === 'dia')
                        <span class="badge-turno-dia">Día</span>
                    @else
                        <span class="badge-turno-noche">Noche</span>
                    @endif
                    &nbsp;|&nbsp;
                    Horario: <strong>{{ $libro->horario }}</strong>
                    &nbsp;|&nbsp;
                    Operador: <strong>{{ $libro->operador->nombre ?? '—' }}</strong>
                </div>
            </td>
            <td style="width: 70px;"></td>
        </tr>
    </table>
</div>

{{-- ── BLOQUE 1: IDENTIFICACIÓN ── --}}
<div class="bloque">
    <div class="bloque-header">1. Identificación del turno</div>
    <div class="bloque-body">
        <table class="grid-2">
            <tr>
                <td>
                    <div class="lbl">Operador en turno</div>
                    <div class="val">{{ $libro->operador->nombre ?? '—' }}</div>
                </td>
                <td>
                    <div class="lbl">Operador turno anterior</div>
                    <div class="val">{{ $libro->operador_turno_anterior ?? '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="lbl">Cerrado por</div>
                    <div class="val">{{ $libro->cerradoPor->nombre ?? '—' }}</div>
                </td>
                <td>
                    <div class="lbl">Hora de cierre</div>
                    <div class="val">{{ $libro->cerrado_at?->format('H:i') ?? '—' }}</div>
                </td>
            </tr>
        </table>
    </div>
</div>

{{-- ── BLOQUE 2: AL RECIBIR ── --}}
<div class="bloque bloque-largo">
    <div class="bloque-header">2. Al recibir el turno</div>
    <div class="bloque-body">
        <table class="grid-2">
            <tr>
                <td>
                    <div class="lbl">Maquinistas en servicio</div>
                    @php $mr = $libro->maquinistas_al_recibir ?? []; @endphp
                    @if(count($mr))
                        <ul class="lista">
                            @foreach($mr as $item)
                            <li>
                                {{ $item['nombre'] }}
                                @if(!empty($item['unidades']))
                                    — {{ collect($item['unidades'])->pluck('nombre')->implode(', ') }}
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <span class="sin-dato">—</span>
                    @endif
                </td>
                <td>
                    <div class="lbl">Cuarteleros en servicio</div>
                    @php $cr = $libro->cuarteleros_al_recibir ?? []; @endphp
                    @if(count($cr))
                        <ul class="lista">
                            @foreach($cr as $item)
                            <li>
                                {{ $item['nombre'] }}
                                @if(!empty($item['unidades']))
                                    — {{ collect($item['unidades'])->pluck('nombre')->implode(', ') }}
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <span class="sin-dato">—</span>
                    @endif
                </td>
            </tr>
        </table>

        <div class="lbl" style="margin-top:6px">Unidades fuera de servicio al recibir</div>
        @php $fsr = $libro->unidades_fuera_servicio_al_recibir ?? []; @endphp
        @if(count($fsr))
            @foreach($fsr as $u)
                <span class="badge-peligro">{{ $u['nombre'] }}{{ $u['patente'] ? ' ('.$u['patente'].')' : '' }}</span>
            @endforeach
        @else
            <span class="sin-dato">Ninguna unidad fuera de servicio.</span>
        @endif

        {{-- Oficiales fuera de servicio (8-1) al recibir --}}
        <div class="lbl" style="margin-top:10px">Oficiales fuera de servicio (8-1) al recibir</div>
        @php $ofr = $libro->oficiales_fuera_servicio_al_recibir; @endphp
        @if(is_null($ofr))
            <span class="sin-dato">Sin registro.</span>
        @elseif(count($ofr))
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Clave</th><th>Nombre</th><th>Compañía</th><th>Desde</th><th>Hasta</th><th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ofr as $o)
                    <tr>
                        <td>
                            @if(filled($o['clave'] ?? null))
                                <span class="badge-8-1">{{ $o['clave'] }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $o['nombre'] }}</td>
                        <td>{{ $o['compania'] ?? '—' }}</td>
                        <td>{{ \Carbon\Carbon::parse($o['fecha_inicio'])->format('d/m/Y') }}</td>
                        <td>{{ $o['fecha_fin'] ? \Carbon\Carbon::parse($o['fecha_fin'])->format('d/m/Y') : 'Indefinido' }}</td>
                        <td style="font-size:8px">{{ $o['motivo'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <span class="sin-dato">Ningún oficial fuera de servicio.</span>
        @endif
    </div>
</div>

{{-- ── BLOQUE 3: DURANTE EL TURNO ── --}}
<div class="bloque">
    <div class="bloque-header">3. Durante el turno</div>
    <div class="bloque-body">

        {{-- Salidas emergencia --}}
        <div class="lbl">Salidas de emergencia</div>
        @if($salidasEmergencia->count())
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Unidad</th><th>Clave</th><th>Dirección</th><th>Descripción</th><th>Salida</th><th>Llegada</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salidasEmergencia as $s)
                    <tr>
                        <td>{{ $s->unidad->nombre ?? '—' }}</td>
                        <td><span class="badge-emergencia">{{ $s->claveSalida->codigo ?? '—' }}</span></td>
                        <td>{{ $s->direccion }}</td>
                        <td style="font-size:8px">{{ $s->descripcion_emergencia ?? '—' }}</td>
                        <td>{{ \Carbon\Carbon::parse($s->salida_at)->format('H:i') }}</td>
                        <td>{{ $s->llegada_at ? \Carbon\Carbon::parse($s->llegada_at)->format('H:i') : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <span class="sin-dato">Sin salidas de emergencia en el turno.</span>
        @endif

        {{-- Salidas administrativas --}}
        <div class="lbl" style="margin-top:10px">Salidas administrativas</div>
        @if($salidasAdmin->count())
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Unidad</th><th>Clave</th><th>Dirección</th><th>Autoriza</th><th>Salida</th><th>Llegada</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salidasAdmin as $s)
                    <tr>
                        <td>{{ $s->unidad->nombre ?? '—' }}</td>
                        <td><span class="badge-administrativa">{{ $s->claveSalida->codigo ?? '—' }}</span></td>
                        <td>{{ $s->direccion }}</td>
                        <td>{{ $s->oficial->nombre ?? '—' }}</td>
                        <td>{{ \Carbon\Carbon::parse($s->salida_at)->format('H:i') }}</td>
                        <td>{{ $s->llegada_at ? \Carbon\Carbon::parse($s->llegada_at)->format('H:i') : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <span class="sin-dato">Sin salidas administrativas en el turno.</span>
        @endif

        {{-- Puestas en servicio --}}
        @php $puestas = $libro->puestas_en_servicio ?? []; @endphp
        <div class="lbl" style="margin-top:10px">Puestas en servicio durante el turno</div>
        @if(count($puestas))
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Tipo</th><th>Nombre</th><th>Unidad(es)</th><th>Entrada</th><th>Salida</th><th>Tiempo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($puestas as $p)
                    <tr>
                        <td>
                            <span class="badge-maquinista">
                                {{ $p['tipo'] === 'maquinista' ? 'Maquinista' : 'Cuartelero' }}
                            </span>
                        </td>
                        <td>{{ $p['nombre'] }}</td>
                        <td>{{ $p['unidades'] ?: '—' }}</td>
                        <td>{{ \Carbon\Carbon::parse($p['entrada_at'])->format('H:i') }}</td>
                        <td>{{ $p['salida_at'] ? \Carbon\Carbon::parse($p['salida_at'])->format('H:i') : 'En servicio' }}</td>
                        <td>
                            @if($p['total_minutos'])
                                @php $h = intdiv($p['total_minutos'], 60); $m = $p['total_minutos'] % 60; @endphp
                                {{ $h > 0 ? $h.'h ' : '' }}{{ $m }}min
                            @else —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <span class="sin-dato">Sin puestas en servicio registradas.</span>
        @endif
    </div>
</div>

{{-- ── BLOQUE 4: AL ENTREGAR ── --}}
<div class="bloque bloque-largo">
    <div class="bloque-header">4. Al entregar el turno</div>
    <div class="bloque-body">
        <table class="grid-2">
            <tr>
                <td>
                    <div class="lbl">Maquinistas en servicio</div>
                    @php $me = $libro->maquinistas_al_entregar ?? []; @endphp
                    @if(count($me))
                        <ul class="lista">
                            @foreach($me as $item)
                            <li>
                                {{ $item['nombre'] }}
                                @if(!empty($item['unidades']))
                                    — {{ collect($item['unidades'])->pluck('nombre')->implode(', ') }}
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <span class="sin-dato">—</span>
                    @endif
                </td>
                <td>
                    <div class="lbl">Cuarteleros en servicio</div>
                    @php $ce = $libro->cuarteleros_al_entregar ?? []; @endphp
                    @if(count($ce))
                        <ul class="lista">
                            @foreach($ce as $item)
                            <li>
                                {{ $item['nombre'] }}
                                @if(!empty($item['unidades']))
                                    — {{ collect($item['unidades'])->pluck('nombre')->implode(', ') }}
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <span class="sin-dato">—</span>
                    @endif
                </td>
            </tr>
        </table>

        <div class="lbl" style="margin-top:6px">Unidades fuera de servicio al entregar</div>
        @php $fse = $libro->unidades_fuera_servicio_al_entregar ?? []; @endphp
        @if(count($fse))
            @foreach($fse as $u)
                <span class="badge-peligro">{{ $u['nombre'] }}{{ $u['patente'] ? ' ('.$u['patente'].')' : '' }}</span>
            @endforeach
        @else
            <span class="sin-dato">Ninguna unidad fuera de servicio.</span>
        @endif

        {{-- Oficiales fuera de servicio (8-1) al entregar --}}
        <div class="lbl" style="margin-top:10px">Oficiales fuera de servicio (8-1) al entregar</div>
        @php $ofe = $libro->oficiales_fuera_servicio_al_entregar; @endphp
        @if(is_null($ofe))
            <span class="sin-dato">Sin registro.</span>
        @elseif(count($ofe))
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Clave</th><th>Nombre</th><th>Compañía</th><th>Desde</th><th>Hasta</th><th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ofe as $o)
                    <tr>
                        <td>
                            @if(filled($o['clave'] ?? null))
                                <span class="badge-8-1">{{ $o['clave'] }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $o['nombre'] }}</td>
                        <td>{{ $o['compania'] ?? '—' }}</td>
                        <td>{{ \Carbon\Carbon::parse($o['fecha_inicio'])->format('d/m/Y') }}</td>
                        <td>{{ $o['fecha_fin'] ? \Carbon\Carbon::parse($o['fecha_fin'])->format('d/m/Y') : 'Indefinido' }}</td>
                        <td style="font-size:8px">{{ $o['motivo'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <span class="sin-dato">Ningún oficial fuera de servicio.</span>
        @endif
    </div>
</div>

{{-- ── BLOQUE 5: NOVEDADES ── --}}
<div class="bloque">
    <div class="bloque-header">5. Novedades del turno</div>
    <div class="bloque-body">
        <div class="lbl">Cronológico de novedades</div>
        <p class="texto-libre" style="margin-bottom:10px">{{ $libro->novedades_cronologicas ?? '—' }}</p>

        <table class="grid-2">
            <tr>
                <td>
                    <div class="lbl">Observaciones en telecomunicaciones</div>
                    <p class="texto-libre">{{ $libro->observaciones_telecomunicaciones ?? '—' }}</p>
                </td>
                <td>
                    <div class="lbl">Novedades del VIPER</div>
                    <p class="texto-libre">{{ $libro->novedades_viper ?? '—' }}</p>
                </td>
            </tr>
        </table>
    </div>
</div>

{{-- ── PIE ── --}}
<div class="footer">
    Documento generado el {{ now()->format('d/m/Y H:i') }}
    &nbsp;|&nbsp; Sistema Central de Alarmas — Cuerpo de Bomberos de San Pedro de la Paz
</div>

</body>
</html>