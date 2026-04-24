<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verifica Permesso</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial;
            text-align: center;
            padding: 40px;
            color: white;
        }
        .valid { background: #2ecc71; }
        .invalid { background: #e74c3c; }

        .box {
            background: rgba(0,0,0,0.2);
            padding: 20px;
            border-radius: 10px;
        }

        .plate {
            font-size: 40px;
            font-weight: bold;
        }
    </style>
</head>
<body class="{{ $status === 'valid' ? 'valid' : 'invalid' }}">

<div class="box">
    {!! QrCode::size(180)->generate(url("/verify/{$permit->qr_token}")) !!}
    
    @if($status === 'valid')
        <h1>✅ PERMESSO VALIDO</h1>

        <div class="plate">{{ $permit->plate }}</div>

        <p><strong>{{ $permit->holder }}</strong></p>
        <p>{{ $permit->type }}</p>
        <p>Scadenza: {{ $permit->valid_to }}</p>

    @else
        <h1>❌ NON VALIDO</h1>
        <p>{{ $reason ?? 'Errore' }}</p>

        @if(isset($permit))
            <p>Targa: {{ $permit->plate }}</p>
        @endif
    @endif
</div>

</body>
</html>