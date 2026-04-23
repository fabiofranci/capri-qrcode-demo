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
    margin:0;
    font-family: Arial, sans-serif;
    background:#f4f6f8;
    color:#222;
  }

  .header {
    background:#1f3c88;
    color:#fff;
    padding:15px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
  }

  .header img {
    height:40px;
  }

  .header-title {
    font-size:18px;
    font-weight:bold;
  }

  #reader {
    width:100%;
    max-width:400px;
    margin:20px auto;
  }

  .screen {
    position: fixed;
    inset: 0;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    text-align:center;
    padding:20px;
  }

  .ok {
    background:#2ecc71;
    color:#fff;
  }

  .ko {
    background:#e74c3c;
    color:#fff;
  }

  .badge {
    font-size:64px;
    margin-bottom:10px;
  }

  .plate {
    font-size:48px;
    font-weight:bold;
    letter-spacing:2px;
  }

  .meta {
    font-size:18px;
    margin-top:5px;
  }

  button {
    margin-top:20px;
    padding:12px 20px;
    font-size:16px;
    border:none;
    border-radius:6px;
    background:#1f3c88;
    color:#fff;
    cursor:pointer;
  }

  .start-screen {
    position: fixed;
    inset: 0;
    background: #1f3c88;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 20px;
}

.start-btn {
    margin-top: 20px;
    padding: 14px 24px;
    font-size: 18px;
    border: none;
    border-radius: 8px;
    background: #fff;
    color: #1f3c88;
    cursor: pointer;
    font-weight: bold;
}
</style>
</head>
<body>
<div class="header">
  <img src="/logo.png" alt="Logo">
  <div class="header-title">Comune di Capri – Verifica Permessi</div>
</div>
<div style="text-align:center; margin-top:10px; color:#666;">
  Sistema di controllo accessi NCC e navette
</div>

<h2>Scanner Polizia Municipale</h2>

<div id="start-screen" class="start-screen">
    <img src="/logo.png" style="height:60px; margin-bottom:15px;">
    <h2>Comune di Capri</h2>
    <p style="opacity:0.7;">Controllo permessi NCC e navette</p>

    <button onclick="startScanner()" class="start-btn">
        📷 Tocca per inquadrare QR Code
    </button>
</div>

<div id="reader"></div>

<div id="result"></div>

<script src="https://unpkg.com/html5-qrcode"></script>

<script>
let locked = false;
let unlockTimer = null;

// Beep “locale” (niente file esterni)
function beep(freq = 880, duration = 120) {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const o = ctx.createOscillator();
    const g = ctx.createGain();
    o.connect(g); g.connect(ctx.destination);
    o.frequency.value = freq;
    o.start();
    setTimeout(() => { o.stop(); ctx.close(); }, duration);
  } catch (e) {}
}

function vibrate(ms = 80) {
  if (navigator.vibrate) navigator.vibrate(ms);
}

function lockScan(ms = 2000) {
  locked = true;
  clearTimeout(unlockTimer);
  unlockTimer = setTimeout(() => locked = false, ms);
}

function renderValid(data) {
  document.getElementById('result').innerHTML = `
    <div class="screen ok">
      <div class="badge">✔</div>
      <div class="plate">${data.plate}</div>
      <div class="meta">${data.holder}</div>
      <div class="meta">${data.type}</div>
      <div class="meta">Scadenza: ${data.valid_to}</div>

      <button onclick="resetScanner()">Nuova scansione</button>
    </div>
  `;
}

function renderInvalid(data) {
  document.getElementById('result').innerHTML = `
    <div class="screen ko">
      <div class="badge">✖</div>
      <div class="meta">${data.reason || 'Permesso non valido'}</div>

      <button onclick="resetScanner()">Nuova scansione</button>
    </div>
  `;
}

function resetScanner() {
    document.getElementById('result').innerHTML = '';
    locked = false;

    // opzionale: torna alla schermata iniziale
    document.getElementById('start-screen').style.display = 'flex';

    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            html5QrCode.clear();
        }).catch(() => {});
    }
}

function extractToken(decodedText) {
  if (!decodedText) return null;
  if (decodedText.includes('/')) {
    return decodedText.split('/').pop();
  }
  return decodedText;
}

function onScanSuccess(decodedText) {
  if (locked) return;

  const token = extractToken(decodedText);
  if (!token) return;

  lockScan(2000); // blocca scansioni multiple

  fetch(`https://capriqrcode.sw19.it/api/verify/${token}`)
    .then(r => r.json())
    .then(data => {
      if (data.status === 'valid') {
        beep(880, 120);
        vibrate(80);
        renderValid(data);
      } else {
        beep(220, 180);
        vibrate([80, 50, 80]);
        renderInvalid(data);
      }
    })
    .catch(() => {
      beep(220, 180);
      renderInvalid({ reason: 'Errore rete' });
    });
}

let html5QrCode = null;

function startScanner() {
    document.getElementById('start-screen').style.display = 'none';

    html5QrCode = new Html5Qrcode("reader");

    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: 260 },
        onScanSuccess
    ).catch(() => {
        Html5Qrcode.getCameras().then(devices => {
            if (devices.length) {
                html5QrCode.start(
                    devices[devices.length - 1].id,
                    { fps: 10, qrbox: 260 },
                    onScanSuccess
                );
            }
        });
    });
}
</script>

</body>
</html>