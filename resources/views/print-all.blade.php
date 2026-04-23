<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Stampa Permessi</title>
    <style>
        body {
            font-family: Arial;
        }

        .grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .card {
            width: 300px;
            border: 2px solid #000;
            padding: 15px;
            text-align: center;
        }

        .plate {
            font-size: 28px;
            font-weight: bold;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="grid">
    @foreach($permits as $permit)
        <div class="card">
            <div><strong>COMUNE DI CAPRI</strong></div>
            <div>PERMESSO {{ strtoupper($permit->type) }}</div>

            <div class="plate">{{ $permit->plate }}</div>

            <div>{{ $permit->holder }}</div>
            <div>Scadenza: {{ $permit->valid_to }}</div>

            {!! QrCode::size(120)->generate(url('/verify/' . $permit->qr_token)) !!}

            <div>CAP-{{ str_pad($permit->id, 4, '0', STR_PAD_LEFT) }}</div>
        </div>
    @endforeach
</div>

</body>
</html>