<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <div style="text-align: center; margin-bottom: 20px;">
        <img src="{{ config('app.url') }}/images/logo_SanPedroDeLaPaz.png"
             alt="CBSPP" style="width: 70px; height: 70px; object-fit: contain; margin-bottom: 8px;">
        <h2 style="color: #1a1a2e; margin-top: 5px;">Central de Alarmas CBSPP</h2>
    </div>

    <h3>Hallazgos detectados en inspección</h3>

    <p>Se completó una inspección con <strong>{{ $totalHallazgos }} hallazgo(s)</strong> que requieren atención.</p>

    <hr style="border: 1px solid #eee;">

    <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
        <tr>
            <td style="padding: 8px; font-weight: bold;">Unidad</td>
            <td style="padding: 8px;">{{ $inspeccion->unidad->nombre }}</td>
        </tr>
        <tr style="background: #f8f9fa;">
            <td style="padding: 8px; font-weight: bold;">Cuartelero</td>
            <td style="padding: 8px;">{{ $inspeccion->cuartelero->nombre }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; font-weight: bold;">Fecha</td>
            <td style="padding: 8px;">{{ $inspeccion->fecha->format('d/m/Y') }}</td>
        </tr>
        <tr style="background: #f8f9fa;">
            <td style="padding: 8px; font-weight: bold;">Kilometraje</td>
            <td style="padding: 8px;">{{ $inspeccion->kilometraje ?? '—' }}</td>
        </tr>
    </table>

    @if($criticos->isNotEmpty())
        <h4 style="color: #dc3545;">🔴 Críticos ({{ $criticos->count() }})</h4>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd; margin-bottom: 15px;">
            <tr style="background: #dc3545; color: white;">
                <th style="padding: 8px; text-align: left;">Sección</th>
                <th style="padding: 8px; text-align: left;">Ítem</th>
            </tr>
            @foreach($criticos as $h)
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $h->item->seccion->nombre }}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;">{{ $h->item->nombre }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if($atencion->isNotEmpty())
        <h4 style="color: #ffc107;">🟡 Atención ({{ $atencion->count() }})</h4>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd; margin-bottom: 15px;">
            <tr style="background: #ffc107;">
                <th style="padding: 8px; text-align: left;">Sección</th>
                <th style="padding: 8px; text-align: left;">Ítem</th>
            </tr>
            @foreach($atencion as $h)
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $h->item->seccion->nombre }}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $h->item->nombre }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if($info->isNotEmpty())
        <h4 style="color: #0dcaf0;">🔵 Informativos ({{ $info->count() }})</h4>
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd; margin-bottom: 15px;">
            <tr style="background: #0dcaf0;">
                <th style="padding: 8px; text-align: left;">Sección</th>
                <th style="padding: 8px; text-align: left;">Ítem</th>
            </tr>
            @foreach($info as $h)
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $h->item->seccion->nombre }}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #eee;">{{ $h->item->nombre }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if($inspeccion->observaciones)
        <h4>Observaciones del cuartelero:</h4>
        <p style="background: #f8f9fa; padding: 12px; border-radius: 5px;">{{ $inspeccion->observaciones }}</p>
    @endif

    <div style="text-align: center; margin: 25px 0;">
        <a href="{{ config('app.url') }}/hallazgos"
           style="background: #1a1a2e; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
            Ver hallazgos en el sistema
        </a>
    </div>

    <hr style="border: 1px solid #eee;">
    <p style="font-size: 12px; color: #999; text-align: center;">
        © {{ date('Y') }} Central de Alarmas — Cuerpo de Bomberos de San Pedro de la Paz
    </p>

</body>
</html>