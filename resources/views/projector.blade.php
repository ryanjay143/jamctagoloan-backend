<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>JAMC Live Output</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@700&display=swap');
        
        html, body { 
            height: 100vh; 
            width: 100vw; 
            margin: 0; 
            padding: 0;
            overflow: hidden; 
            background-color: #000; 
            transition: background-color 0.8s ease; 
            font-family: 'Oswald', sans-serif; 
        }

        .center-container { 
            height: 100%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 50px; 
            box-sizing: border-box; 
        }

        h1 { 
            color: #ffffff; 
            text-align: center; 
            text-transform: uppercase; 
            white-space: pre-wrap; 
            font-weight: 700; 
            margin: 0;
            opacity: 0; 
            /* Smooth scaling and fade transition */
            transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            transform: scale(0.95); 
            
            /* High-definition shadows para sa readability sa live video */
            text-shadow: 
                0px 0px 10px rgba(0,0,0,0.9),
                -2px -2px 0 #000, 2px -2px 0 #000, 
                -2px 2px 0 #000, 2px 2px 0 #000,
                0px 5px 20px rgba(0,0,0,1);
            
            letter-spacing: 1px; 
            line-height: 1.1; 
        }

        h1.visible { 
            opacity: 1; 
            transform: scale(1); 
        }

        .debug-status { 
            position: fixed; 
            bottom: 10px; 
            left: 10px; 
            color: rgba(255,255,255,0.2); 
            font-size: 9px; 
            font-family: monospace; 
            z-index: 50; 
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div id="status" class="debug-status">Initializing...</div>
    <div class="center-container">
        <h1 id="lyrics"></h1>
    </div>

    <!-- 1. Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

    <script>
        const lyricsEl = document.getElementById('lyrics');
        const statusEl = document.getElementById('status');
        let lastTimestamp = 0;

        function applyData(data) {
            if (!data) return;

            // Ayaw i-process kung karaan na ang data (base sa updatedAt timestamp)
            if (data.updatedAt && data.updatedAt < lastTimestamp) return;
            lastTimestamp = data.updatedAt || Date.now();

            // Background Color Logic
            const bg = data.background || 'none';
            const colors = {
                'green': '#00FF00',
                'praise': '#1e1b4b',
                'worship': '#09090b',
                'black': '#000000'
            };
            document.body.style.backgroundColor = colors[bg] || '#000000';

            // Font Size
            lyricsEl.style.fontSize = (data.fontSize || 90) + 'px';
            
            // Text Update with Animation
            if (data.text && data.text.trim() !== "") {
                if (lyricsEl.textContent !== data.text) {
                    lyricsEl.classList.remove('visible'); // Quick fade out
                    setTimeout(() => {
                        lyricsEl.textContent = data.text;
                        lyricsEl.classList.add('visible'); // Fade in with new text
                    }, 150);
                } else {
                    lyricsEl.classList.add('visible');
                }
            } else {
                lyricsEl.classList.remove('visible');
            }
        }

        // --- REVERB CONFIG ---
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: 'xadx2yzktngfhlyk82rb',
            wsHost: window.location.hostname,
            wsPort: 443,
            wssPort: 443,
            forceTLS: true,
            enabledTransports: ['ws', 'wss'],
            disableStats: true
        });

        // --- STABLE CONNECTION MONITORING ---
        function initConnectionStatus() {
            if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
                const pusher = window.Echo.connector.pusher;
                
                pusher.connection.bind('connected', () => {
                    statusEl.textContent = "LIVE: REVERB CONNECTED";
                    statusEl.style.color = "rgba(0, 255, 0, 0.5)";
                });

                pusher.connection.bind('disconnected', () => {
                    statusEl.textContent = "OFFLINE: RECONNECTING...";
                    statusEl.style.color = "rgba(255, 0, 0, 0.5)";
                });

                // Paminaw sa Channel
                window.Echo.channel('lyrics-channel')
                    .listen('.lyrics.updated', (data) => {
                        console.log("WebSocket Received:", data);
                        applyData(data);
                    });
            } else {
                setTimeout(initConnectionStatus, 500);
            }
        }

        initConnectionStatus();

        // --- INITIAL LOAD (Para dili blangko inig abli) ---
        function loadLatest() {
            fetch('/obs-latest')
                .then(res => res.json())
                .then(data => applyData(data))
                .catch(() => {
                    statusEl.textContent = "SYNC ERROR - RETRYING...";
                    setTimeout(loadLatest, 3000);
                });
        }

        loadLatest();

        // --- AUTO REFRESH PREVENTER ---
        // OBS Browser sources sometimes sleep. This keeps it awake.
        setInterval(() => {
            if (statusEl.textContent.includes('OFFLINE')) {
                loadLatest();
            }
        }, 10000);

    </script>
</body>
</html>