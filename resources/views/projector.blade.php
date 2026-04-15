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
            font-weight: 900; opacity: 0; transition: all 0.3s ease;
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
            if (!data) return;
            lyricsEl.style.fontSize = (data.fontSize || 90) + 'px';
            if (data.text && data.text.trim() !== "") {
                lyricsEl.textContent = data.text;
                lyricsEl.classList.add('visible');
            } else {
                lyricsEl.classList.remove('visible');
            }
        }

        // --- ECHO CONFIGURATION WITH FALLBACKS ---
        // Try WebSocket first, but fall back to polling if it fails.
        var _echoConnected = false;
        var _pollingStarted = false;

        function startPolling() {
            if (_pollingStarted) return;
            _pollingStarted = true;
            statusEl.textContent = "POLLING (fallback)";
            // initial
            fetch('/obs-latest').then(function(r){ return r.json(); }).then(function(d){ applyData(d); });
            setInterval(function(){
                fetch('/obs-latest').then(function(r){ return r.json(); }).then(function(d){ applyData(d); });
            }, 1000);
        }

        try {
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: 'xadx2yzktngfhlyk82rb',
                wsHost: 'jamctagoloan-backend-noqvsxwn.on-forge.com',
                wsPort: 443,
                wssPort: 443,
                forceTLS: true,
                enabledTransports: ['wss', 'ws'],
            });

            // Connected
            window.Echo.connector.pusher.connection.bind('connected', () => {
                _echoConnected = true;
                statusEl.textContent = "CONNECTED (websocket)";
            });

            // Error -> fallback to polling
            window.Echo.connector.pusher.connection.bind('error', (err) => {
                console.error("WSS Error:", err);
                if (!_echoConnected) startPolling();
                else statusEl.textContent = "CONNECTION ERROR";
            });

            // Safety timeout: if not connected quickly, start polling
            setTimeout(function(){ if (!_echoConnected) startPolling(); }, 2000);

            // Listen for events when Echo works
            window.Echo.channel('lyrics-channel').listen('LyricsUpdated', (e) => {
                applyData(e.data);
            });
        } catch (ex) {
            console.error('Echo init failed', ex);
            startPolling();
        }
    </script>
</body>
</html>