<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>JAMC Live Output</title>
    
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0-rc2/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <style>
        html, body { height: 100vh; width: 100vw; margin: 0; overflow: hidden; background-color: #000; }
        .center-container { height: 100%; display: flex; align-items: center; justify-content: center; padding: 50px; }
        h1 { 
            color: white; text-align: center; text-transform: uppercase; white-space: pre-wrap; 
            font-weight: 900; opacity: 0; transition: all 0.2s ease;
            text-shadow: 0 5px 20px rgba(0,0,0,1);
        }
        h1.visible { opacity: 1; transform: scale(1); }
        .debug-status { position: fixed; bottom: 10px; left: 10px; color: #555; font-size: 10px; font-family: monospace; }
    </style>
</head>
<body>
    <div id="status" class="debug-status">Initializing...</div>
    <div class="center-container"><h1 id="lyrics">WAITING...</h1></div>

    <script>
    var lyricsEl = document.getElementById('lyrics');
    var statusEl = document.getElementById('status');

    // Mokuha sa domain sa imong site automatic
    const BASE_URL = window.location.origin; 
    // Kon naa sa Forge, BASE_URL kay https://jamctagoloan-backend...
    
    function applyData(data) {
        if (!data) return;
        lyricsEl.style.fontSize = (data.fontSize || 90) + 'px';
        if (data.text && data.text.trim() !== "") {
            lyricsEl.textContent = data.text;
            lyricsEl.classList.add('visible');
        } else {
            lyricsEl.classList.remove('visible');
        }
    }

    // --- FETCH LATEST (FIXED URL) ---
    // Kon ang imong route kay /obs/latest, gamita ni:
    fetch(BASE_URL + '/obs/latest')
        .then(r => r.json())
        .then(d => applyData(d))
        .catch(e => console.log("Fetch failed", e));

    // --- ECHO CONFIGURATION (FIXED FORGE URL) ---
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: 'xadx2yzktngfhlyk82rb',
        wsHost: 'jamctagoloan-backend-noqvsxwn.on-forge.com', // Force domain
        wsPort: 443,
        wssPort: 443,
        forceTLS: true,
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
    });

    window.Echo.channel('lyrics-channel')
        .listen('LyricsUpdated', (e) => {
            applyData(e.data);
            statusEl.textContent = "CONNECTED (WebSocket)";
        });
</script>
</body>
</html>