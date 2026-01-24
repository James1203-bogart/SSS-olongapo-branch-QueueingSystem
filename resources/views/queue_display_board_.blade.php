<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Queue Display Board v2</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
      :root{
        --bg:#e5e7eb; /* light background */
        --panel:#d1d5db; /* right side panel */
        --tile:#ffffff; /* counter tiles */
        --text:#111827; /* dark text */
        --muted:#4b5563; /* muted */
        --line:#6b7280; /* separator */
        --crawler:#4b5563; /* crawler bar */
        --accent:#fbbf24; /* yellow */
        --crawlerH:96px; /* fixed crawler height */
      }
      html, body{ height:100%; }
      body{ background:var(--bg); color:var(--text); margin:0; padding:0; }
      .crawler-animate { animation: crawler-scroll 18s linear infinite; white-space: nowrap; }
      @keyframes crawler-scroll { 0%{ transform: translateX(100%); } 100%{ transform: translateX(-100%);} }
      .ring-highlight { box-shadow: 0 0 0 4px var(--accent) inset; animation: ringPulse 900ms ease-in-out 3; }
      @keyframes ringPulse { 0%{ box-shadow: 0 0 0 0 var(--accent) inset; } 50%{ box-shadow: 0 0 0 8px var(--accent) inset; } 100%{ box-shadow: 0 0 0 0 var(--accent) inset; } }
      
    </style>
  </head>
  <body class="h-screen overflow-hidden">
    <div class="w-full h-full p-0">

      <!-- Main grid: 4 columns -->
      <div class="grid grid-cols-4 gap-3" style="height: calc(100vh - var(--crawlerH));">
        <!-- Left 3 cols: counters 3x3 -->
        <div class="col-span-3 grid grid-cols-3 gap-3" id="countersWrap">
          <div id="displayAllCounters" class="grid grid-cols-3 gap-3 col-span-3"></div>
        </div>
        <!-- Right 1 col: stacked info -->
        <div class="bg-[var(--panel)] rounded-lg p-0 flex flex-col">
          <div class="px-6 py-4">
            <div class="text-4xl font-bold">TIME</div>
            <div id="displayTime" class="text-5xl font-semibold">--:--:--\nAM</div>
          </div>
          <div class="border-t" style="border-color:var(--line)"></div>
          <div class="px-6 py-4">
            <div class="text-4xl font-bold">DATE</div>
            <div id="displayDate" class="text-3xl font-semibold">-- --, ----</div>
          </div>
          <div class="border-t" style="border-color:var(--line)"></div>
          <div class="px-6 py-4">
            <div class="text-4xl font-bold">OFFLINE CALLING</div>
            <div id="offlineList" class="text-xl mt-2">No offline calls</div>
          </div>
          <div class="border-t" style="border-color:var(--line)"></div>
          <div class="px-6 py-4 text-left">
            <div class="text-4xl font-bold">NOW SERVING</div>
            <div id="displayNowNumber" class="text-5xl font-bold mt-2">- - -</div>
            <div id="displayNowCounter" class="text-3xl mt-2">number</div>
            <div id="displayNowCategoryWrap" class="hidden text-2xl mt-1 text-[var(--muted)]"><span id="displayNowCategory"></span></div>
            <!-- Branch logo under Now Serving (design-preserving addition) -->
            <div id="displayLogo" class="mt-4 flex items-center justify-start">
              <img src="{{ asset('images/sss.svg') }}" alt="SSS" class="h-12 w-auto opacity-90">
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom crawler -->
      <div class="rounded-lg overflow-hidden bg-[var(--crawler)]" style="height: var(--crawlerH);">
        <div class="py-2">
          <div id="displayCrawler" class="text-[var(--accent)] text-7xl font-extrabold px-6 whitespace-nowrap tracking-wide crawler-animate">CRAWLER</div>
        </div>
      </div>
    </div>

    <script>
      function resolveBranchSlug() {
        try {
          const qp = new URLSearchParams(window.location.search);
          if (qp.has('branch')) return qp.get('branch');
          const m = (window.location.pathname || '').match(/\/branch\/([^\/]+)/);
          if (m && m[1]) return decodeURIComponent(m[1]);
          return localStorage.getItem('lastBranchSlug') || '';
        } catch { return ''; }
      }
      const BRANCH = resolveBranchSlug();
      const lsKey = (base) => BRANCH ? `${base}:${BRANCH}` : base;
      const LAST_TS_KEY = lsKey('ring:last_ts');
      let lastTs = parseInt(localStorage.getItem(LAST_TS_KEY)||'0');
      let audioReady = false; let audioCtx = null;
      let speakQueue = []; let speaking = false; let voices = [];
      const VOICE_ENABLED = true; // always on
      const VOICE_LANG = localStorage.getItem(lsKey('voice_lang')) || 'en-US';
      const VOICE_NAME = localStorage.getItem(lsKey('voice_name')) || '';
      const VOICE_REPEAT = parseInt(localStorage.getItem(lsKey('voice_repeat')) || '1');
      const SPEAK_NUMBER_IN_WORDS = true; // pronounce numbers as words

      // Voice overrides via URL/localStorage for cross-browser consistency
      function getVoiceOverrideName() {
        try {
          const qp = new URLSearchParams(window.location.search);
          const qv = qp.get('voice') || qp.get('voiceName') || '';
          if (qv) { try { localStorage.setItem(lsKey('voice_name'), qv); } catch(_) {} return qv; }
          const saved = localStorage.getItem(lsKey('voice_name')) || '';
          return saved;
        } catch(_) { return ''; }
      }
      function getVoiceOverrideLang() {
        try {
          const qp = new URLSearchParams(window.location.search);
          const ql = qp.get('voiceLang') || qp.get('lang') || '';
          if (ql) { try { localStorage.setItem(lsKey('voice_lang'), ql); } catch(_) {} return ql; }
          return localStorage.getItem(lsKey('voice_lang')) || '';
        } catch(_) { return ''; }
      }
      function getRateOverride() {
        try {
          const qp = new URLSearchParams(window.location.search);
          const qr = qp.get('rate');
          if (qr) { try { localStorage.setItem(lsKey('voice_rate'), qr); } catch(_) {} return parseFloat(qr); }
          const saved = localStorage.getItem(lsKey('voice_rate'));
          return saved ? parseFloat(saved) : null;
        } catch(_) { return null; }
      }
      function getPitchOverride() {
        try {
          const qp = new URLSearchParams(window.location.search);
          const qpitch = qp.get('pitch');
          if (qpitch) { try { localStorage.setItem(lsKey('voice_pitch'), qpitch); } catch(_) {} return parseFloat(qpitch); }
          const saved = localStorage.getItem(lsKey('voice_pitch'));
          return saved ? parseFloat(saved) : null;
        } catch(_) { return null; }
      }

      function unlockAudio(){
        try {
          if (!audioCtx) audioCtx = new (window.AudioContext||window.webkitAudioContext)();
          if (audioCtx.state === 'suspended') audioCtx.resume();
          audioReady = true; localStorage.setItem(lsKey('audio_unlocked'),'1');
        } catch { /* ignore */ }
      }
      // Try to unlock audio automatically to allow beeps; TTS works regardless
      function autoUnlockAudio(){
        try {
          if (!audioCtx) audioCtx = new (window.AudioContext||window.webkitAudioContext)();
          if (audioCtx.state === 'suspended') audioCtx.resume();
          audioReady = true;
        } catch { /* ignore */ }
      }
      document.addEventListener('DOMContentLoaded', autoUnlockAudio);
      document.addEventListener('visibilitychange', ()=>{ if (!document.hidden) autoUnlockAudio(); });
      setInterval(()=>{ try { if (audioCtx && audioCtx.state === 'suspended') audioCtx.resume(); } catch {} }, 5000);

      // No UI gating; voice is always enabled

      function playBeep(freq=880, duration=0.15){
        try {
          if (!audioCtx) audioCtx = new (window.AudioContext||window.webkitAudioContext)();
          const osc = audioCtx.createOscillator(); const gain = audioCtx.createGain();
          osc.type = 'sine'; osc.frequency.value = freq; gain.gain.setValueAtTime(0.0001, audioCtx.currentTime);
          gain.gain.exponentialRampToValueAtTime(0.3, audioCtx.currentTime+0.01);
          osc.connect(gain); gain.connect(audioCtx.destination); osc.start();
          setTimeout(()=>{ gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime+duration); osc.stop(audioCtx.currentTime+duration+0.02); }, duration*1000);
        } catch { /* ignore */ }
      }

      function speakCall(text){
        try {
          const msg = new SpeechSynthesisUtterance(text);
          const list = voices.length ? voices : window.speechSynthesis.getVoices();
          if (!voices.length && list.length) { voices = list; }
          // Resolve overrides
          const overrideName = (getVoiceOverrideName() || VOICE_NAME || '').trim();
          const overrideLang = (getVoiceOverrideLang() || VOICE_LANG || '').trim();
          const rateOverride = getRateOverride();
          const pitchOverride = getPitchOverride();
          let chosen = null;
          if (overrideName) { chosen = list.find(v => v.name && v.name.toLowerCase().includes(overrideName.toLowerCase())) || null; }
          if (!chosen && overrideLang) { chosen = list.find(v => v.lang && v.lang.toLowerCase().startsWith(overrideLang.toLowerCase())) || null; }
          if (!chosen) { chosen = list[0] || null; }
          if (chosen) msg.voice = chosen;
          msg.lang = overrideLang || (chosen && chosen.lang) || 'en-US';
          msg.rate = (rateOverride && isFinite(rateOverride)) ? rateOverride : 1.0;
          msg.pitch = (pitchOverride && isFinite(pitchOverride)) ? pitchOverride : 1.0;
          msg.volume = 1.0;
          window.speechSynthesis.speak(msg);
          msg.onend = () => { speaking = false; setTimeout(processSpeakQueue, 200); };
          msg.onerror = () => { speaking = false; setTimeout(processSpeakQueue, 200); };
        } catch { speaking = false; setTimeout(processSpeakQueue, 200); }
      }

      window.speechSynthesis.onvoiceschanged = () => { try { voices = window.speechSynthesis.getVoices(); } catch {} };

      function enqueueAnnouncement(text){
        const rep = Math.max(1, VOICE_REPEAT);
        for (let i=0; i<rep; i++) speakQueue.push(text);
        processSpeakQueue();
      }

      function processSpeakQueue(){
        if (speaking) return; const next = speakQueue.shift(); if (!next) return;
        speaking = true;
        // Beep before speaking
        playBeep(880, 0.18);
        setTimeout(()=> playBeep(660, 0.18), 220);
        setTimeout(()=> speakCall(next), 500);
      }

      // Attempt a lightweight TTS unlock on first interaction
      document.addEventListener('click', () => { try { speakCall(''); } catch {} }, { once: true });
      document.addEventListener('keydown', () => { try { speakCall(''); } catch {} }, { once: true });

      // Optional console test helpers
      window.testBeep = () => { autoUnlockAudio(); playBeep(880,0.18); };
      window.testVoice = () => { enqueueAnnouncement('Testing voice: announcements are enabled.'); };
      // Set a friendly default voice/pitch/rate if none stored
      document.addEventListener('DOMContentLoaded', () => {
        try {
          if (!localStorage.getItem(lsKey('voice_name'))) { localStorage.setItem(lsKey('voice_name'), 'Michael'); }
          if (!localStorage.getItem(lsKey('voice_pitch'))) { localStorage.setItem(lsKey('voice_pitch'), '1.0'); }
          if (!localStorage.getItem(lsKey('voice_rate'))) { localStorage.setItem(lsKey('voice_rate'), '0.95'); }
        } catch(_) {}
      });

      // Build counters grid
      (function initCounters(){
        const el = document.getElementById('displayAllCounters');
        if (!el) return;
        const COUNTERS = ['Counter 1','Counter 2','Counter 3','Counter 4','Priority','E-Center Priority','Backroom','Medical','E-Center'];
        el.innerHTML = COUNTERS.map((name) => {
          const label = (String(name).toLowerCase() === 'e-center') ? 'E-Center Regular' : name;
          return `
          <div class="relative bg-[var(--tile)] rounded-xl p-4 text-center shadow-sm" data-name="${name.toLowerCase()}">
            <div class="qdb-label text-xl font-semibold uppercase">${label}</div>
            <div class="qdb-num text-5xl font-bold mt-2">---</div>
            <div class="qdb-cat text-2xl text-[var(--muted)] mt-1"></div>
          </div>
          `;
        }).join('');
      })();

      // Ensure a tile exists for a given counter name. If none matches,
      // repurpose an unused tile by changing its label and data-name.
      function ensureTileFor(name, presentKeys){
        const wrap = document.getElementById('displayAllCounters'); if (!wrap) return null;
        const norm = s => String(s||'').trim().toLowerCase();
        const rawKey = norm(name);
        // Special-case mapping: keep E-Center tile for E-Center Regular variants
        const mappedKey = (rawKey === 'e-center regular' || rawKey === 'e center regular' || rawKey === 'ecenter regular' || rawKey === 'e-center reg') ? 'e-center' : rawKey;
        let tile = wrap.querySelector(`[data-name="${mappedKey}"]`);
        if (tile){
          // Update visible label if server name differs
          const labelEl = tile.querySelector('.qdb-label'); if (labelEl) labelEl.textContent = name;
          return tile;
        }
        // Find a candidate tile not currently updated by server
        const children = Array.from(wrap.children);
        const now = Date.now();
        const candidates = children.filter(ch => {
          const key = String(ch.getAttribute('data-name')||'').toLowerCase();
          const numEl = ch.querySelector('.qdb-num'); const catEl = ch.querySelector('.qdb-cat');
          const isUnused = (numEl && (numEl.textContent||'').trim() === '---') && (catEl && !(catEl.textContent||'').trim());
          const notInServer = !presentKeys || !presentKeys.has(key);
          const lockUntil = parseInt(ch.getAttribute('data-lock-until')||'0',10);
          const lockKey = String(ch.getAttribute('data-lock-key')||'');
          const isLockedForOther = lockUntil>now && norm(lockKey)!==mappedKey;
          return (isUnused || notInServer) && !isLockedForOther;
        });
        if (candidates.length){
          tile = candidates[0];
          tile.setAttribute('data-name', mappedKey);
          const labelEl = tile.querySelector('.qdb-label'); if (labelEl) labelEl.textContent = name;
          return tile;
        }
        return null;
      }

      // Time + Date
      function updateTime(){
        const now = new Date();
        let h = now.getHours(); const m = String(now.getMinutes()).padStart(2,'0'); const s = String(now.getSeconds()).padStart(2,'0');
        const ampm = h>=12?'PM':'AM'; h = h%12; h = h? h:12;
        const t = document.getElementById('displayTime'); if (t) t.innerHTML = `${h}:${m}:${s}<br>${ampm}`;
        const dEl = document.getElementById('displayDate'); if (dEl) dEl.textContent = now.toLocaleDateString(undefined, { year:'numeric', month:'long', day:'numeric' });
      }
      setInterval(updateTime, 1000); updateTime();

      // Offline section visibility helpers (hide by default; show only on offline calls)
      function offlineSectionEl(){
        try { const ol = document.getElementById('offlineList'); return ol ? ol.parentElement : null; } catch { return null; }
      }
      document.addEventListener('DOMContentLoaded', ()=>{
        try { const sec = offlineSectionEl(); if (sec) sec.classList.add('hidden'); } catch {}
      });

      // Crawler
      function setCrawlerFromStorage(){
        const el = document.getElementById('displayCrawler'); if (!el) return;
        const text = localStorage.getItem(lsKey('crawler_text')) || 'CRAWLER';
        // Only re-render if changed to avoid unnecessary reflows
        if (el.textContent !== text) {
          el.classList.remove('crawler-animate');
          void el.offsetWidth;
          el.textContent = text;
          el.classList.add('crawler-animate');
        }
      }
      let __lastCrawlerText = null;
      async function fetchCrawlerFromServer(force){
        try {
          const urls = [
            BRANCH ? ('/crawler?branch='+encodeURIComponent(BRANCH)) : '/crawler',
            BRANCH ? ('http://127.0.0.1:8000/crawler?branch='+encodeURIComponent(BRANCH)) : 'http://127.0.0.1:8000/crawler'
          ];
          for (const url of urls){
            try {
              const res = await fetch(url, { cache:'no-store' }); if (!res.ok) continue;
              const data = await res.json(); const text = (data && data.text) ? String(data.text) : 'CRAWLER';
              if (force || text !== __lastCrawlerText) {
                __lastCrawlerText = text;
                try { localStorage.setItem(lsKey('crawler_text'), text); } catch {}
                setCrawlerFromStorage();
              }
              break;
            } catch {}
          }
        } catch {}
      }
      setCrawlerFromStorage(); fetchCrawlerFromServer(true);
      // Faster refresh loop so crawler updates quickly after changes
      setInterval(()=>fetchCrawlerFromServer(false), 3000);
      // Ping server for last-changed to trigger immediate updates
      let __lastCrawlerTs = 0;
      async function pollCrawlerLastChanged(){
        const urls = [
          BRANCH ? ('/crawler/last-changed?branch='+encodeURIComponent(BRANCH)) : '/crawler/last-changed',
          BRANCH ? ('http://127.0.0.1:8000/crawler/last-changed?branch='+encodeURIComponent(BRANCH)) : 'http://127.0.0.1:8000/crawler/last-changed',
          BRANCH ? ('http://localhost:8000/crawler/last-changed?branch='+encodeURIComponent(BRANCH)) : 'http://localhost:8000/crawler/last-changed'
        ];
        for (const url of urls){
          try {
            const res = await fetch(url, { cache:'no-store' }); if (!res.ok) continue;
            const d = await res.json(); const ts = parseInt(d && d.ts ? d.ts : '0', 10);
            if (isFinite(ts) && ts > 0 && ts !== __lastCrawlerTs){
              __lastCrawlerTs = ts;
              fetchCrawlerFromServer(true);
              break;
            }
          } catch {}
        }
      }
      setInterval(pollCrawlerLastChanged, 1000);
      // Listen for localStorage changes to update instantly when same-origin UI edits crawler
      window.addEventListener('storage', (e)=>{
        try {
          if (e.key === lsKey('crawler_text')) setCrawlerFromStorage();
          // When any queue update hint is stored, force-refresh crawler from server
          if (e.key === lsKey('queue_updated')) { fetchCrawlerFromServer(true); pollCrawlerLastChanged(); }
        } catch {}
      });

      // Update counters from server
      async function refreshCounters(){
        try {
          const urls = [
            BRANCH ? ('/counters/status?branch='+encodeURIComponent(BRANCH)) : '/counters/status',
            BRANCH ? ('http://127.0.0.1:8000/counters/status?branch='+encodeURIComponent(BRANCH)) : 'http://127.0.0.1:8000/counters/status'
          ];
          for (const url of urls){
            try {
              const res = await fetch(url, { cache:'no-store' }); if (!res.ok) continue;
              const data = await res.json(); const statuses = data.counters || {}; const norm = s => String(s||'').trim().toLowerCase();
              const wrap = document.getElementById('displayAllCounters'); if (!wrap) return;
              // Clear expired locks so tiles can be reassigned
              try {
                const now = Date.now();
                Array.from(wrap.children).forEach(ch=>{
                  const until = parseInt(ch.getAttribute('data-lock-until')||'0',10);
                  if (until && until<=now){ ch.removeAttribute('data-lock-until'); ch.removeAttribute('data-lock-key'); }
                });
              } catch {}
              const presentKeys = new Set(Object.keys(statuses).map(n => norm(n)));
              Object.keys(statuses).forEach(name => {
                const tile = ensureTileFor(name, presentKeys);
                if (!tile) return; const numEl = tile.querySelector('.qdb-num'); const catEl = tile.querySelector('.qdb-cat');
                const st = statuses[name]; if (numEl) numEl.textContent = (st && st.number) ? st.number : '---'; if (catEl) catEl.textContent = (st && st.category) ? st.category : '';
              });
              break;
            } catch {}
          }
        } catch {}
      }
      setInterval(refreshCounters, 2500); refreshCounters();

      // Poll last ring for Now Serving
      async function pollRing(){
        const endpoints = [
          // Prefer branch-scoped first
          BRANCH ? ('/ring/last?branch='+encodeURIComponent(BRANCH)) : '/ring/last',
          BRANCH ? ('http://127.0.0.1:8000/ring/last?branch='+encodeURIComponent(BRANCH)) : 'http://127.0.0.1:8000/ring/last',
          BRANCH ? ('http://localhost:8000/ring/last?branch='+encodeURIComponent(BRANCH)) : 'http://localhost:8000/ring/last',
          // Fallbacks without branch in case caller didn't send it
          '/ring/last',
          'http://127.0.0.1:8000/ring/last',
          'http://localhost:8000/ring/last'
        ];
        for (const url of endpoints){
          try {
            const res = await fetch(url, { cache:'no-store' }); if (!res.ok) continue; const d = await res.json();
            if (d && (d.number||d.counter||d.category)) {
              const ts = parseInt(d.ts || '0');
              if (!isNaN(ts) && ts > lastTs) { lastTs = ts; localStorage.setItem(LAST_TS_KEY, String(lastTs)); updateNowServing(d, true); break; }
            }
          } catch {}
        }
      }
      function norm(s){ return String(s||'').trim().toLowerCase(); }
      // Convert integers to words (British style with "and") up to 999,999
      function numberToWords(num){
        try {
          num = parseInt(num, 10);
          if (isNaN(num)) return String(num);
          if (num === 0) return 'zero';
          const ones = ['zero','one','two','three','four','five','six','seven','eight','nine','ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen'];
          const tens = ['twenty','thirty','forty','fifty','sixty','seventy','eighty','ninety'];
          const underThousand = (n) => {
            let w = '';
            const h = Math.floor(n/100); const r = n%100;
            if (h>0){ w += ones[h] + ' hundred'; if (r>0) w += ' and '; }
            if (r>0){
              if (r<20){ w += ones[r]; }
              else { const t = Math.floor(r/10); const u = r%10; w += tens[t-2]; if (u>0) w += ' ' + ones[u]; }
            }
            return w;
          };
          const thousands = Math.floor(num/1000); const rest = num%1000; let words='';
          if (thousands>0){ words += underThousand(thousands) + ' thousand'; }
          if (rest>0){ words += (words? ' ' : '') + underThousand(rest); }
          return words;
        } catch { return String(num); }
      }
      function numberStringToWords(raw){
        const digits = String(raw||'').replace(/\D+/g,'');
        if (!digits) return String(raw||'');
        return numberToWords(parseInt(digits,10));
      }
      function updateNowServing(data, isNew=false){
        const number = String(data.number||'- - -'); const category = String(data.category||''); const counter = String(data.counter||'');
        const offline = category.toLowerCase()==='offline' || /offline/i.test(number);
        if (offline){
          // Reveal the offline section only when an offline call happens
          try { const sec = offlineSectionEl(); if (sec) sec.classList.remove('hidden'); } catch {}
          // Show a single static OFFLINE message; do not accumulate
          const ol = document.getElementById('offlineList');
          if (ol) {
            try { ol.removeAttribute('data-items'); } catch {}
            ol.innerHTML = '<div class="inline-block bg-white text-[var(--text)] px-4 py-3 rounded text-4xl font-bold">--OFFLINE--</div>';
          }
          // Ensure the offline announcement is voiced even if we don't update Now Serving
          if (isNew && VOICE_ENABLED) {
            enqueueAnnouncement('Sorry we are offline, please be patient');
          }
          // Also reflect OFFLINE in the Now Serving number for clarity
          try { const nEl=document.getElementById('displayNowNumber'); if(nEl) nEl.textContent='--OFFLINE--'; } catch {}
          try { const cEl=document.getElementById('displayNowCounter'); if(cEl) cEl.textContent=''; } catch {}
          try { const cw=document.getElementById('displayNowCategoryWrap'); if(cw) cw.classList.add('hidden'); } catch {}
          // Auto-clear the offline message after a short delay to restore default text
          try {
            const msRaw = localStorage.getItem(lsKey('offline_auto_clear_ms')) || '';
            const ms = parseInt(msRaw, 10) || 30000; // default 30s
            if (window.__offlineClearTimer) clearTimeout(window.__offlineClearTimer);
            window.__offlineClearTimer = setTimeout(() => {
              const el = document.getElementById('offlineList');
              if (el) { el.removeAttribute('data-items'); el.textContent = 'No offline calls'; }
              try { const sec = offlineSectionEl(); if (sec) sec.classList.add('hidden'); } catch {}
            }, ms);
          } catch {}
          return;
        }
        const nEl = document.getElementById('displayNowNumber'); if (nEl) nEl.textContent = number;
        const cEl = document.getElementById('displayNowCounter'); if (cEl) { cEl.textContent = counter ? `counter ${counter.replace(/^counter\s*/i,'')}` : 'number'; }
        const catWrap = document.getElementById('displayNowCategoryWrap'); const catEl = document.getElementById('displayNowCategory');
        if (category){ catEl.textContent = category; catWrap.classList.remove('hidden'); } else { catWrap.classList.add('hidden'); }

        // Update matching tile immediately, creating/repurposing one if needed
        const wrap = document.getElementById('displayAllCounters');
        if (wrap && counter){
          const tile = ensureTileFor(counter);
          if (tile){
            const labelEl = tile.querySelector('.qdb-label'); if (labelEl) labelEl.textContent = counter;
            const numEl = tile.querySelector('.qdb-num'); const catEl = tile.querySelector('.qdb-cat');
            if (numEl) numEl.textContent = number; if (catEl) catEl.textContent = category;
            // Temporarily lock this tile to the current counter name so periodic
            // refresh won't immediately reassign it. Lock duration is configurable.
            try {
              const ttlRaw = localStorage.getItem(lsKey('tile_lock_ms')) || '';
              const ttl = parseInt(ttlRaw,10) || 15000; // default 15s
              const now = Date.now();
              tile.setAttribute('data-lock-key', String(counter));
              tile.setAttribute('data-lock-until', String(now+ttl));
            } catch {}
            if (isNew){
              tile.classList.add('ring-highlight');
              setTimeout(()=> tile.classList.remove('ring-highlight'), 3000);
            }
          }
        }

        // Audio cues for new calls
        if (isNew && VOICE_ENABLED){
          const spokenNumber = SPEAK_NUMBER_IN_WORDS ? numberStringToWords(number) : number;
          const counterPhrase = counter ? counter.replace(/^counter\s*/i, 'Counter ') : 'Counter';
          const phrase = offline
            ? 'Sorry we are offline, please be patient'
            : `Calling number ${spokenNumber}, Proceed to ${counterPhrase}`;
          enqueueAnnouncement(phrase);
        }
      }
      setInterval(pollRing, 1500); pollRing();

      // Voice is always on; no overlay
    </script>
  </body>
</html>
