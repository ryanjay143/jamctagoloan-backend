<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Projector</title>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&display=swap" rel="stylesheet" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      width: 1920px; height: 1080px; overflow: hidden;
      background: #000;
      font-family: 'Oswald', sans-serif;
      display: flex; align-items: center; justify-content: center;
    }
    #lyrics {
      color: #fff;
      text-align: center;
      font-weight: 700;
      text-transform: uppercase;
      line-height: 1.1;
      padding: 20px;
      white-space: pre-wrap;
      text-shadow:
        -6px -6px 0 #000, 6px -6px 0 #000,
        -6px  6px 0 #000, 6px  6px 0 #000,
        -6px  0px 0 #000, 6px  0px 0 #000,
         0px -6px 0 #000, 0px  6px 0 #000,
        -5px -5px 0 #000, 5px -5px 0 #000,
        -5px  5px 0 #000, 5px  5px 0 #000,
         0px 10px 30px rgba(0,0,0,0.8);
    }
  </style>
</head>
<body>
  <div id="lyrics" style="font-size: 90px;"></div>
  <script>
    const useSse = @json(!app()->isLocal());
    const el = document.getElementById('lyrics');
    const body = document.body;

    function applyBg(bg) {
      body.style.backgroundImage = '';
      if (bg === 'praise') body.style.backgroundImage = 'linear-gradient(to bottom right, #4f46e5, #7c3aed, #4f46e5)';
      else if (bg === 'worship') body.style.backgroundImage = 'linear-gradient(to top, #000000, #171717, #000000)';
      else if (bg === 'green') body.style.backgroundColor = '#00FF00';
      else if (bg && bg.startsWith('#')) body.style.backgroundColor = bg;
      else body.style.backgroundColor = '#000';
    }

    function applyState(data) {
      if (data.text !== undefined) el.textContent = data.text;
      isOutputCleared = !data.text || !String(data.text).trim();
      if (data.fontSize) el.style.fontSize = data.fontSize + 'px';
      if (data.fontFamily) el.style.fontFamily = data.fontFamily;
      if (data.bold !== undefined) el.style.fontWeight = data.bold ? '700' : '400';
      if (data.allCaps !== undefined) el.style.textTransform = data.allCaps ? 'uppercase' : 'none';
      if (data.hasOutline !== undefined) el.style.textShadow = data.hasOutline
        ? '-6px -6px 0 #000, 6px -6px 0 #000, -6px 6px 0 #000, 6px 6px 0 #000, 0px 10px 30px rgba(0,0,0,0.8)'
        : 'none';
      if (data.background !== undefined) applyBg(data.background);
    }

    let stream = null;
    let pollTimer = null;
    let isPolling = false;
    let lastUpdatedAt = 0;
    let isOutputCleared = false;
    const ACTIVE_POLL_INTERVAL_MS = 2500;
    const CLEARED_POLL_INTERVAL_MS = 10000;

    async function pollState() {
      if (isPolling) return;
      isPolling = true;

      try {
        const response = await fetch('/api/obs-state', {
          headers: { Accept: 'application/json' },
          cache: 'no-store',
        });

        if (response.ok) {
          const data = await response.json();
          if (!data.updatedAt || data.updatedAt > lastUpdatedAt) {
            lastUpdatedAt = data.updatedAt || Date.now();
            applyState(data);
          }
        }
      } catch {}
      finally {
        isPolling = false;
      }

      pollTimer = setTimeout(
        pollState,
        isOutputCleared ? CLEARED_POLL_INTERVAL_MS : ACTIVE_POLL_INTERVAL_MS
      );
    }

    function connectStream() {
      if (stream) {
        stream.close();
      }

      stream = new EventSource('/api/obs-state/stream');

      const handleMessage = (event) => {
        try {
          applyState(JSON.parse(event.data));
        } catch {}
      };

      stream.addEventListener('obs-state', handleMessage);
      stream.onmessage = handleMessage;

      stream.onerror = () => {
        stream.close();
        setTimeout(connectStream, 1000);
      };
    }

    if (useSse) {
      connectStream();
    } else {
      pollState();
    }

    window.addEventListener('beforeunload', () => {
      stream?.close();
      if (pollTimer) clearTimeout(pollTimer);
    });
  </script>
</body>
</html>
