<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
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
            align-items: center; justify-content: center; padding: 50px; 
        }
        h1 { 
            color: white; text-align: center; text-transform: uppercase; 
            white-space: pre-wrap; font-weight: 900; opacity: 0; 
            transition: all 0.4s ease; transform: scale(0.95);
            text-shadow: -4px -4px 0 #000, 4px -4px 0 #000, -4px 4px 0 #000, 4px 4px 0 #000, 0px 10px 30px rgba(0,0,0,1);
        }
        h1.visible { opacity: 1; transform: scale(1); }
    </style>
</head>
<body>
    <div class="center-container"><h1 id="lyrics"></h1></div>

    <script>
        var lyricsEl = document.getElementById('lyrics');

        function applyData(data) {
            try {
                var parsed = (typeof data === 'string') ? JSON.parse(data) : data;
                if (!parsed) return;

                // Background
                var bg = parsed.background || 'none';
                if (bg === 'green') document.body.style.backgroundColor = '#00FF00';
                else if (bg === 'praise') document.body.style.backgroundColor = '#1e1b4b';
                else if (bg === 'worship') document.body.style.backgroundColor = '#09090b';
                else document.body.style.backgroundColor = '#000000';

                // Text & Size
                lyricsEl.style.fontSize = (parsed.fontSize || 60) + 'px';
                if (parsed.text && parsed.text.trim() !== "") {
                    lyricsEl.textContent = parsed.text;
                    lyricsEl.classList.add('visible');
                } else {
                    lyricsEl.classList.remove('visible');
                }
            } catch(e) { console.error("Parse error"); }
        }

        // Polling connection logic (avoids a permanent loading spinner in the browser)
        function startPolling() {
            // Initial fetch
            fetch('/obs-latest').then(function(r){ return r.json(); }).then(function(d){ applyData(d); });

            // Poll every 1s for updates
            setInterval(function(){
                fetch('/obs-latest').then(function(r){ return r.json(); }).then(function(d){ applyData(d); });
            }, 1000);
        }

        startPolling();

        // Initial Load
        fetch('/obs-latest').then(function(r){ return r.json(); }).then(function(d){ applyData(d); });
    </script>
</body>
</html>