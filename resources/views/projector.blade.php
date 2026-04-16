<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>JAMC Live Output</title>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0-rc2/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <style>
        /* Google Fonts: Oswald para sa Bold/Pro look */
        @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@700&display=swap');

        html, body { 
            height: 100vh; width: 100vw; margin: 0; overflow: hidden; 
            background-color: #000; transition: background-color 0.8s ease;
            font-family: 'Oswald', sans-serif; 
        }
        
        .center-container { 
            height: 100%; display: flex; align-items: center; justify-content: center; 
            padding: 60px; box-sizing: border-box;
        }

        h1 { 
            color: #ffffff; text-align: center; text-transform: uppercase; 
            white-space: pre-wrap; font-weight: 700; opacity: 0; 
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); transform: scale(0.98);
            text-shadow: 
                -3px -3px 0 #000, 3px -3px 0 #000, -3px 3px 0 #000, 3px 3px 0 #000,
                -4px 0px 0 #000, 4px 0px 0 #000, 0px -4px 0 #000, 0px 4px 0 #000,
                0px 10px 30px rgba(0,0,0,0.8), 0px 20px 60px rgba(0,0,0,0.6);
            letter-spacing: 2px; line-height: 1.1;
        }

        h1.visible { opacity: 1; transform: scale(1); }
        .debug-status { position: fixed; bottom: 10px; left: 10px; color: #555; font-size: 10px; font-family: monospace; z-index: 50; }
    </style>
</head>
<body>
    <div id="status" class="debug-status">Initializing...</div>
    <div class="center-container"><h1 id="lyrics"></h1></div>

    <script>
        var lyricsEl = document.getElementById('lyrics');
        var statusEl = document.getElementById('status');

        // KINI NGA FUNCTION ANG MO-UPDATE SA SCREEN
        function applyData(data) {
            if (!data) return;

            // Background
            var bg = data.background || 'none';
            if (bg === 'green') document.body.style.backgroundColor = '#00FF00';
            else if (bg === 'praise') document.body.style.backgroundColor = '#1e1b4b';
            else if (bg === 'worship') document.body.style.backgroundColor = '#09090b';
            else document.body.style.backgroundColor = '#000000';

            // Font Size 
            lyricsEl.style.fontSize = (data.fontSize || 90) + 'px';
            
            // Text Animation
            if (data.text && data.text.trim() !== "") {
                lyricsEl.textContent = data.text;
                lyricsEl.classList.add('visible');
            } else {
                lyricsEl.classList.remove('visible');
            }
        }

        // --- LARAVEL ECHO CONFIGURATION ---
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: 'xadx2yzktngfhlyk82rb',
            wsHost: 'jamctagoloan-backend-noqvsxwn.on-forge.com',
            wsPort: 443,
            wssPort: 443,
            forceTLS: true,
            enabledTransports: ['ws', 'wss'],
            disableStats: true,
        });

        // Connection Status check
        window.Echo.connector.pusher.connection.bind('connected', () => {
            statusEl.textContent = "CONNECTED (WSS)";
        });

        window.Echo.connector.pusher.connection.bind('error', (err) => {
            statusEl.textContent = "ERROR CONNECTING";
        });

        // --- WEBSOCKET LISTENER ---
        window.Echo.channel('lyrics-channel')
            .listen('.lyrics.updated', (e) => {
                console.log("Received via WebSockets:", e);
                // KINI ANG FIX: Gitawag nato ang applyData function
                applyData(e);
            });

        // --- INITIAL LOAD ---
        // Mokuha daan sa database inig abli sa OBS para dili blangko
        // (Siguroha nga sakto ang route. Kung naa sa api.php, gamita ang /api/obs/latest)
        fetch('/api/obs/latest')
            .then(res => res.json())
            .then(data => {
                if (data && data.text) applyData(data);
            })
            .catch(err => console.log("Initial load standby"));
    </script>
</body>
</html>