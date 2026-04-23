<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Scanner PM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Manifest -->
    <link rel="manifest" href="/manifest.json">

<style>
  body { margin:0; font-family: Arial; background:#111; color:#fff; }
  #reader { width: 100%; max-width: 420px; margin: 0 auto; }
  #result { margin: 0; }

  .screen {
    position: fixed; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    text-align: center; padding: 20px;
    transition: background 120ms ease;
  }
  .ok   { background:#2ecc71; }
  .ko   { background:#e74c3c; }

  .plate { font-size: 48px; font-weight: 700; letter-spacing: 2px; }
  .meta  { font-size: 18px; opacity:.95; }

  .hint { position: fixed; bottom: 12px; left:0; right:0; text-align:center; opacity:.6; font-size:12px; }
</style>
</head>
<body>

<h2>Scanner Polizia Municipale</h2>

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
  const el = document.getElementById('result');
  el.innerHTML = `
    <div class="screen ok">
      <div style="font-size:42px;">✅</div>
      <div class="plate">${data.plate}</div>
      <div class="meta">${data.holder}</div>
      <div class="meta">${data.type}</div>
      <div class="meta">Scadenza: ${data.valid_to}</div>
    </div>
  `;
}

function renderInvalid(data) {
  const el = document.getElementById('result');
  el.innerHTML = `
    <div class="screen ko">
      <div style="font-size:42px;">❌</div>
      <div class="meta">${data.reason || 'Non valido'}</div>
    </div>
  `;
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

// Avvio camera (preferisci posteriore)
const html5QrCode = new Html5Qrcode("reader");

html5QrCode.start(
  { facingMode: "environment" },
  { fps: 10, qrbox: 260 },
  onScanSuccess
).catch(() => {
  Html5Qrcode.getCameras().then(devices => {
    if (devices.length) {
      html5QrCode.start(devices[devices.length - 1].id, { fps: 10, qrbox: 260 }, onScanSuccess);
    }
  });
});
</script>

</body>
</html>