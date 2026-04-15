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
        .debug-status { position: fixed; bottom: 10px; left: 10px; color: #333; font-size: 10px; }
    </style>
</head>
<body>
    <div id="status" class="debug-status">Connecting...</div>
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

                // --- ECHO CONFIGURATION ---
                // Use the current page host and protocol so client connects to the correct origin.
                (function() {
                    const isSecure = location.protocol === 'https:';

                    window.Echo = new Echo({
                        broadcaster: 'reverb',
                        key: '{{ env("REVERB_APP_KEY") }}',
                        wsHost: jamctagoloan-backend-noqvsxwn.on-forge.com,
                        wsPort: isSecure ? 443 : 80,
                        wssPort: isSecure ? 443 : 80,
                        forceTLS: isSecure,
                        enabledTransports: isSecure ? ['wss'] : ['ws'],
                        disableStats: true,
                    });
                })();

        window.Echo.connector.pusher.connection.bind('connected', () => {
            statusEl.textContent = "CONNECTED TO SERVER";
        });

        window.Echo.connector.pusher.connection.bind('error', (err) => {
            statusEl.textContent = "CONNECTION ERROR: " + err.type;
        });

        window.Echo.channel('lyrics-channel')
            .listen('LyricsUpdated', (e) => {
                applyData(e.data);
            });
    </script>
</body>
</html>