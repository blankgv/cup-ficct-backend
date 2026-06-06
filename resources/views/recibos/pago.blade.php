<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo {{ $numero }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; color: #1a1a1a; }
        .encabezado { text-align: center; border-bottom: 2px solid #1a1a1a; padding-bottom: 10px; margin-bottom: 20px; }
        .encabezado h1 { font-size: 18px; margin: 0; }
        .encabezado p { margin: 2px 0; font-size: 11px; }
        .titulo-recibo { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
        .numero { color: #555; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 4px; vertical-align: top; }
        td.label { width: 35%; color: #555; }
        td.valor { font-weight: bold; }
        .monto { margin-top: 20px; padding: 12px; background: #f2f2f2; font-size: 14px; }
        .monto .valor { font-size: 18px; }
        .estado { display: inline-block; padding: 3px 10px; background: #1a7a34; color: #fff; border-radius: 3px; font-size: 11px; }
        .pie { margin-top: 40px; font-size: 10px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="encabezado">
        <h1>Curso Preuniversitario - FICCT</h1>
        <p>Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones</p>
    </div>

    <div class="titulo-recibo">Recibo de pago</div>
    <div class="numero">N.° {{ $numero }} &mdash; emitido el {{ $emitido }}</div>

    <table>
        <tr>
            <td class="label">Postulante</td>
            <td class="valor">{{ $pago->postulante->nombres }} {{ $pago->postulante->apellidos }}</td>
        </tr>
        <tr>
            <td class="label">Documento</td>
            <td class="valor">{{ $pago->postulante_documento }}</td>
        </tr>
        <tr>
            <td class="label">Convocatoria</td>
            <td class="valor">{{ $pago->convocatoria->nombre }} ({{ $pago->convocatoria->gestion }})</td>
        </tr>
        <tr>
            <td class="label">Concepto</td>
            <td class="valor">{{ $pago->concepto }}</td>
        </tr>
        <tr>
            <td class="label">Método</td>
            <td class="valor">{{ $pago->metodo->value }}</td>
        </tr>
        <tr>
            <td class="label">Fecha de pago</td>
            <td class="valor">{{ $pago->fecha_pago?->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Confirmado el</td>
            <td class="valor">{{ $pago->confirmado_at?->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Estado</td>
            <td class="valor"><span class="estado">{{ $pago->estado->value }}</span></td>
        </tr>
    </table>

    <div class="monto">
        <table>
            <tr>
                <td class="label">Monto total</td>
                <td class="valor">Bs {{ number_format((float) $pago->monto, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="pie">Este recibo fue generado electrónicamente y es válido sin firma.</div>
</body>
</html>
