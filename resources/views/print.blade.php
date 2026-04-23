<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tagliando</title>
    <style>
        body {
            font-family: Arial;
        }

        .card {
            width: 400px;
            border: 2px solid #000;
            padding: 20px;
            text-align: center;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
        }

        .plate {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }

        .qr {
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="title">COMUNE DI CAPRI</div>
    <div>PERMESSO {{ strtoupper($permit->type) }}</div>

    <div class="plate">{{ $permit->plate }}</div>

    <div>{{ $permit->holder }}</div>
    <div>Scadenza: {{ $permit->valid_to }}</div>

    <div class="qr">
        {!! QrCode::size(150)->generate(url('/verify/' . $permit->qr_token)) !!}
    </div>

    <div>ID: CAP-{{ str_pad($permit->id, 4, '0', STR_PAD_LEFT) }}</div>
</div>

</body>
</html>