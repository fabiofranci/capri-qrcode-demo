<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
        }

        @page { size: A6 portrait; margin: 10mm; }

        .page {
            width: 100%;
        }

        .card {
            width: 100%;
            border: 2px solid #000;
            padding: 15px;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
            padding-bottom: 6px;
        }

        .subtitle {
            text-align: center;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .plate {
            text-align: center;
            font-size: 34px;
            font-weight: bold;
            margin: 12px 0;
            letter-spacing: 3px;
        }

        .info {
            font-size: 13px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
        }

        td {
            vertical-align: middle;
        }

        .dates {
            font-size: 12px;
            line-height: 1.6;
        }

        .qr {
            text-align: center;
            width: 140px;
        }

        .qr-box {
            position: relative;
            display: inline-block;
        }

        .qr-box img.qr-img {
            width: 120px;
        }

        .qr-box img.logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 32px;
            background: white;
            padding: 4px;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="page">
@php
    $color = match($permit->type) {
        'NCC' => '#1d4ed8',   // blu
        'navetta' => '#ea580c', // arancione
        default => '#111827',
    };
@endphp

<div class="card" style="border:2px solid {{ $color }};">

<table style="width: 100%; margin-bottom: 10px;">
    <tr>
        <td style="width: 50px;">
            <img src="{{ public_path('logo-capri.png') }}" style="width: 40px;">
        </td>

        <td style="text-align: center;">
            <div style="font-weight: bold; font-size: 16px;">
                COMUNE DI CAPRI
            </div>
            <div style="font-size: 11px;">
                Permesso transito / accesso area regolamentata
            </div>
        </td>

        <td style="width: 50px;"></td>
    </tr>
</table>

        <div class="plate">
            {{ strtoupper($permit->plate) }}
        </div>

        <div style="text-align:center; font-size:10px; margin-top:4px;">
            ID: CAP-{{ str_pad($permit->id, 4, '0', STR_PAD_LEFT) }}
        </div>

        <div class="info">
            <strong>Intestatario:</strong> {{ $permit->holder }}<br>
            <strong>Tipo:</strong> {{ $permit->type }}
        </div>

        <table>
            <tr>
                <td class="dates">
                    <strong>Dal:</strong> {{ $permit->valid_from?->format('d/m/Y') }}<br>
                    <strong>Al:</strong> {{ $permit->valid_to?->format('d/m/Y') }}
                </td>


                <td class="qr" style="text-align: center;">

                    <!-- QR -->
                <img src="data:image/png;base64,{{ $qr }}" style="width: 120px;">

                </td>  
            </tr>
        </table>

        <div class="footer">
            Verifica autenticità tramite QR Code
        </div>

    </div>
</div>

</body>
</html>