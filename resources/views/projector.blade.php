<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>JAMC Live Output</title>
    
    <!-- I-import ang Echo ug Pusher pinaagi sa CDN -->
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0-rc2/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <style>
        /* CSS design gikan sa una */
        html, body { height: 100vh; width: 100vw; margin: 0; overflow: hidden; background-color: #000; }
        .center-container { height: 100%; display: flex; align-items: center; justify-content: center; padding: 50px; }
        h1 { 
            color: white; text-align: center; text-transform: uppercase; white-space: pre-wrap; 
            font-weight: 900; opacity: 0; transition: all 0.2s ease; /* duration-0.2s para paspas */
            text-shadow: 0 5px 20px rgba(0,0,0,1);
        }
        h1.visible { opacity: 1; }
    </style>
</head>
<body>
    <div class="center-container"><h1 id="lyrics"></h1></div>

    <script>
        var lyricsEl = document.getElementById('lyrics');

        // Initial Logic
        function applyData(data) {
            if (!data) return;
            lyricsEl.style.fontSize = (data.fontSize || 60) + 'px';
            if (data.text && data.text.trim() !== "") {
                lyricsEl.textContent = data.text;
                lyricsEl.classList.add('visible');
            } else {
                lyricsEl.classList.remove('visible');
            }
        }

        // --- LARAVEL ECHO CONFIG ---
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config("broadcasting.connections.reverb.key") }}',
            wsHost: 'jamctagoloan-backend-noqvsxwn.on-forge.com',
            wsPort: 443,
            wssPort: 443,
            forceTLS: true,
            enabledTransports: ['ws', 'wss']
        });

        // Pag-paminaw sa Live Update
        window.Echo.channel('lyrics-channel')
            .listen('LyricsUpdated', (e) => {
                applyData(e.data);
            });
    </script>
</body>
</html>