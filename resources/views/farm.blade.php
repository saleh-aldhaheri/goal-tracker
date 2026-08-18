<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Goal Garden — Goal Tracker</title>
    <script>
        (function () {
            try {
                var m = localStorage.getItem('gt-theme-mode') || 'system';
                var dark = (m === 'dark') || (m !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
            } catch (e) { document.documentElement.setAttribute('data-theme', 'dark'); }
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Silkscreen:wght@400;700&family=Barlow+Condensed:wght@500;600;700&family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="farm-page">
    <div class="farm" id="farm" data-time="day" data-raining="0">
        <div class="scene">
            <div class="sky"></div>
            <div class="stars" id="stars"></div>
            <div class="sun"></div>
            <div class="moon"></div>
            <div class="clouds">
                <div class="cloud c1"></div><div class="cloud c2"></div><div class="cloud c3"></div>
            </div>
            <div class="hills"></div>
            <div class="hills h2"></div>
            <div class="ground"></div>
            <div class="bed"></div>

            <div class="housewrap">
                <div class="lawn"></div>
                <svg viewBox="0 0 96 74" shape-rendering="crispEdges">
                    <circle class="winglow" cx="28" cy="42" r="17" fill="#ffd98a"/>
                    <circle class="winglow" cx="68" cy="42" r="17" fill="#ffd98a"/>
                    <rect x="14" y="60" width="68" height="5" fill="#5f4027"/>
                    <rect x="16" y="36" width="64" height="26" fill="#f2e7d0"/>
                    <rect x="16" y="36" width="64" height="26" fill="none" stroke="#3a2f22" stroke-width="2"/>
                    <polygon points="6,38 48,6 90,38" fill="#c96a4a"/>
                    <polygon points="6,38 48,6 90,38" fill="none" stroke="#3a2f22" stroke-width="2"/>
                    <rect x="44" y="46" width="12" height="16" fill="#6b4a2f"/>
                    <rect x="52" y="55" width="2" height="3" fill="#e8c97a"/>
                    <rect class="window" x="24" y="42" width="12" height="10"/>
                    <rect class="window" x="62" y="42" width="12" height="10"/>
                    <rect x="24" y="42" width="12" height="10" fill="none" stroke="#3a2f22" stroke-width="2"/>
                    <rect x="62" y="42" width="12" height="10" fill="none" stroke="#3a2f22" stroke-width="2"/>
                    <line x1="30" y1="42" x2="30" y2="52" stroke="#3a2f22" stroke-width="2"/>
                    <line x1="24" y1="47" x2="36" y2="47" stroke="#3a2f22" stroke-width="2"/>
                    <line x1="68" y1="42" x2="68" y2="52" stroke="#3a2f22" stroke-width="2"/>
                    <line x1="62" y1="47" x2="74" y2="47" stroke="#3a2f22" stroke-width="2"/>
                    <rect x="64" y="12" width="9" height="16" fill="#8a6a4a"/>
                    <rect x="64" y="12" width="9" height="16" fill="none" stroke="#3a2f22" stroke-width="2"/>
                </svg>
                <div class="smoke"></div><div class="smoke s2"></div>
            </div>

            <div class="tufts" id="tufts"></div>
            <div class="rain" id="rain"></div>
            <div class="fireflies" id="fireflies"></div>
            <div class="butterflies" id="butterflies"></div>
            <div id="birdSlot"></div>
            <div class="plots" id="plots"></div>

            @if ($goals->isEmpty())
                <div class="farm-empty">
                    <div>
                        <div style="font-family:'Silkscreen',monospace;font-size:16px;margin-bottom:10px">AN EMPTY GARDEN</div>
                        <div style="color:var(--fk-dim);font-size:12px">Plant your first goal and a flower will grow here.</div>
                        <div style="margin-top:16px"><a class="btn primary" href="{{ route('goals.create') }}">+ New goal</a></div>
                    </div>
                </div>
            @endif
        </div>

        <header class="hud">
            <div class="brand">
                <div class="mark">🌱</div>
                <div>
                    <h1>{{ \Illuminate\Support\Str::upper(auth()->user()->name) }}'S GOAL GARDEN</h1>
                    <div class="sub"><a class="back" href="{{ route('dashboard') }}">← Dashboard</a> · each flower is a goal</div>
                </div>
            </div>
            <div class="controls">
                <div class="group" id="timeGroup">
                    <button data-time="auto" class="on">Auto</button>
                    <button data-time="day">Day</button>
                    <button data-time="night">Night</button>
                </div>
                <div class="group" id="weatherGroup">
                    <button data-w="auto" class="on">W: Auto</button>
                    <button data-w="clear">Clear</button>
                    <button data-w="rain">Rain</button>
                </div>
            </div>
        </header>
    </div>

    <div class="farm-tip" id="farmTip"></div>
    <div class="toast" id="toast"></div>
    <x-sound-control />

    <script>
        const GOALS = @json($goals);

        function leaf(id, x, y, dir) {
            return '<path d="M' + x + ' ' + y + ' C ' + (x + dir * 12) + ' ' + (y - 7) + ', ' + (x + dir * 20) + ' ' + (y - 1) + ', ' + (x + dir * 22) + ' ' + (y + 3) + ' C ' + (x + dir * 15) + ' ' + (y + 6) + ', ' + (x + dir * 7) + ' ' + (y + 2) + ', ' + x + ' ' + y + ' Z" fill="url(#sg' + id + ')"/>';
        }
        function flower(p, color, id) {
            const grow = p <= 0 ? 0 : p / 100;
            const headY = 90 - 8 - grow * 48;
            let s = '<defs>';
            s += '<linearGradient id="sg' + id + '" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#2f7d3a"/><stop offset="1" stop-color="#5cb86e"/></linearGradient>';
            s += '<radialGradient id="pg' + id + '" cx="50%" cy="30%" r="75%"><stop offset="0" stop-color="#fff" stop-opacity="0.4"/><stop offset="1" stop-color="' + color + '"/></radialGradient>';
            s += '<radialGradient id="cg' + id + '" cx="50%" cy="40%" r="60%"><stop offset="0" stop-color="#ffe08a"/><stop offset="1" stop-color="#e0a83c"/></radialGradient>';
            s += '</defs>';
            s += '<ellipse cx="40" cy="93" rx="20" ry="5" fill="rgba(0,0,0,.28)"/>';
            s += '<ellipse cx="40" cy="92" rx="13" ry="6" fill="#6b4a2f"/>';
            s += '<ellipse cx="40" cy="91" rx="9" ry="4" fill="#7a5538"/>';
            if (p > 0) {
                const sw = 2 + grow * 1.4;
                s += '<polygon points="40,' + (headY + 2) + ' ' + (40 + sw) + ',92 ' + (40 - sw) + ',92" fill="url(#sg' + id + ')"/>';
                if (grow >= 0.3) {
                    const ly = headY + (92 - headY) * 0.4;
                    s += leaf(id, 40, ly, -1);
                    s += leaf(id, 40, ly + 5, 1);
                }
                if (grow >= 0.25 && grow < 0.5) {
                    const br = 5 + grow * 8;
                    s += '<ellipse cx="40" cy="' + headY + '" rx="' + br + '" ry="' + (br * 1.5) + '" fill="url(#pg' + id + ')"/>';
                    s += '<ellipse cx="40" cy="' + (headY - 2) + '" rx="' + (br * 0.5) + '" ry="' + (br * 0.7) + '" fill="#fff" opacity="0.25"/>';
                }
                if (grow >= 0.5) {
                    const petal = 5 + (grow - 0.5) * 14;
                    const cr = 4 + (grow - 0.5) * 4;
                    for (let k = 0; k < 6; k++) {
                        const a = (k / 6) * Math.PI * 2 - Math.PI / 2;
                        const px = 40 + Math.cos(a) * petal * 0.72;
                        const py = headY + Math.sin(a) * petal * 0.95;
                        s += '<ellipse cx="' + px + '" cy="' + py + '" rx="' + (petal * 0.5) + '" ry="' + (petal * 0.72) + '" fill="url(#pg' + id + ')" transform="rotate(' + (a * 180 / Math.PI) + ' ' + px + ' ' + py + ')"/>';
                    }
                    s += '<circle cx="40" cy="' + headY + '" r="' + cr + '" fill="url(#cg' + id + ')"/>';
                    s += '<circle cx="38" cy="' + (headY - 1.5) + '" r="' + (cr * 0.3) + '" fill="#fff" opacity="0.4"/>';
                }
            }
            return '<svg viewBox="0 0 80 100" width="80" height="100">' + s + '</svg>';
        }
        function stateOf(g) { if (g.last_days !== null && g.last_days >= 5) return 'sad'; if (g.last_days !== null) return 'happy'; return 'seed'; }
        function lastText(g) {
            if (g.last_days === null) return 'never started';
            if (g.last_days === 0) return 'worked on today';
            if (g.last_days === 1) return 'last try yesterday';
            return 'last try ' + g.last_days + ' days ago';
        }
        function message(g) {
            const st = stateOf(g);
            if (st === 'sad') return "Hey, I've been waiting! It's been " + g.last_days + " days since your last try — even 10 minutes counts. Come back to me, I believe in you.";
            if (g.progress === 0) return "A fresh seed — the hardest part is starting, and you've already planted me. Let's begin, you can do it!";
            if (g.progress < 25) return "You've started it — that's the hardest part. You're at " + g.progress + "%, keep going!";
            if (g.progress < 75) return "You're already at " + g.progress + "% — keep watering me, you've got this!";
            if (g.progress < 100) return "So close! " + g.progress + "% — one more push and I'll fully bloom.";
            return "In full bloom — you did it! " + g.progress + "% complete.";
        }

        function layout(n) {
            const arr = [];
            const cols = Math.ceil(n / 2);
            for (let i = 0; i < n; i++) {
                const c = i % cols, r = Math.floor(i / cols);
                const left = 28 + c * (62 / Math.max(1, cols - 1)) + (Math.random() * 4 - 2);
                const bottom = 20 + r * 7 + (Math.random() * 3);
                const s = 0.85 + r * 0.15 + Math.random() * 0.15;
                arr.push({ left: Math.min(90, Math.max(26, Math.round(left))), bottom: Math.round(bottom), s: +s.toFixed(2), z: Math.round(bottom * 10) });
            }
            return arr;
        }

        function render() {
            const box = document.getElementById('plots');
            const pos = layout(GOALS.length);
            box.innerHTML = GOALS.map((g, i) => {
                const p = pos[i], st = stateOf(g);
                const cls = ['flowerbox', g.progress >= 75 ? 'bloom' : '', st === 'sad' ? 'sad' : '', st === 'happy' ? 'happy' : ''].filter(Boolean).join(' ');
                return '<div class="plot" data-i="' + i + '" style="left:' + p.left + '%;bottom:' + p.bottom + '%;transform:scale(' + p.s + ');z-index:' + p.z + '">'
                    + '<div class="' + cls + '" id="fb' + i + '" style="--glow:' + g.color + '66">' + flower(g.progress, g.color, i) + '</div>'
                    + '<div class="pname">' + g.name + '</div><div class="ppct">' + g.progress + '%</div></div>';
            }).join('');
            box.querySelectorAll('.plot').forEach(pl => {
                const i = parseInt(pl.dataset.i);
                pl.addEventListener('click', () => { window.location = '/goals/' + GOALS[i].id; });
                pl.addEventListener('mouseenter', e => showTip(i, e));
                pl.addEventListener('mousemove', e => moveTip(e));
                pl.addEventListener('mouseleave', hideTip);
            });
        }

        const tip = document.getElementById('farmTip');
        function showTip(i, e) {
            const g = GOALS[i], st = stateOf(g);
            const emoji = st === 'sad' ? '😢' : st === 'happy' ? '😊' : (g.progress === 0 ? '🌱' : '🌷');
            tip.innerHTML = '<div class="tt-name">' + emoji + ' ' + g.name + '</div>'
                + '<div class="bar"><i style="width:' + g.progress + '%;--c:' + g.color + '"></i></div>'
                + '<div class="tt-row"><span>Progress</span><span>' + g.progress + '%</span></div>'
                + '<div class="tt-row"><span>Last activity</span><span>' + lastText(g) + '</span></div>'
                + '<div class="tt-msg">' + message(g) + '</div>';
            tip.classList.add('show'); moveTip(e);
        }
        function moveTip(e) {
            const pad = 16, w = tip.offsetWidth || 240, h = tip.offsetHeight || 130;
            let x = e.clientX + pad, y = e.clientY + pad;
            if (x + w > window.innerWidth) x = e.clientX - w - pad;
            if (y + h > window.innerHeight) y = e.clientY - h - pad;
            tip.style.left = x + 'px'; tip.style.top = y + 'px';
        }
        function hideTip() { tip.classList.remove('show'); }

        function buildRain() { const r = document.getElementById('rain'); for (let i = 0; i < 60; i++) { const d = document.createElement('div'); d.className = 'drop'; d.style.left = Math.random() * 100 + '%'; d.style.animationDuration = (0.7 + Math.random() * 0.8) + 's'; d.style.animationDelay = (Math.random() * 1.5) + 's'; d.style.opacity = 0.4 + Math.random() * 0.6; r.appendChild(d); } }
        function buildFireflies() { const f = document.getElementById('fireflies'); for (let i = 0; i < 10; i++) { const d = document.createElement('div'); d.className = 'fly'; d.style.left = Math.random() * 100 + '%'; d.style.top = (28 + Math.random() * 52) + '%'; d.style.animationDuration = (4 + Math.random() * 5) + 's'; d.style.animationDelay = (Math.random() * 4) + 's'; f.appendChild(d); } }
        function buildStars() { const s = document.getElementById('stars'); for (let i = 0; i < 60; i++) { const st = document.createElement('div'); st.className = 'star'; st.style.left = Math.random() * 100 + '%'; st.style.top = Math.random() * 55 + '%'; st.style.animationDelay = (Math.random() * 3) + 's'; s.appendChild(st); } }
        function buildTufts() { const box = document.getElementById('tufts'); const shades = ['#4c9a54', '#5aa860', '#418b48', '#6db76a']; for (let i = 0; i < 85; i++) { const t = document.createElement('div'); t.className = 'tuft'; t.style.left = Math.random() * 100 + '%'; t.style.bottom = (Math.random() * 28) + '%'; const c = shades[Math.floor(Math.random() * shades.length)]; const h1 = 6 + Math.floor(Math.random() * 4), h2 = 4 + Math.floor(Math.random() * 4); t.innerHTML = '<svg viewBox="0 0 10 10" shape-rendering="crispEdges" style="animation-delay:' + (Math.random() * 5).toFixed(2) + 's"><rect x="1" y="' + (10 - h1) + '" width="2" height="' + h1 + '" fill="' + c + '"/><rect x="4" y="' + (10 - h2) + '" width="2" height="' + h2 + '" fill="' + c + '"/><rect x="7" y="' + (10 - h1) + '" width="2" height="' + h1 + '" fill="' + c + '"/></svg>'; box.appendChild(t); } }
        function buildButterflies() {
            if (!GOALS.length) return;
            const box = document.getElementById('butterflies');
            const pairs = [['#ffb3d9', '#ffd166'], ['#a9d4ff', '#c8f2c0'], ['#ffd166', '#ff9d9d']];
            const n = Math.min(3, Math.max(1, Math.ceil(GOALS.length / 2)));
            for (let i = 0; i < n; i++) {
                const b = document.createElement('div');
                b.className = 'bfly';
                b.style.left = (15 + Math.random() * 60) + '%';
                b.style.top = (35 + Math.random() * 40) + '%';
                b.style.animationDuration = (7 + Math.random() * 5) + 's';
                b.style.animationDelay = (Math.random() * 4) + 's';
                const [c1, c2] = pairs[i % pairs.length];
                b.style.setProperty('--bc1', c1);
                b.style.setProperty('--bc2', c2);
                box.appendChild(b);
            }
        }
        function flyBird() {
            if (document.hidden) return; // no point animating a background tab
            const slot = document.getElementById('birdSlot');
            const b = document.createElement('div');
            b.className = 'bird fly';
            b.style.top = (8 + Math.random() * 14) + '%';
            slot.appendChild(b);
            setTimeout(() => b.remove(), 14200);
        }
        function scheduleBirds() {
            flyBird();
            setTimeout(scheduleBirds, 45000 + Math.random() * 45000);
        }

        let timeMode = 'auto', weather = 'auto', raining = false;
        try { timeMode = localStorage.getItem('gt-farm-time') || 'auto'; } catch (e) {}
        try { weather = localStorage.getItem('gt-farm-weather') || 'auto'; } catch (e) {}

        function currentTime() { const h = new Date().getHours(); return (h >= 6 && h < 19) ? 'day' : 'night'; }
        function applyTime() {
            const t = timeMode === 'auto' ? currentTime() : timeMode;
            document.getElementById('farm').dataset.time = t;
            window.Sound.setTime(t);
        }
        function reflect() {
            document.querySelectorAll('#timeGroup button').forEach(x => x.classList.toggle('on', x.dataset.time === timeMode));
            document.querySelectorAll('#weatherGroup button').forEach(x => x.classList.toggle('on', x.dataset.w === weather));
        }
        document.getElementById('timeGroup').addEventListener('click', e => {
            const b = e.target.closest('button'); if (!b) return;
            timeMode = b.dataset.time; try { localStorage.setItem('gt-farm-time', timeMode); } catch (e) {}
            applyTime(); reflect(); window.Sound.chime();
        });
        function setRaining(v) {
            if (v === raining) return; raining = v;
            document.getElementById('farm').dataset.raining = raining ? '1' : '0';
            window.Sound.setRaining(raining);
        }
        function weatherTick() {
            if (weather !== 'auto') return;
            if (raining) { if (Math.random() < 0.45) setRaining(false); }
            else { if (Math.random() < 0.4) setRaining(true); }
        }
        document.getElementById('weatherGroup').addEventListener('click', e => {
            const b = e.target.closest('button'); if (!b) return;
            weather = b.dataset.w; try { localStorage.setItem('gt-farm-weather', weather); } catch (e) {}
            if (weather === 'rain') setRaining(true); else if (weather === 'clear') setRaining(false); else weatherTick();
            reflect(); window.Sound.chime();
        });

        buildStars(); buildRain(); buildFireflies(); buildTufts(); buildButterflies(); render(); reflect(); applyTime();
        scheduleBirds();
        setInterval(() => { if (!document.hidden) weatherTick(); }, 20000);
        setInterval(() => { if (!document.hidden) applyTime(); }, 60000);
        document.addEventListener('visibilitychange', () => {
            document.getElementById('farm').classList.toggle('tab-hidden', document.hidden);
        });
    </script>
</body>
</html>
