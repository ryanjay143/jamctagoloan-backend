<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>JAMC Live Output</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@700&display=swap');
        html, body { height: 100vh; width: 100vw; margin: 0; overflow: hidden; background-color: #000; font-family: 'Oswald', sans-serif; }
        .center-container { height: 100%; display: flex; align-items: center; justify-content: center; padding: 60px; box-sizing: border-box; }
        
        h1 { 
            color: #fff; 
            text-align: center; 
            text-transform: uppercase; 
            font-weight: 700;
            transition: opacity 0.05s ease-in, transform 0.05s ease-in;
            margin: 0;
            line-height: 1.1;
            /* 🔥 GIDUGANG NGA OUTLINE (THICK BORDER) 🔥 */
            text-shadow: 
                -3px -3px 0 #000, 3px -3px 0 #000, 
                -3px 3px 0 #000, 3px 3px 0 #000,
                -4px 0px 0 #000, 4px 0px 0 #000,
                0px -4px 0 #000, 0px 4px 0 #000,
                0px 10px 30px rgba(0,0,0,0.8);
        }
        .visible { opacity: 1 !important; }
    </style>
</head>
<body>
    <div class="center-container">
        <h1 id="lyrics" style="opacity: 0;"></h1>
    </div>

    <script>
        var lyricsEl = document.getElementById('lyrics');

        function applyData(data) {
            if (!data) return;

            // Instant Background Change
            var bg = data.background;
            document.body.style.backgroundColor = (bg === 'green') ? '#00FF00' : (bg === 'praise' ? '#1e1b4b' : (bg === 'worship' ? '#09090b' : '#000000'));
            
            lyricsEl.style.fontSize = (data.fontSize || 90) + 'px';
            
            // Instant Text Change
            if (data.text && data.text.trim() !== '') {
                lyricsEl.textContent = data.text;
                lyricsEl.classList.add('visible');
            } else {
                lyricsEl.classList.remove('visible');
            }
        }

        // Paspas nga koneksyon
        function connect() {
            var es = new EventSource('/obs-stream?t=' + Date.now());
            es.onmessage = function (ev) { applyData(JSON.parse(ev.data)); };
            es.onerror = function () { 
                es.close(); 
                setTimeout(connect, 1000); // Reconnect if disconnected
            };
        }
        connect();
        
        // Initial load
        fetch('/obs-latest').then(r => r.json()).then(applyData);
    </script>
</body>
</html>