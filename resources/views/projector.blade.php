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

        function applyData(data) {
            try {
                var parsed = (typeof data === 'string') ? JSON.parse(data) : data;
                if (!parsed) return;

                // Background
                var bg = parsed.background || 'none';
                if (bg === 'green') document.body.style.backgroundColor = '#00FF00';
                else if (bg === 'praise') document.body.style.backgroundColor = '#1e1b4b';
                else if (bg === 'worship') document.body.style.backgroundColor = '#09090b';
                else document.body.style.backgroundColor = '#000000';

                // Text & Size
                lyricsEl.style.fontSize = (parsed.fontSize || 60) + 'px';
                if (parsed.text && parsed.text.trim() !== "") {
                    lyricsEl.textContent = parsed.text;
                    lyricsEl.classList.add('visible');
                } else {
                    lyricsEl.classList.remove('visible');
                }
            } catch(e) { console.error("Parse error", e); }
        }

        // --- FALLBACK: EVENT SOURCE (SSE) ---
        // Kung mag-error ang WebSockets, mao ni ang mo-salo para paspas gihapon
        var fallbackTriggered = false;
        function startSSEFallback() {
            if (fallbackTriggered) return;
            fallbackTriggered = true;
            statusEl.textContent = "CONNECTED (SSE Fallback)";
            
            // Note: I-adjust ang URL kung naa sa api.php imong route (e.g. '/api/obs-stream')
            var es = new EventSource('/obs-stream'); 
            es.onmessage = function(ev) { applyData(ev.data); };
            es.onerror = function() { statusEl.textContent = 'RECONNECTING...'; };
        }

        // --- DYNAMIC REVERB VARIABLES GIKAN SA .ENV ---
        const rHost = '{{ env("REVERB_HOST", "127.0.0.1") }}';
        const rPort = {{ env("REVERB_PORT", 8080) }};
        const rScheme = '{{ env("REVERB_SCHEME", "http") }}';
        const rKey = '{{ env("REVERB_APP_KEY", "xadx2yzktngfhlyk82rb") }}';

        try {
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: rKey,
                wsHost: rHost,
                wsPort: rPort,
                wssPort: rPort,
                forceTLS: (rScheme === 'https'),
                enabledTransports: ['ws', 'wss'],
                disableStats: true,
            });

            window.Echo.connector.pusher.connection.bind('connected', () => {
                statusEl.textContent = "CONNECTED (WebSocket)";
            });

            window.Echo.connector.pusher.connection.bind('error', (err) => {
                console.error("WSS Error:", err);
                statusEl.textContent = "SWITCHING TO SSE...";
                startSSEFallback();
            });

            window.Echo.channel('lyrics-channel')
                .listen('LyricsUpdated', (e) => {
                    applyData(e.data);
                });

            // Kon dili mo-connect sulod sa 3 segundos, i-trigger ang fallback
            setTimeout(() => {
                if (statusEl.textContent === "Initializing...") startSSEFallback();
            }, 3000);

        } catch (ex) {
            console.error('Echo init failed', ex);
            startSSEFallback();
        }

        // Initial Load (I-adjust ang URL kung '/api/obs/latest')
        fetch('/obs/latest').then(r => r.json()).then(d => { 
            if(Object.keys(d || {}).length) applyData(d); 
        }).catch(() => console.log("Initial load failed"));
    </script>
</body>
</html>