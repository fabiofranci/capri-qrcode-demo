<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Scanner PM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Manifest -->
    <link rel="manifest" href="/manifest.json">

    <style>
        body {
            font-family: Arial;
            text-align: center;
            background: #111;
            color: white;
        }

        #reader {
            width: 300px;
            margin: auto;
        }

        .result {
            margin-top: 20px;
            padding: 20px;
            border-radius: 10px;
        }

        .valid { background: #2ecc71; }
        .invalid { background: #e74c3c; }
    </style>
</head>
<body>

<h2>Scanner Polizia Municipale</h2>

<div id="reader"></div>

<div id="result"></div>

<script src="https://unpkg.com/html5-qrcode"></script>

<script>
function showResult(data) {
    const div = document.getElementById('result');

    if (data.status === 'valid') {
        div.className = 'result valid';
        div.innerHTML = `
            <h2>✅ VALIDO</h2>
            <p><strong>${data.plate}</strong></p>
            <p>${data.holder}</p>
            <p>${data.type}</p>
            <p>Scadenza: ${data.valid_to}</p>
        `;
    } else {
        div.className = 'result invalid';
        div.innerHTML = `
            <h2>❌ NON VALIDO</h2>
            <p>${data.reason}</p>
        `;
    }
}

function onScanSuccess(decodedText) {
    try {
        const url = new URL(decodedText);
        const token = url.pathname.split('/').pop();

        fetch(`/api/verify/${token}`)
            .then(res => res.json())
            .then(data => showResult(data));

    } catch (e) {
        console.error(e);
    }
}

const html5QrCode = new Html5Qrcode("reader");

html5QrCode.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 250 },
    onScanSuccess
).catch(() => {

    // fallback se environment non funziona
    Html5Qrcode.getCameras().then(devices => {
        if (devices.length) {
            html5QrCode.start(
                devices[devices.length - 1].id,
                { fps: 10, qrbox: 250 },
                onScanSuccess
            );
        }
    });

});

</script>

</body>
</html>