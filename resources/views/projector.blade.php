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
      if (data.fontSize) el.style.fontSize = data.fontSize + 'px';
      if (data.fontFamily) el.style.fontFamily = data.fontFamily;
      if (data.isBold !== undefined) el.style.fontWeight = data.isBold ? '700' : '400';
      if (data.isUppercase !== undefined) el.style.textTransform = data.isUppercase ? 'uppercase' : 'none';
      if (data.hasOutline !== undefined) el.style.textShadow = data.hasOutline
        ? '-6px -6px 0 #000, 6px -6px 0 #000, -6px 6px 0 #000, 6px 6px 0 #000, 0px 10px 30px rgba(0,0,0,0.8)'
        : 'none';
      if (data.background !== undefined) applyBg(data.background);
    }

    let lastUpdatedAt = null;
    async function sync() {
      try {
        const res = await fetch('/api/obs-state');
        if (res.ok) {
          const data = await res.json();
          if (data.updatedAt !== lastUpdatedAt) {
            lastUpdatedAt = data.updatedAt;
            applyState(data);
          }
        }
      } catch {}
      setTimeout(sync, 150);
    }

    sync();
  </script>
</body>
</html>
