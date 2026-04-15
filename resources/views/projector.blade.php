<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>JAMC Laravel OBS Output</title>
    <style>
        html, body {
            height: 100vh; width: 100vw; margin: 0; padding: 0;
            overflow: hidden; background-color: #000000;
            transition: background-color 0.6s ease;
            font-family: Arial, Helvetica, sans-serif;
        }
        .center-container {
            height: 100%; width: 100%; display: flex;
            align-items: center; justify-content: center;
            padding: 50px; box-sizing: border-box;
        }
        h1 {
            color: white; text-align: center; text-transform: uppercase;
            white-space: pre-wrap; margin: 0; line-height: 1.1;
            letter-spacing: 1px; font-weight: 900; 
            text-shadow: 
                -6px -6px 0 #000, 6px -6px 0 #000, -6px 6px 0 #000, 6px 3px 0 #000,
                -4px 0px 0 #000, 4px 0px 0 #000, 0px -4px 0 #000, 0px 4px 0 #000,
                0px 8px 30px rgba(0,0,0,1);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            opacity: 0; transform: scale(0.96);
        }
        h1.visible { opacity: 1; transform: scale(1); }
        .status-text {
            position: absolute; bottom: 15px; right: 20px;
            color: rgba(255, 255, 255, 0.1); font-family: Arial, sans-serif;
            font-size: 10px; font-weight: bold; text-transform: uppercase;
            letter-spacing: 3px; pointer-events: none;
        }
    </style>
</head>
<body>
    <div id="status" class="status-text">LARAVEL LINKED</div>
    <div class="center-container">
        <h1 id="lyrics">WAITING...</h1>
    </div>

    <script>
        const lyricsEl = document.getElementById('lyrics');
        const statusEl = document.getElementById('status');

        function applyData(data) {
            try {
                const parsed = typeof data === 'string' ? JSON.parse(data) : data;
                const text = parsed.text || '';
                const size = parsed.fontSize || 60;
                const bg = parsed.background || 'none';

                if (bg === 'green') document.body.style.backgroundColor = '#00FF00';
                else if (bg === 'praise') document.body.style.backgroundColor = '#1e1b4b';
                else if (bg === 'worship') document.body.style.backgroundColor = '#09090b';
                else document.body.style.backgroundColor = '#000000';

                lyricsEl.style.fontSize = size + 'px';
                
                if (text.trim() !== "") {
                    lyricsEl.textContent = text;
                    lyricsEl.classList.add('visible');
                } else {
                    lyricsEl.classList.remove('visible');
                }
                statusEl.textContent = "SYNCED • " + new Date().toLocaleTimeString();
            } catch(e) { statusEl.textContent = 'ERROR'; }
        }

        // SSE KONEKSYON SA LARAVEL
        const es = new EventSource('/obs-stream');
        es.onmessage = (ev) => applyData(ev.data);
        es.onerror = () => { statusEl.textContent = 'RECONNECTING...'; }

        // INITIAL LOAD
        fetch('/api/obs/latest').then(r => r.json()).then(d => { 
            if(Object.keys(d || {}).length) applyData(d); 
        });
    </script>
</body>
</html>