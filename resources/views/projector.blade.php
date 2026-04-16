<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>JAMC Live Output</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@700&display=swap');
        html, body { height: 100vh; width: 100vw; margin: 0; overflow: hidden; background-color: #000; transition: background-color 0.8s ease; font-family: 'Oswald', sans-serif; }
        .center-container { height: 100%; display: flex; align-items: center; justify-content: center; padding: 60px; box-sizing: border-box; }
        h1 { color: #ffffff; text-align: center; text-transform: uppercase; white-space: pre-wrap; font-weight: 700; opacity: 0; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); transform: scale(0.98); text-shadow: -3px -3px 0 #000, 3px -3px 0 #000, -3px 3px 0 #000, 3px 3px 0 #000, -4px 0px 0 #000, 4px 0px 0 #000, 0px -4px 0 #000, 0px 4px 0 #000, 0px 10px 30px rgba(0,0,0,0.8), 0px 20px 60px rgba(0,0,0,0.6); letter-spacing: 2px; line-height: 1.1; }
        h1.visible { opacity: 1; transform: scale(1); }
        .debug-status { position: fixed; bottom: 10px; left: 10px; color: #555; font-size: 10px; font-family: monospace; z-index: 50; }
    </style>
</head>
<body>
    <div id="status" class="debug-status">CONNECTING...</div>
    <div class="center-container"><h1 id="lyrics"></h1></div>

    <script>
        var lyricsEl = document.getElementById('lyrics');
        var statusEl = document.getElementById('status');

        function applyData(data) {
            if (!data) return;
            var bg = data.background || 'none';
            if (bg === 'green') document.body.style.backgroundColor = '#00FF00';
            else if (bg === 'praise') document.body.style.backgroundColor = '#1e1b4b';
            else if (bg === 'worship') document.body.style.backgroundColor = '#09090b';
            else document.body.style.backgroundColor = '#000000';

            lyricsEl.style.fontSize = (data.fontSize || 90) + 'px';
            
            if (data.text && data.text.trim() !== "") {
                lyricsEl.textContent = data.text;
                lyricsEl.classList.add('visible');
            } else {
                lyricsEl.classList.remove('visible');
            }
        }

        // --- INSTANT SSE CONNECTION (Walay WSS Errors!) ---
        function connectSSE() {
            // I-siguro nga naa sa api.php o web.php ang imong route
            var es = new EventSource('/obs-stream'); 
            
            es.onmessage = function(ev) {
                applyData(JSON.parse(ev.data));
                statusEl.textContent = "CONNECTED (SSE)";
            };

            es.onerror = function() {
                statusEl.textContent = "RECONNECTING...";
                es.close();
                setTimeout(connectSSE, 2000); // Auto reconnect kung maputol
            };
        }

        connectSSE();

        // Initial Load
        fetch('/obs-latest')
            .then(res => res.json())
            .then(data => { if (data && data.text) applyData(data); })
            .catch(err => console.log("Initial load standby"));
    </script>
</body>
</html>