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
            text-align: center;
            border-bottom: 2px solid #1a1a2e;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header h1 {
            font-size: 15px;
            color: #1a1a2e;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header .sub {
            font-size: 10px;
            color: #555;
            margin-top: 3px;
        }
        .header .meta {
            margin-top: 6px;
            font-size: 9px;
            color: #777;
        }

        /* ── Resumen total ── */
        .resumen-total {
            background: #1a1a2e;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 10px;
            margin-bottom: 14px;
            display: inline-block;
        }

        /* ── Card compañía ── */
        .compania-card {
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .compania-header {
            background: #1a1a2e;
            color: white;
            padding: 6px 10px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 3px 3px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .compania-header .badge-estado {
            font-size: 8px;
            padding: 2px 7px;
            border-radius: 10px;
            background: #27ae60;
            color: white;
        }
        .compania-header .badge-estado.sin-reporte {
            background: #e74c3c;
        }
        .compania-body {
            padding: 10px;
        }
        .sin-reporte-msg {
            color: #e74c3c;
            font-style: italic;
            font-size: 9px;
        }

        /* ── Grid dos columnas ── */
        .grid-2 {
            width: 100%;
            margin-bottom: 10px;
        }
        .grid-2 td {
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }

        /* ── Sección ── */
        .seccion-titulo {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #555;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        .seccion-valor {
            font-size: 10px;
            margin-bottom: 2px;
        }

        /* ── Lista voluntarios ── */
        .vol-lista {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .vol-lista li {
            padding: 2px 0;
            font-size: 9px;
            border-bottom: 1px dotted #eee;
        }
        .vol-lista li:last-child { border-bottom: none; }
        .hora-badge {
            display: inline-block;
            background: #f0f0f0;
            color: #555;
            padding: 0px 4px;
            border-radius: 3px;
            font-size: 8px;
            margin-left: 4px;
        }
        .hora-badge.tardio {
            background: #fff3cd;
            color: #856404;
        }

        /* ── Tabla unidades ── */
        .tabla-unidades {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 9px;
        }
        .tabla-unidades th {
            background: #f5f5f5;
            padding: 4px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
            font-size: 8px;
            text-transform: uppercase;
        }
        .tabla-unidades td {
            padding: 3px 6px;
            border: 1px solid #eee;
        }
        .tabla-unidades tr:nth-child(even) td { background: #fafafa; }

        .badge-unidad {
            display: inline-block;
            background: #2980b9;
            color: white;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-tipo {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
        }
        .badge-maquinista { background: #e74c3c; color: white; }
        .badge-cuartelero { background: #17a2b8; color: white; }
        .badge-sin        { background: #aaa;    color: white; }

        /* ── Observaciones ── */
        .obs-box {
            background: #f9f9f9;
            border-left: 3px solid #aaa;
            padding: 5px 8px;
            margin-top: 8px;
            font-size: 9px;
            color: #555;
            border-radius: 0 3px 3px 0;
        }

        /* ── Pie de página ── */
        .footer {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            text-align: center;
            font-size: 8px;
            color: #999;
        }
    </style>
</head>
<body>

    {{-- Cabecera --}}
    <div class="header">
        <h1>Guardia Nocturna</h1>
        <div class="sub">Cuerpo de Bomberos de San Pedro de la Paz</div>
        <div class="meta">
            Fecha: <strong>{{ $guardia->fecha->format('d/m/Y') }}</strong>
            &nbsp;|&nbsp;
            Cerrada a las: <strong>{{ $guardia->cerrado_at?->format('H:i') ?? '—' }}</strong>
            &nbsp;|&nbsp;
            Operador: <strong>{{ $guardia->cerradoPor->nombre ?? '—' }}</strong>
        </div>
    </div>

    {{-- Total voluntarios --}}
    @php
        $totalVols = $guardia->companias
            ->where('sin_reporte', false)
            ->sum(fn($c) => $c->voluntarios->count());
    @endphp
    <div class="resumen-total">
        Total voluntarios en guardia: <strong>{{ $totalVols }}</strong>
    </div>

    {{-- Compañías --}}
    @foreach($guardia->companias as $gnCompania)
    <div class="compania-card">
        <div class="compania-header">
            <span>{{ $gnCompania->compania->numero }}ª Compañía — {{ $gnCompania->compania->nombre }}</span>
            <span class="badge-estado {{ $gnCompania->sin_reporte ? 'sin-reporte' : '' }}">
                {{ $gnCompania->sin_reporte ? 'Sin reporte' : 'Reportado' }}
            </span>
        </div>

        <div class="compania-body">
            @if($gnCompania->sin_reporte)
                <p class="sin-reporte-msg">
                    Esta compañía no reportó guardia nocturna.
                    @if($gnCompania->observaciones)
                        — {{ $gnCompania->observaciones }}
                    @endif
                </p>
            @else
                {{-- Oficial y cuartelero --}}
                <table class="grid-2">
                    <tr>
                        <td>
                            <div class="seccion-titulo">Oficial a cargo</div>
                            <div class="seccion-valor">{{ $gnCompania->oficialACargo->nombre ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="seccion-titulo">Cuartelero de turno</div>
                            <div class="seccion-valor">{{ $gnCompania->cuartelero?->nombre ?? '—' }}</div>
                        </td>
                    </tr>
                </table>

                {{-- Voluntarios y unidades --}}
                <table class="grid-2">
                    <tr>
                        {{-- Voluntarios --}}
                        <td>
                            <div class="seccion-titulo">
                                Voluntarios en guardia ({{ $gnCompania->voluntarios->count() }})
                            </div>
                            @if($gnCompania->voluntarios->isNotEmpty())
                                <ul class="vol-lista">
                                    @foreach($gnCompania->voluntarios as $gnVol)
                                    <li>
                                        {{ $gnVol->voluntario->nombre ?? '—' }}
                                        @if($gnVol->hora_ingreso)
                                            <span class="hora-badge tardio">
                                                {{ \Carbon\Carbon::parse($gnVol->hora_ingreso)->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="hora-badge">01:00</span>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            @else
                                <span style="color:#aaa;font-size:9px">Sin voluntarios.</span>
                            @endif
                        </td>

                        {{-- Unidades --}}
                        <td>
                            <div class="seccion-titulo">
                                Unidades en servicio ({{ $gnCompania->unidades->count() }})
                            </div>
                            @if($gnCompania->unidades->isNotEmpty())
                                <table class="tabla-unidades">
                                    <thead>
                                        <tr>
                                            <th>Unidad</th>
                                            <th>Conductor</th>
                                            <th>Tipo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($gnCompania->unidades as $u)
                                        <tr>
                                            <td>
                                                <span class="badge-unidad">{{ $u->unidad->nombre }}</span>
                                            </td>
                                            <td>
                                                {{ $u->maquinista?->nombre ?? $u->cuartelero?->nombre ?? '—' }}
                                            </td>
                                            <td>
                                                @if($u->maquinista_id)
                                                    <span class="badge-tipo badge-maquinista">Maquinista</span>
                                                @elseif($u->cuartelero_id)
                                                    <span class="badge-tipo badge-cuartelero">Cuartelero</span>
                                                @else
                                                    <span class="badge-tipo badge-sin">Sin conductor</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <span style="color:#aaa;font-size:9px">Sin unidades.</span>
                            @endif
                        </td>
                    </tr>
                </table>

                {{-- Observaciones --}}
                @if($gnCompania->observaciones)
                <div class="obs-box">
                    <strong>Observaciones:</strong> {{ $gnCompania->observaciones }}
                </div>
                @endif
            @endif
        </div>
    </div>
    @endforeach

    {{-- Pie --}}
    <div class="footer">
        Documento generado el {{ now()->format('d/m/Y H:i') }}
        &nbsp;|&nbsp; Sistema Central de Alarmas — Cuerpo de Bomberos de San Pedro de la Paz
    </div>

</body>
</html>