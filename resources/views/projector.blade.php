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
            padding: 60px; 
            box-sizing: border-box; 
        }

        h1 { 
            color: #ffffff; 
            text-align: center; 
            text-transform: uppercase; 
            white-space: pre-wrap; 
            font-weight: 700; 
            opacity: 0; 
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); 
            transform: scale(0.98); 
            /* High-contrast shadow para mabasa bisag unsay background */
            text-shadow: -3px -3px 0 #000, 3px -3px 0 #000, -3px 3px 0 #000, 3px 3px 0 #000, 
                         -4px 0px 0 #000, 4px 0px 0 #000, 0px -4px 0 #000, 0px 4px 0 #000, 
                         0px 10px 30px rgba(0,0,0,0.8), 0px 20px 60px rgba(0,0,0,0.6); 
            letter-spacing: 2px; 
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
            color: rgba(255,255,255,0.3); 
            font-size: 10px; 
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

    <!-- 1. I-load ang gikinahanglan nga libraries (Pusher/Echo) -->
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

    <script>
        var lyricsEl = document.getElementById('lyrics');
        var statusEl = document.getElementById('status');

        // Function para i-apply ang kausaban sa screen
        function applyData(data) {
            if (!data) return;

            // Background Logic
            var bg = data.background || 'none';
            if (bg === 'green') document.body.style.backgroundColor = '#00FF00';
            else if (bg === 'praise') document.body.style.backgroundColor = '#1e1b4b';
            else if (bg === 'worship') document.body.style.backgroundColor = '#09090b';
            else document.body.style.backgroundColor = '#000000';

            // Font Size Logic
            lyricsEl.style.fontSize = (data.fontSize || 90) + 'px';
            
            // Text Update Logic
            if (data.text && data.text.trim() !== "") {
                lyricsEl.textContent = data.text;
                lyricsEl.classList.add('visible');
            } else {
                lyricsEl.classList.remove('visible');
                // Optional: Limpyohan ang text human sa transition out
                setTimeout(() => { 
                    if(!lyricsEl.classList.contains('visible')) lyricsEl.textContent = ""; 
                }, 300);
            }
        }

        // --- REVERB (WEBSOCKET) SETUP ---
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: 'xadx2yzktngfhlyk82rb',
            wsHost: window.location.hostname,
            wsPort: 443, // Para sa Forge (SSL)
            wssPort: 443,
            forceTLS: true,
            enabledTransports: ['ws', 'wss'],
            disableStats: true
        });

        // Monitor ang Connection Status
        window.Echo.connector.pusher.connection.bind('connected', () => {
            statusEl.textContent = "CONNECTED (REVERB)";
        });

        window.Echo.connector.pusher.connection.bind('disconnected', () => {
            statusEl.textContent = "DISCONNECTED";
        });

        // PAMINAW SA CHANNEL (ZERO DELAY)
        window.Echo.channel('lyrics-channel')
            .listen('.lyrics.updated', (data) => {
                console.log("Live Update:", data);
                applyData(data);
            });

        // INITIAL LOAD (Inig abli sa OBS para dili blangko)
        function loadLatest() {
            fetch('/obs-latest')
                .then(res => res.json())
                .then(data => {
                    if (data) applyData(data);
                })
                .catch(err => {
                    statusEl.textContent = "RETRYING LOAD...";
                    setTimeout(loadLatest, 3000);
                });
        }

        loadLatest();
    </script>
</body>
</html>