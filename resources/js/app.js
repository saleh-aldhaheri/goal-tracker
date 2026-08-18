import './bootstrap';

function $(sel, ctx = document) { return ctx.querySelector(sel); }
function $$(sel, ctx = document) { return Array.from(ctx.querySelectorAll(sel)); }

/* ---------- Theme (light / system / dark) ---------- */
const THEME_KEY = 'gt-theme-mode';
let mode = 'system';
try { mode = localStorage.getItem(THEME_KEY) || 'system'; } catch (e) { /* ignore */ }
const mq = window.matchMedia('(prefers-color-scheme: dark)');

function applyTheme() {
  const dark = mode === 'dark' || (mode === 'system' && mq.matches);
  document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
  $$('#themeSeg button').forEach(b => b.classList.toggle('on', b.dataset.mode === mode));
  const mt = $('#mTheme');
  if (mt) mt.textContent = 'Theme: ' + (mode === 'system' ? 'Auto' : mode === 'dark' ? 'Dark' : 'Light');
}
function setMode(m) { mode = m; try { localStorage.setItem(THEME_KEY, m); } catch (e) { /* ignore */ } applyTheme(); }
function cycleTheme() { setMode(mode === 'light' ? 'dark' : mode === 'dark' ? 'system' : 'light'); }
mq.addEventListener('change', () => { if (mode === 'system') applyTheme(); });

/* ---------- Clock ---------- */
function tickClock() {
  const el = $('#clock');
  if (!el) return;
  const d = new Date();
  const p = n => String(n).padStart(2, '0');
  el.textContent = `${p(d.getHours())}:${p(d.getMinutes())}:${p(d.getSeconds())}`;
}

/* ---------- Rolling counters ---------- */
function formatValue(v, suffix, fmt) {
  if (fmt === 'time') {
    const h = Math.floor(v / 60), m = v % 60;
    return (h ? `${h}h ` : '') + `${m}m`;
  }
  return v + (suffix || '');
}
function runCounters() {
  $$('[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count, 10) || 0;
    const suffix = el.dataset.suffix || '';
    const fmt = el.dataset.fmt;
    const dur = 900, start = performance.now();
    function frame(now) {
      const t = Math.min(1, (now - start) / dur);
      const eased = 1 - Math.pow(1 - t, 3);
      el.textContent = formatValue(Math.round(target * eased), suffix, fmt);
      if (t < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  });
}

/* ---------- Ring gauge ---------- */
function runRing() {
  const ring = $('.ring-fg');
  if (!ring) return;
  const pct = parseInt(ring.dataset.pct, 10) || 0;
  requestAnimationFrame(() => {
    ring.style.strokeDashoffset = (326.7 * (1 - pct / 100)).toFixed(2);
  });
  const lit = Math.round(pct / 100 * 8);
  $$('.ring .ff').forEach((s, i) => s.classList.toggle('lit', i < lit));
}

/* ---------- Tape / bar / spark fill animations ---------- */
function animateTapes() {
  requestAnimationFrame(() => {
    $$('.tape .fill[data-w]').forEach(f => {
      f.style.width = '0%';
      requestAnimationFrame(() => { f.style.width = f.dataset.w + '%'; });
    });
  });
}
function animateBars() {
  requestAnimationFrame(() => {
    $$('.bar .col[data-h]').forEach(c => {
      requestAnimationFrame(() => { c.style.height = c.dataset.h + '%'; });
    });
    $$('.spark i[data-h]').forEach(b => {
      requestAnimationFrame(() => { b.style.height = b.dataset.h + '%'; });
    });
  });
}

/* ---------- Quick log modal ---------- */
function initQuickLog() {
  const backdrop = $('#quickLog');
  if (!backdrop) return;
  window.openLog = () => { backdrop.classList.add('open'); };
  window.closeLog = () => { backdrop.classList.remove('open'); };
  backdrop.addEventListener('click', e => { if (e.target === backdrop) closeLog(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLog(); });
  const form = $('#quickLogForm');
  if (form) {
    form.addEventListener('submit', () => {
      const id = $('#quickGoal').value;
      form.action = `/goals/${id}/activities`;
    });
  }
}

/* ---------- Confirm dialog (replaces native confirm()) ---------- */
function initConfirmDialog() {
  const modal = $('#confirmModal');
  if (!modal) return;
  const msgEl = $('#confirmMessage');
  const okBtn = $('#confirmOk');
  const cancelBtn = $('#confirmCancel');
  let resolveFn = null;

  const settle = ok => {
    modal.classList.remove('open');
    if (resolveFn) { const r = resolveFn; resolveFn = null; r(ok); }
  };

  okBtn.addEventListener('click', () => settle(true));
  cancelBtn.addEventListener('click', () => settle(false));
  modal.addEventListener('click', e => { if (e.target === modal) settle(false); });

  document.addEventListener('submit', e => {
    const form = e.target && e.target.closest ? e.target.closest('form') : null;
    const message = form && form.getAttribute('data-confirm');
    if (!message) return;
    e.preventDefault();
    msgEl.textContent = message;
    modal.classList.add('open');
    resolveFn = ok => { if (ok) form.submit(); };
  }, true);

  document.addEventListener('keydown', e => { if (e.key === 'Escape') settle(false); });
}

/* ---------- Toast ---------- */
let toastTimer;
window.showToast = function (msg) {
  let t = $('#toast');
  if (!t) { t = document.createElement('div'); t.id = 'toast'; t.className = 'toast'; document.body.appendChild(t); }
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 2400);
};

/* ---------- Sound (SFX + music + ambience) ---------- */
window.Sound = (() => {
  let ctx = null;
  const state = { sfx: true, sfxVol: 0.8, music: false, musicVol: 0.6, song: 'off', ambience: true, ambVol: 0.6 };
  try {
    const s = JSON.parse(localStorage.getItem('gt-sound') || '{}');
    if (s.sfx !== undefined) state.sfx = s.sfx;
    if (s.sfxVol !== undefined) state.sfxVol = s.sfxVol;
    if (s.music !== undefined) state.music = s.music;
    if (s.musicVol !== undefined) state.musicVol = s.musicVol;
    state.song = s.song || 'off';
    if (s.ambience !== undefined) state.ambience = s.ambience;
    if (s.ambVol !== undefined) state.ambVol = s.ambVol;
  } catch (e) { /* ignore */ }

  let musicNodes = [], musicTimer = null, musicGain = null;
  let ambBuilt = false, ambGain = null, rainG = null, windG = null, bugG = null, bugTimer = null;
  let ambTime = 'day', raining = false;

  function ac() { if (!ctx) ctx = new (window.AudioContext || window.webkitAudioContext)(); if (ctx.state === 'suspended') ctx.resume(); return ctx; }
  function save() { try { localStorage.setItem('gt-sound', JSON.stringify(state)); } catch (e) { /* ignore */ } }

  function tone(f, d, type, vol, when) {
    if (!state.sfx) return;
    try {
      const c = ac(), t = c.currentTime + (when || 0);
      const o = c.createOscillator(), g = c.createGain();
      o.type = type || 'sine'; o.frequency.value = f;
      const v = (vol || 0.12) * state.sfxVol;
      g.gain.setValueAtTime(0, t); g.gain.linearRampToValueAtTime(v, t + 0.02); g.gain.exponentialRampToValueAtTime(0.0001, t + d);
      o.connect(g).connect(c.destination); o.start(t); o.stop(t + d + 0.05);
    } catch (e) { /* ignore */ }
  }
  function chime() { tone(660, .25, 'sine', .12); tone(880, .35, 'sine', .10, .12); }
  function pop() { tone(440, .12, 'triangle', .14); tone(660, .2, 'triangle', .12, .06); }
  function logSfx() { tone(392, .2, 'sine', .12); tone(523, .25, 'sine', .10, .1); tone(659, .3, 'sine', .08, .2); }
  function completeSfx() { tone(523, .15, 'triangle', .12); tone(659, .15, 'triangle', .12, .1); tone(784, .3, 'triangle', .12, .2); tone(1047, .4, 'sine', .10, .3); }
  function playForStatus(text) {
    if (!text) return;
    const s = String(text).toLowerCase();
    if (s.includes('log') || s.includes('entry')) logSfx();
    else if (s.includes('complet') || s.includes('paus') || s.includes('resum') || s.includes('archiv')) completeSfx();
    else if (s.includes('creat') || s.includes('add')) pop();
    else chime();
  }

  const SONGS = {
    calm: { chords: [[261.6, 329.6, 392], [220, 261.6, 329.6], [174.6, 220, 261.6], [196, 246.9, 293.7]], tempo: 5200, wave: 'sine' },
    focus: { chords: [[293.7, 369.9, 440], [261.6, 329.6, 392], [329.6, 415.3, 493.9], [246.9, 311.1, 369.9]], tempo: 4400, wave: 'triangle', arp: 1 },
    rainy: { chords: [[220, 261.6, 311.1], [174.6, 220, 261.6], [196, 246.9, 293.7], [174.6, 220, 261.6]], tempo: 5600, wave: 'sine', pluck: 1 },
    night: { chords: [[130.8, 196, 246.9], [110, 164.8, 196], [123.5, 174.6, 220], [98, 146.8, 196]], tempo: 7200, wave: 'sine' },
    // Quiet, low-register, deliberately sparse presets for deep work —
    // slower tempo and a lower gain than the others so they sit further
    // back and don't compete with thinking.
    deepfocus: { chords: [[196, 246.9, 293.7], [174.6, 220, 261.6], [164.8, 207.7, 246.9], [174.6, 220, 261.6]], tempo: 8000, wave: 'sine', gain: 0.6 },
    lofi: { chords: [[220, 277.2, 329.6], [196, 246.9, 293.7], [174.6, 220, 261.6], [196, 246.9, 293.7]], tempo: 5000, wave: 'triangle', arp: 1, gain: 0.65 },
    minimal: { chords: [[130.8, 196], [116.5, 174.6], [130.8, 196], [98, 146.8]], tempo: 9000, wave: 'sine', gain: 0.55 },
  };
  function ensureMusicGain() { if (!musicGain) { musicGain = ac().createGain(); musicGain.gain.value = state.musicVol; musicGain.connect(ac().destination); } }
  function mNote(freq, vol, type, dur, delay, detune) {
    const c = ac(); const o = c.createOscillator(), g = c.createGain();
    o.type = type; o.frequency.value = freq; if (detune) o.detune.value = detune;
    const t = c.currentTime + (delay || 0);
    g.gain.setValueAtTime(0, t); g.gain.linearRampToValueAtTime(vol, t + 0.35); g.gain.setValueAtTime(vol, t + Math.max(0.6, dur - 0.9)); g.gain.linearRampToValueAtTime(0.0001, t + dur);
    o.connect(g).connect(musicGain); o.start(t); o.stop(t + dur + 0.1); musicNodes.push(o);
  }
  function startMusic(key) {
    stopMusic(); const song = SONGS[key]; if (!song) return; state.song = key; save(); ensureMusicGain();
    let i = 0;
    function step() {
      // Nodes scheduled by the previous step have already finished playing
      // by the time this fires (their stop time <= tempo ms out), so it's
      // safe to drop the old references here rather than let the array
      // grow unbounded for the lifetime of a play session.
      musicNodes = [];
      const ch = song.chords[i % song.chords.length]; i++;
      const dur = song.tempo / 1000;
      const gain = song.gain || 1;
      ch.forEach(f => { mNote(f, 0.055 * gain, song.wave, dur, 0); mNote(f * 2, 0.028 * gain, song.wave, dur, 0, 5); });
      mNote(ch[0] / 2, 0.07 * gain, 'sine', dur, 0);
      if (song.arp) { const mel = [ch[0] * 2, ch[1] * 2, ch[2] * 2, ch[1] * 2]; mel.forEach((m, k) => mNote(m, 0.04 * gain, 'triangle', 0.5, k * (dur / 4))); }
      if (song.pluck) { const mel = [ch[2] * 2, ch[1] * 2, ch[0] * 2]; mel.forEach((m, k) => mNote(m, 0.05 * gain, 'square', 0.4, k * 0.65)); }
      musicTimer = setTimeout(step, song.tempo);
    }
    step();
  }
  function stopMusic() { clearTimeout(musicTimer); musicNodes.forEach(n => { try { n.stop(); } catch (e) { /* ignore */ } }); musicNodes = []; state.song = 'off'; save(); }
  function setMusicVol(v) { state.musicVol = v; save(); if (musicGain) musicGain.gain.value = v; }

  function noiseBuffer(c) { const b = c.createBuffer(1, c.sampleRate * 2, c.sampleRate); const d = b.getChannelData(0); for (let i = 0; i < d.length; i++) d[i] = Math.random() * 2 - 1; return b; }
  function buildAmbience() {
    if (ambBuilt) return; ambBuilt = true;
    const c = ac();
    ambGain = c.createGain(); ambGain.gain.value = 0; ambGain.connect(c.destination);
    const rb = c.createBufferSource(); rb.buffer = noiseBuffer(c); rb.loop = true; const rf = c.createBiquadFilter(); rf.type = 'lowpass'; rf.frequency.value = 850; rainG = c.createGain(); rainG.gain.value = 0; rb.connect(rf).connect(rainG).connect(ambGain); rb.start();
    const wb = c.createBufferSource(); wb.buffer = noiseBuffer(c); wb.loop = true; const wf = c.createBiquadFilter(); wf.type = 'bandpass'; wf.frequency.value = 420; wf.Q.value = 0.6; windG = c.createGain(); windG.gain.value = 0; wb.connect(wf).connect(windG).connect(ambGain); wb.start();
    const lfo = c.createOscillator(); lfo.frequency.value = 0.08; const lg = c.createGain(); lg.gain.value = 140; lfo.connect(lg).connect(wf.frequency); lfo.start();
    bugG = c.createGain(); bugG.gain.value = 0; bugG.connect(ambGain);
  }
  function chirp() {
    if (!state.ambience || ambTime !== 'night') return;
    const c = ac(); const n = 3 + Math.floor(Math.random() * 3);
    for (let i = 0; i < n; i++) { const o = c.createOscillator(), g = c.createGain(); o.type = 'square'; o.frequency.value = 4200 + Math.random() * 400; const t = c.currentTime + i * 0.09; g.gain.setValueAtTime(0, t); g.gain.linearRampToValueAtTime(0.05, t + 0.01); g.gain.linearRampToValueAtTime(0, t + 0.05); o.connect(g).connect(bugG); o.start(t); o.stop(t + 0.06); }
    bugTimer = setTimeout(chirp, 650 + Math.random() * 1100);
  }
  function ambUpdate() {
    if (!state.ambience) { if (ambGain) ambGain.gain.setTargetAtTime(0, ac().currentTime, 0.3); clearTimeout(bugTimer); bugTimer = null; return; }
    buildAmbience();
    const c = ac();
    ambGain.gain.setTargetAtTime(state.ambVol, c.currentTime, 0.4);
    rainG.gain.setTargetAtTime(raining ? 0.16 : 0, c.currentTime, 0.5);
    windG.gain.setTargetAtTime(ambTime === 'day' ? 0.09 : 0, c.currentTime, 0.8);
    bugG.gain.setTargetAtTime(ambTime === 'night' ? 1 : 0, c.currentTime, 0.5);
    if (ambTime === 'night' && !bugTimer) chirp();
    if (ambTime !== 'night') { clearTimeout(bugTimer); bugTimer = null; }
  }
  function setAmbience(v) { state.ambience = v; save(); ambUpdate(); }
  function setAmbVol(v) { state.ambVol = v; save(); ambUpdate(); }
  function setRaining(v) { raining = v; ambUpdate(); }
  function setTime(t) { ambTime = t; ambUpdate(); }

  return { state, ac, save, chime, pop, log: logSfx, complete: completeSfx, playForStatus, SONGS, startMusic, stopMusic, setMusicVol, setAmbience, setAmbVol, setRaining, setTime };
})();

/* ---------- Sound control panel + status SFX ---------- */
function initSoundUI() {
  const fab = $('#sndFab');
  if (!fab) return;
  const panel = $('#sndPanel');
  const song = $('#sndSong');
  const musicVol = $('#sndMusicVol');
  const sfx = $('#sndSfx');
  const sfxVol = $('#sndSfxVol');
  const amb = $('#sndAmb');
  const ambVol = $('#sndAmbVol');
  const S = window.Sound;

  const reflect = () => {
    if (song) song.value = S.state.song;
    if (musicVol) musicVol.value = S.state.musicVol;
    if (sfx) sfx.checked = S.state.sfx;
    if (sfxVol) sfxVol.value = S.state.sfxVol;
    if (amb) amb.checked = S.state.ambience;
    if (ambVol) ambVol.value = S.state.ambVol;
    fab.classList.toggle('off', !S.state.sfx && S.state.song === 'off' && !S.state.ambience);
  };

  fab.addEventListener('click', () => panel.classList.toggle('open'));
  document.addEventListener('click', e => { if (!e.target.closest('.snd-wrap')) panel.classList.remove('open'); });
  if (song) song.addEventListener('change', () => { if (song.value === 'off') S.stopMusic(); else S.startMusic(song.value); reflect(); });
  if (musicVol) musicVol.addEventListener('input', () => S.setMusicVol(parseFloat(musicVol.value)));
  if (sfx) sfx.addEventListener('change', () => { S.state.sfx = sfx.checked; S.save(); reflect(); });
  if (sfxVol) sfxVol.addEventListener('input', () => { S.state.sfxVol = parseFloat(sfxVol.value); S.save(); });
  if (amb) amb.addEventListener('change', () => S.setAmbience(amb.checked));
  if (ambVol) ambVol.addEventListener('input', () => S.setAmbVol(parseFloat(ambVol.value)));
  reflect();
}

function initStatusSfx() {
  const st = document.body.getAttribute('data-status');
  if (st) window.Sound.playForStatus(st);
}

/* ---------- Ambient sky (stars / wind) ---------- */
function initAmbient() {
  const stars = $('#stars');
  if (stars) {
    for (let i = 0; i < 90; i++) {
      const s = document.createElement('div');
      s.className = 'star';
      s.style.left = Math.random() * 100 + '%';
      s.style.top = Math.random() * 60 + '%';
      s.style.animationDelay = (Math.random() * 3) + 's';
      stars.appendChild(s);
    }
  }
  const wind = $('#wind');
  if (wind) {
    for (let i = 0; i < 4; i++) {
      const s = document.createElement('div');
      s.className = 'streak';
      s.style.top = (10 + Math.random() * 70) + '%';
      s.style.animationDuration = (6 + Math.random() * 6) + 's';
      s.style.animationDelay = (Math.random() * 5) + 's';
      wind.appendChild(s);
    }
    for (let i = 0; i < 8; i++) {
      const p = document.createElement('div');
      p.className = 'pollen';
      p.style.left = Math.random() * 100 + '%';
      p.style.animationDuration = (9 + Math.random() * 8) + 's';
      p.style.animationDelay = (Math.random() * 8) + 's';
      p.style.opacity = 0.4 + Math.random() * 0.4;
      wind.appendChild(p);
    }
  }
}

/* ---------- Boot ---------- */
function boot() {
  applyTheme();
  $$('#themeSeg button').forEach(b => b.addEventListener('click', () => setMode(b.dataset.mode)));
  const mt = $('#mTheme');
  if (mt) mt.addEventListener('click', e => { e.preventDefault(); cycleTheme(); });

  tickClock();
  setInterval(tickClock, 1000);

  runCounters();
  runRing();
  animateTapes();
  animateBars();
  initQuickLog();
  initConfirmDialog();
  initSoundUI();
  initStatusSfx();
  initAmbient();

  ['pointerdown', 'keydown'].forEach(ev => document.addEventListener(ev, () => { try { window.Sound.ac(); } catch (e) { /* ignore */ } }, { passive: true }));
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot);
} else {
  boot();
}
