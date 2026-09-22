<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #1a1a2e;">Central de Alarmas CBSPP</h2>
    </div>

    <h3>Se te ha asignado un hallazgo</h3>

    <p><strong>{{ $asignadoPor->nombre }}</strong> te ha asignado un hallazgo para su gestión.</p>

    <hr style="border: 1px solid #eee;">

    @php
        $severidadColor = match($hallazgo->severidad) {
            'critico' => '#dc3545',
            'atencion' => '#ffc107',
            'info' => '#0dcaf0',
            default => '#6c757d',
        };
    @endphp

    <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
        <tr>
            <td style="padding: 8px; font-weight: bold; width: 40%;">Severidad</td>
            <td style="padding: 8px;">
                <span style="background: {{ $severidadColor }}; color: white; padding: 3px 10px; border-radius: 4px; font-size: 12px;">
                    {{ strtoupper($hallazgo->severidad) }}
                </span>
            </td>
        </tr>
        <tr style="background: #f8f9fa;">
            <td style="padding: 8px; font-weight: bold;">Ítem</td>
            <td style="padding: 8px;">{{ $hallazgo->item->nombre }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; font-weight: bold;">Sección</td>
            <td style="padding: 8px;">{{ $hallazgo->item->seccion->nombre }}</td>
        </tr>
        <tr style="background: #f8f9fa;">
            <td style="padding: 8px; font-weight: bold;">Unidad</td>
            <td style="padding: 8px;">{{ $hallazgo->inspeccion->unidad->nombre }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; font-weight: bold;">Cuartelero</td>
            <td style="padding: 8px;">{{ $hallazgo->inspeccion->cuartelero->nombre }}</td>
        </tr>
        <tr style="background: #f8f9fa;">
            <td style="padding: 8px; font-weight: bold;">Fecha</td>
            <td style="padding: 8px;">{{ $hallazgo->inspeccion->fecha->format('d/m/Y') }}</td>
        </tr>
    </table>

    <p>Ingresa al sistema para revisar el hallazgo, cambiar su estado o agregar comentarios.</p>

    <div style="text-align: center; margin: 25px 0;">
        <a href="{{ config('app.url') }}/hallazgos/{{ $hallazgo->id }}"
           style="background: #1a1a2e; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
            Ver hallazgo en el sistema
        </a>
    </div>

    <hr style="border: 1px solid #eee;">
    <p style="font-size: 12px; color: #999; text-align: center;">
        © {{ date('Y') }} Central de Alarmas — Cuerpo de Bomberos de San Pedro de la Paz
    </p>

</body>
</html>