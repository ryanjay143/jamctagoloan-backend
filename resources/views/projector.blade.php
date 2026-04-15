<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>JAMC Live Output</title>
    <style>
        html, body { 
            height: 100vh; width: 100vw; margin: 0; padding: 0; 
            overflow: hidden; background-color: #000000; 
            transition: background-color 0.6s ease;
            font-family: Arial, sans-serif; 
        }
        .center-container { 
            height: 100%; width: 100%; display: flex; 
            align-items: center; justify-content: center; 
            padding: 50px; box-sizing: border-box;
        }
        h1 { 
            color: white; text-align: center; text-transform: uppercase; 
            white-space: pre-wrap; font-weight: 900; opacity: 0; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            transform: scale(0.98);
            /* Baga nga Outline para sa OBS */
            text-shadow: 
                -4px -4px 0 #000, 4px -4px 0 #000, -4px 4px 0 #000, 4px 4px 0 #000, 
                0px 6px 20px rgba(0,0,0,1);
        }
        h1.visible { opacity: 1; transform: scale(1); }
    </style>
</head>
<body>
    <div class="center-container">
        <h1 id="lyrics">JAMC SYSTEM CONNECTED</h1>
    </div>

    <script>
        var lyricsEl = document.getElementById('lyrics');

        function applyData(parsed) {
            if (!parsed) return;

            // Background Control
            var bg = parsed.background || 'none';
            if (bg === 'green') document.body.style.backgroundColor = '#00FF00';
            else if (bg === 'praise') document.body.style.backgroundColor = '#1e1b4b';
            else if (bg === 'worship') document.body.style.backgroundColor = '#09090b';
            else document.body.style.backgroundColor = '#000000';

            // Text & Size
            lyricsEl.style.fontSize = (parsed.fontSize || 90) + 'px';
            
            if (parsed.text && parsed.text.trim() !== "") {
                if(lyricsEl.textContent !== parsed.text) {
                    lyricsEl.classList.remove('visible');
                    setTimeout(() => {
                        lyricsEl.textContent = parsed.text;
                        lyricsEl.classList.add('visible');
                    }, 300);
                }
            } else {
                lyricsEl.classList.remove('visible');
            }
        }

        // --- SSE (Server Sent Events) para Instant Sync ---
        function connectSSE() {
            var es = new EventSource('/obs-stream');
            
            es.onmessage = function(ev) {
                try {
                    applyData(JSON.parse(ev.data));
                } catch(e) {}
            };

            es.onerror = function() {
                es.close();
                setTimeout(connectSSE, 2000); // Reconnect if disconnected
            };
        }

        connectSSE();

        // Initial Load
        fetch('/obs-latest').then(r => r.json()).then(d => applyData(d));
    </script>
</body>
</html>