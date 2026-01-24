<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Queue Display Board</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/caller.css') }}">
    <style>
      @keyframes crawler-scroll { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }
      .crawler-animate { animation: crawler-scroll 18s linear infinite; white-space: nowrap; }
      @keyframes call-blink { 0%,100% { opacity:1; } 50% { opacity:0.35; } }
      .calling-blink { animation: call-blink 0.7s ease-in-out 3; }
    </style>
  </head>
  <body class="bg-gray-900 min-h-screen flex items-center justify-center">
    <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-3xl p-12 shadow-2xl border-4 border-yellow-500 w-full max-w-[1800px] relative flex flex-col justify-start" style="height: 95vh;">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-white text-3xl font-semibold text-center w-full">Queue Display Board <span id="branchLabel" class="text-yellow-400 text-xl font-normal"></span></h1>
      </div>
      <div class="h-1 w-40 bg-yellow-500 mx-auto rounded-full mb-8"></div>

      <div class="flex flex-row items-center gap-8 mb-8 h-[520px]">
        <div class="flex-1 bg-black rounded-2xl p-12 flex flex-col items-center justify-center">
          <p class="text-yellow-500 text-center mb-4 text-4xl">NOW SERVING</p>
          <div id="displayNowBlock" class="text-center">
            <div id="displayNowNumber" class="text-white text-[clamp(14rem,20vw,24rem)] leading-none text-center tracking-wider">- - -</div>
            <div id="displayNowCounter" class="mt-6 text-center text-yellow-500 text-7xl">to -</div>
            <div id="displayNowCategoryWrap" class="mt-4 mx-auto max-w-md hidden">
              <div class="bg-gray-800 rounded-xl p-4">
                <p class="text-gray-400 text-sm mb-1">Transaction Type</p>
                <p id="displayNowCategory" class="text-white text-4xl">-</p>
              </div>
            </div>
          </div>
        </div>
        <div class="flex flex-col gap-8 w-72 justify-center">
          <div class="bg-gray-800 rounded-xl p-8 text-center flex flex-col items-center">
            <span class="text-gray-200 text-lg mb-2">Time</span>
            <span id="displayTime" class="text-yellow-500 text-6xl">--:--:--<br>AM</span>
          </div>
        </div>
      </div>

      <div class="bg-gray-800 rounded-xl p-6">
        <h2 class="text-yellow-500 text-center text-4xl mb-4">All Counters Status</h2>
        <div id="displayAllCounters" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 2xl:grid-cols-7 gap-4 place-items-center"></div>
      </div>

      <div class="absolute left-0 right-0 bottom-0 bg-black py-4 overflow-hidden rounded-b-3xl border-t border-gray-700">
        <div id="displayCrawler" class="text-yellow-400 text-7xl font-extrabold px-8 tracking-wide crawler-animate">Hello</div>
      </div>
    </div>

    <button id="enableAudio" class="fixed top-4 left-1/2 -translate-x-1/2 px-6 py-3 rounded-full bg-yellow-400 text-black font-bold shadow-2xl z-50 hidden">Enable Sound for Announcements</button>

    <script>
      function resolveBranchSlug() {
        try {
          const qp = new URLSearchParams(window.location.search);
          if (qp.has('branch')) return qp.get('branch');
          const m = (window.location.pathname || '').match(/\/branch\/([^\/]+)/);
          if (m && m[1]) return decodeURIComponent(m[1]);
          const saved = localStorage.getItem('lastBranchSlug');
          return saved || '';
        } catch (e) { return ''; }
      }
      const BRANCH = resolveBranchSlug();
      const lsKey = (base) => BRANCH ? `${base}:${BRANCH}` : base;

      function getVoiceOverrideName(){ try { const qp=new URLSearchParams(location.search); const qv=qp.get('voice')||qp.get('voiceName')||''; if(qv){localStorage.setItem(lsKey('voice_name'),qv); return qv;} return localStorage.getItem(lsKey('voice_name'))||''; } catch(_){ return ''; } }
      function getVoiceOverrideLang(){ try { const qp=new URLSearchParams(location.search); const ql=qp.get('voiceLang')||qp.get('lang')||''; if(ql){localStorage.setItem(lsKey('voice_lang'),ql); return ql;} return localStorage.getItem(lsKey('voice_lang'))||''; } catch(_){ return ''; } }
      function getRateOverride(){ try { const qp=new URLSearchParams(location.search); const qr=qp.get('rate'); if(qr){localStorage.setItem(lsKey('voice_rate'),qr); return parseFloat(qr);} const s=localStorage.getItem(lsKey('voice_rate')); return s?parseFloat(s):null; } catch(_){ return null; } }
      function getPitchOverride(){ try { const qp=new URLSearchParams(location.search); const q=qp.get('pitch'); if(q){localStorage.setItem(lsKey('voice_pitch'),q); return parseFloat(q);} const s=localStorage.getItem(lsKey('voice_pitch')); return s?parseFloat(s):null; } catch(_){ return null; } }

      function updateTime(){ const now=new Date(); let h=now.getHours(); const m=String(now.getMinutes()).padStart(2,'0'); const s=String(now.getSeconds()).padStart(2,'0'); const ampm=h>=12?'PM':'AM'; h=h%12; h=h?h:12; const el=document.getElementById('displayTime'); if(el) el.innerHTML=`${h}:${m}:${s}<br>${ampm}`; }
      setInterval(updateTime,1000); updateTime();

      (function initCounters(){ const el=document.getElementById('displayAllCounters'); if(!el) return; const COUNTERS=['Counter 1','Counter 2','Counter 3','Counter 4','Priority','E-Center Priority','Backroom','Medical','E-Center']; el.innerHTML=COUNTERS.map(name=>`\n          <div class=\"relative bg-black rounded-2xl p-4 text-center flex flex-col items-center justify-center min-h-[320px]\" data-name=\"${String(name).trim().toLowerCase()}\">\n            <div class=\"text-gray-200 text-3xl\">${name}</div>\n            <div class=\"qdb-num text-white text-[8rem] leading-none mt-2\">---</div>\n            <div class=\"qdb-cat text-gray-400 text-4xl mt-1\"></div>\n          </div>\n        `).join(''); })();

      function setCrawlerFromStorage(){ const el=document.getElementById('displayCrawler'); if(!el) return; const text=localStorage.getItem(lsKey('crawler_text'))||'Hello'; el.classList.remove('crawler-animate'); void el.offsetWidth; el.textContent=text; el.classList.add('crawler-animate'); }
      async function fetchCrawlerFromServer(){ try { const url=BRANCH?('/crawler?branch='+encodeURIComponent(BRANCH)):'/crawler'; const res=await fetch(url,{cache:'no-store'}); if(!res.ok) return; const d=await res.json(); const text=(d&&d.text)?String(d.text):'Hello'; localStorage.setItem(lsKey('crawler_text'),text); setCrawlerFromStorage(); } catch(_){} }
      window.addEventListener('storage',(e)=>{ if(e.key===lsKey('crawler_text')) setCrawlerFromStorage(); }); setCrawlerFromStorage(); fetchCrawlerFromServer(); setInterval(fetchCrawlerFromServer,15000);

      function startBlink(el){ try { if(!el) return; el.classList.remove('calling-blink'); void el.offsetWidth; el.classList.add('calling-blink'); el.addEventListener('animationend',()=>{ try{ el.classList.remove('calling-blink'); }catch(_){} },{once:true}); } catch(_){}}

      function updateCounterTile(counterName,num,cat){ if(!counterName) return; const norm=(s)=>String(s||'').trim().toLowerCase(); const cont=document.getElementById('displayAllCounters'); if(!cont) return; const tile=cont.querySelector(`[data-name="${norm(counterName)}"]`); if(!tile) return; const n=tile.querySelector('.qdb-num'); const c=tile.querySelector('.qdb-cat'); if(n) n.textContent=num||'---'; if(c) c.textContent=cat||''; }

      let preferredVoice=null; function pickPreferredVoice(){ try{ const vs=window.speechSynthesis.getVoices()||[]; const override=(getVoiceOverrideName()||'').toLowerCase().trim(); if(override){ preferredVoice=vs.find(v=>v.name.toLowerCase().includes(override))||null; if(preferredVoice) return; } preferredVoice=vs.find(v=>v.name.toLowerCase().includes('michael'))||vs.find(v=>/^en/.test(v.lang||''))||vs[0]||null; }catch(_){ preferredVoice=null; } } try{ window.speechSynthesis.onvoiceschanged=pickPreferredVoice; pickPreferredVoice(); }catch(_){}
      function numberToWords(num){ try{ const n=parseInt(num,10); if(!isFinite(n)) return String(num); if(n===0) return 'zero'; const ones=['','one','two','three','four','five','six','seven','eight','nine']; const teens=['ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen']; const tens=['','','twenty','thirty','forty','fifty','sixty','seventy','eighty','ninety']; function under100(x){ if(x<10) return ones[x]; if(x<20) return teens[x-10]; const t=Math.floor(x/10); const o=x%10; return o?`${tens[t]} ${ones[o]}`:`${tens[t]}`; } function under1000(x){ const h=Math.floor(x/100); const r=x%100; if(h&&r) return `${ones[h]} hundred ${under100(r)}`; if(h&&!r) return `${ones[h]} hundred`; return under100(r);} const k=Math.floor(n/1000); const r=n%1000; if(!k) return under1000(n); const left=`${under1000(k)} thousand`; if(!r) return left; if(r<100) return `${left} and ${under100(r)}`; return `${left} and ${under1000(r)}`; }catch(_){ return String(num);} }
      function numberStringToWords(raw){ try{ return String(raw||'').replace(/\d+/g,(m)=>numberToWords(m)); }catch(_){ return String(raw||''); } }
      const MIN_COUNTER_UPDATE_GAP_MS=5000; window.counterUpdateTs=window.counterUpdateTs||{};

      let audioReady=false; function markAudioReady(){ audioReady=true; const btn=document.getElementById('enableAudio'); if(btn) btn.classList.add('hidden'); }
      function speakOnce(text){ return new Promise(resolve=>{ let done=false; const finish=()=>{ if(done) return; done=true; markAudioReady(); resolve(); }; try{ const u=new SpeechSynthesisUtterance(text); const rate=getRateOverride(); const pitch=getPitchOverride(); u.rate=(rate&&isFinite(rate))?rate:0.85; u.pitch=(pitch&&isFinite(pitch))?pitch:1.0; u.volume=1; const lang=getVoiceOverrideLang(); u.lang=lang||(preferredVoice && preferredVoice.lang)||'en-GB'; if(preferredVoice) u.voice=preferredVoice; const timer=setTimeout(finish,6000); const clearAll=()=>{ try{ clearTimeout(timer);}catch(_){}}; u.onend=()=>{ clearAll(); finish(); }; u.onerror=()=>{ clearAll(); finish(); }; if(window.speechSynthesis.speaking){ try{ window.speechSynthesis.cancel(); }catch(_){ } setTimeout(()=>{ try{ window.speechSynthesis.speak(u);}catch(_){ finish(); } }, 60); } else { window.speechSynthesis.speak(u); } }catch(_){ finish(); } }); }

      let speaking=false; const pendingAnnouncements=[]; const ANNOUNCE_DELAY_MS=3000; async function processAnnouncements(){ if(speaking) return; const next=pendingAnnouncements.shift(); if(!next) return; speaking=true; try{ await new Promise(res=>setTimeout(res,ANNOUNCE_DELAY_MS)); const raw=String(next.number||'').trim(); const numberPhrase=numberStringToWords(raw); const counterPhrase=(String(next.counter||'')).replace(/^counter\s*/i,'Counter '); const isOffline=(String(next.category||'').toLowerCase()==='offline')||/offline/i.test(raw); const phrase=isOffline? 'Sorry we are offline, please be patient' : `Calling number ${numberPhrase}, Proceed to ${counterPhrase}`; await speakOnce(phrase); } finally { speaking=false; if(pendingAnnouncements.length) processAnnouncements(); } }

      function updateNowServingFromPayload(data){ if(!data) return; try{ window.latestRing={ number:String(data.number||''), counter:String(data.counter||''), category:String(data.category||'') }; }catch(_){ } const number=(data.number?String(data.number).trim():'- - -'); const counter=(data.counter?String(data.counter).trim():'-'); const category=(data.category?String(data.category).trim():''); const isOffline=(category.toLowerCase()==='offline')||/offline/i.test(number);
        // Throttle updates when same counter gets a new number within 5s
        try{ const key=String(counter||'').trim().toLowerCase(); const lastTs=window.counterUpdateTs[key]||0; const elapsed=Date.now()-lastTs; const prev=window.lastNowServing||{}; const sameCounter=key && String(prev.counter||'').trim().toLowerCase()===key; const numberChanged=String(prev.number||'')!==number; if(!isOffline && sameCounter && numberChanged && elapsed<MIN_COUNTER_UPDATE_GAP_MS){ const wait=MIN_COUNTER_UPDATE_GAP_MS-elapsed; setTimeout(()=>{ try{ updateNowServingFromPayload(data); }catch(_){ } }, wait); return; } }catch(_){ }
        if(isOffline){ const offlineList=document.getElementById('offlineList'); if(offlineList){ offlineList.innerHTML='<div class="text-center"><div class="text-6xl font-bold text-slate-900 tracking-wide">--OFFLINE--</div></div>'; } const numEl=document.getElementById('displayNowNumber'); if(numEl){ numEl.textContent='--OFFLINE--'; }
          try{ const msRaw=localStorage.getItem(lsKey('offline_auto_clear_ms'))||''; const ms=parseInt(msRaw,10)||30000; if(window.__offlineClearTimer) clearTimeout(window.__offlineClearTimer); window.__offlineClearTimer=setTimeout(()=>{ const ol=document.getElementById('offlineList'); if(ol){ ol.innerHTML='<div class="w-full rounded-lg bg-slate-100 text-slate-600 text-center py-2">No offline calls</div>'; } }, ms);}catch(_){ } } else {
          const numEl=document.getElementById('displayNowNumber'); if(numEl){ numEl.textContent=number; startBlink(numEl); }
          const ctrEl=document.getElementById('displayNowCounter'); if(ctrEl){ ctrEl.textContent=`Counter ${counter.replace(/^counter\s*/i,'')}`; }
          const catEl=document.getElementById('displayNowCategory'); const wrapEl=document.getElementById('displayNowCategoryWrap'); if(category){ if(catEl) catEl.textContent=category; if(wrapEl) wrapEl.classList.remove('hidden'); } else { if(wrapEl) wrapEl.classList.add('hidden'); }
          const offlineList=document.getElementById('offlineList'); if(offlineList){ offlineList.innerHTML='<div class="w-full rounded-lg bg-slate-100 text-slate-600 text-center py-2">No offline calls</div>'; }
        }
        try{ const isOfflinePersist=(category.toLowerCase()==='offline')||/offline/i.test(number); if(!isOfflinePersist){ window.lastNowServing={ number, counter, category, ts:Date.now() }; localStorage.setItem(lsKey('last_now_serving'), JSON.stringify(window.lastNowServing)); } }catch(_){ }
        if(!isOffline){ updateCounterTile(counter, number, category); const cont=document.getElementById('displayAllCounters'); const norm=(s)=>String(s||'').trim().toLowerCase(); const tile=cont?cont.querySelector(`[data-name="${norm(counter)}"]`):null; const nEl=tile?tile.querySelector('.qdb-num'):null; if(nEl){ startBlink(nEl); } try{ const key=String(counter||'').trim().toLowerCase(); window.counterUpdateTs[key]=Date.now(); }catch(_){ } }
        if(window.speechSynthesis && !isOffline){ pendingAnnouncements.push({ number, counter, category }); processAnnouncements(); }
      }

      async function refreshCounters(){ try { const url=BRANCH?('/counters/status?branch='+encodeURIComponent(BRANCH)):'/counters/status'; const res=await fetch(url,{cache:'no-store'}); if(!res.ok) return; const data=await res.json(); const statuses=data.counters||{}; const norm=(s)=>String(s||'').trim().toLowerCase(); const names=Array.isArray(data.names)&&data.names.length?data.names:Object.keys(statuses); const cont=document.getElementById('displayAllCounters'); if(!cont) return; // ensure all tiles exist
        cont.innerHTML=names.map(name=>{ const key=norm(name); const st=statuses[name]||statuses[key]||null; const num=st? (st.number||'---') : '---'; const cat=st? (st.category||'') : ''; return `\n          <div class=\"relative bg-black rounded-2xl p-4 text-center flex flex-col items-center justify-center min-h-[320px]\" data-name=\"${String(name).trim().toLowerCase()}\">\n            <div class=\"text-gray-200 text-3xl\">${name}</div>\n            <div class=\"qdb-num text-white text-[8rem] leading-none mt-2\">${num}</div>\n            <div class=\"qdb-cat text-gray-400 text-4xl mt-1\">${cat}</div>\n          </div>\n        `; }).join(''); } catch(_){ } }
      setInterval(refreshCounters,2500); refreshCounters();

      async function pollRemoteRing(){ const endpoints=[ BRANCH?('/ring/last?branch='+encodeURIComponent(BRANCH)):'/ring/last', BRANCH?('http://127.0.0.1:8000/ring/last?branch='+encodeURIComponent(BRANCH)):'http://127.0.0.1:8000/ring/last', BRANCH?('http://localhost:8000/ring/last?branch='+encodeURIComponent(BRANCH)):'http://localhost:8000/ring/last' ]; for(const url of endpoints){ try{ const res=await fetch(url,{cache:'no-store'}); if(!res.ok) continue; const data=await res.json(); if(data && (data.number||data.counter||data.category)){ updateNowServingFromPayload(data); break; } }catch(_){ } } }
      setInterval(pollRemoteRing,1500); pollRemoteRing();

      function tryUnlockAudio(){ if(audioReady) return; try{ const Ctx=window.AudioContext||window.webkitAudioContext; if(Ctx){ const ctx=new Ctx(); if(ctx.state==='suspended') ctx.resume(); } speakOnce(''); markAudioReady(); }catch(_){ } }
      document.addEventListener('click', tryUnlockAudio, { once:true });
      document.addEventListener('keydown', tryUnlockAudio, { once:true });
      setTimeout(()=>{ if(!audioReady){ const btn=document.getElementById('enableAudio'); if(btn){ btn.classList.remove('hidden'); btn.addEventListener('click', ()=>{ tryUnlockAudio(); speakOnce('Announcements enabled'); }); } } }, 800);

      // Init branch label
      try { const bl=document.getElementById('branchLabel'); if(bl){ bl.textContent = BRANCH ? `(Branch: ${BRANCH})` : ''; } if(BRANCH){ localStorage.setItem('lastBranchSlug', BRANCH); } } catch(_){}
    </script>
  </body>
</html>
