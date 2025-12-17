<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title>Queue Display Board</title>
		<script src="https://cdn.tailwindcss.com"></script>
		<link rel="stylesheet" href="{{ asset('css/caller.css') }}">
	</head>
	<body class="bg-gray-900 min-h-screen flex items-center justify-center">
		<div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-3xl p-12 shadow-2xl border-4 border-yellow-500 w-full max-w-[1800px] relative flex flex-col justify-start" style="height: 95vh;">
			<div class="flex items-center justify-between mb-4">
				<h1 class="text-white text-3xl font-semibold text-center w-full">Queue Display Board</h1>
			</div>
			<div class="h-1 w-32 bg-yellow-500 mx-auto rounded-full mb-8"></div>

			<div class="flex flex-row gap-8 mb-8 h-[350px]">
				<div class="flex-1 bg-black rounded-2xl p-12 flex flex-col justify-center">
					<p class="text-yellow-500 text-center mb-4 text-xl">NOW SERVING</p>
					<div id="displayNowBlock" class="text-center">
						<div id="displayNowNumber" class="text-white text-9xl text-center tracking-wider">- - -</div>
						<div id="displayNowCounter" class="mt-6 text-center">
							<p class="text-yellow-500 text-4xl">to -</p>
						</div>
						<div id="displayNowCategoryWrap" class="mt-4 mx-auto max-w-md hidden">
							<div class="bg-gray-800 rounded-xl p-4">
								<p class="text-gray-400 text-sm mb-1">Transaction Type</p>
								<p id="displayNowCategory" class="text-white text-2xl">-</p>
							</div>
						</div>
					</div>
				</div>
				<div class="flex flex-col gap-8 w-72 justify-center">
					<div class="bg-gray-800 rounded-xl p-8 text-center flex flex-col items-center">
						<div class="flex items-center gap-2 mb-2">
							<svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M13 7a3 3 0 11-6 0 3 3 0 016 0zM2 14s1-4 8-4 8 4 8 4v1H2v-1z"/></svg>
							<span class="text-gray-200 text-lg">Waiting</span>
						</div>
						<span id="displayWaitingCount" class="text-yellow-500 text-6xl">0</span>
					</div>
					<div class="bg-gray-800 rounded-xl p-8 text-center flex flex-col items-center">
						<span class="text-gray-200 text-lg mb-2">Time</span>
						<span id="displayTime" class="text-yellow-500 text-5xl">--:--:--<br>AM</span>
					</div>
				</div>
			</div>

			<div class="bg-gray-800 rounded-xl p-6">
				<h2 class="text-yellow-500 text-center text-2xl mb-4">All Counters Status</h2>
				<div id="displayAllCounters" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 2xl:grid-cols-7 gap-4"></div>
			</div>
		</div>

		<!-- One-time Audio Unlock (required by some browsers) -->
		<button id="enableAudio" class="fixed top-4 left-1/2 -translate-x-1/2 px-6 py-3 rounded-full bg-yellow-400 text-black font-bold shadow-2xl z-50 animate-pulse hidden">Enable Sound for Announcements</button>

		<!-- Quick tools removed as requested -->

		<script>
			// Time
			function updateTime() {
				const now = new Date();
				let h = now.getHours();
				const m = now.getMinutes().toString().padStart(2, '0');
				const s = now.getSeconds().toString().padStart(2, '0');
				const ampm = h >= 12 ? 'PM' : 'AM';
				h = h % 12; h = h ? h : 12;
				document.getElementById('displayTime').innerHTML = `${h}:${m}:${s}<br>${ampm}`;
			}
			setInterval(updateTime, 1000); updateTime();

			// Fetch queue data and update visuals
			async function refreshBoard(){
				try {
					const res = await fetch('/debug/queue', { cache: 'no-store' });
					if (!res.ok) return;
					const data = await res.json();
					const tickets = data.tickets || [];

					// Waiting
					document.getElementById('displayWaitingCount').textContent = tickets.filter(t => t.status === 'waiting').length;

					// Now Serving
					const serving = tickets.find(t => t.status === 'serving');
					document.getElementById('displayNowNumber').textContent = serving ? serving.number : '- - -';
					document.getElementById('displayNowCounter').innerHTML = `<p class="text-yellow-500 text-4xl">to ${serving && serving.counter ? serving.counter : '-'}</p>`;
					if (serving && serving.category) {
						document.getElementById('displayNowCategory').textContent = serving.category;
						document.getElementById('displayNowCategoryWrap').classList.remove('hidden');
					} else {
						document.getElementById('displayNowCategoryWrap').classList.add('hidden');
					}

					// All counters grid
					  const COUNTERS = ['Counter 1', 'Counter 2', 'Counter 3', 'Backroom', 'E-Center Regular', 'E-Center Priority', 'Priority'];
					const byCounter = {};
					tickets.filter(t => t.status === 'serving' && t.counter).forEach(t => { byCounter[String(t.counter)] = t; });
					document.getElementById('displayAllCounters').innerHTML = COUNTERS.map(name => {
						const t = byCounter[name];
						const num = t ? (t.number || '---') : '---';
						const cat = t ? (t.category || '') : '';
						return `
							<div class="bg-black rounded-xl p-8 text-center">
								<span class="text-gray-200">${name}</span>
								<div class="text-white text-3xl mt-4">${num}</div>
								${cat ? `<div class=\"text-gray-500 text-sm mt-2 truncate\">${cat}</div>` : ''}
							</div>
						`;
					}).join('');
				} catch (e) {}
			}
			setInterval(refreshBoard, 2000); refreshBoard();

			// Cross-instance ring: poll Caller app (port 8000) to trigger updates here (port 8001)
			let lastRingTs = 0;
			let lastEventSig = '';

			function updateNowServingFromPayload(data) {
				if (!data) return;
				const ts = data.ts || 0;
				const sig = `${data.number || ''}|${data.counter || ''}|${data.category || ''}|${ts}`;
				if (sig === lastEventSig) return; // suppress duplicates of same call
				lastEventSig = sig;
				lastRingTs = ts || Date.now();
				const number = data.number || '- - -';
				const counter = data.counter || '-';
				const category = data.category || '';
				document.getElementById('displayNowNumber').textContent = number;
				document.getElementById('displayNowCounter').innerHTML = `<p class="text-yellow-500 text-4xl">to ${counter}</p>`;
				if (category) {
					document.getElementById('displayNowCategory').textContent = category;
					document.getElementById('displayNowCategoryWrap').classList.remove('hidden');
				} else {
					document.getElementById('displayNowCategoryWrap').classList.add('hidden');
				}

				// queue automated voice announcement
				if (window.speechSynthesis) {
					pendingAnnouncements.push({ number, counter, category });
					processAnnouncements();
				}
			}

			// --- Automated Voice Announcer ---
			function playChime() {
				try {
					const Ctx = window.AudioContext || window.webkitAudioContext;
					if (!Ctx) return;
					const ctx = window.__qdbAudioCtx || new Ctx();
					window.__qdbAudioCtx = ctx;
					if (ctx.state === 'suspended') { try { ctx.resume(); } catch(_) {} }
					const now = ctx.currentTime + 0.01;
					// Brighter, more musical chime (C5, E5, G5)
					const notes = [523.25, 659.25, 783.99];
					notes.forEach((f, i) => {
						const o = ctx.createOscillator();
						const g = ctx.createGain();
						o.type = 'triangle';
						o.frequency.setValueAtTime(f, now + i * 0.22);
						o.connect(g); g.connect(ctx.destination);
						g.gain.setValueAtTime(0.0001, now + i * 0.22);
						g.gain.exponentialRampToValueAtTime(0.35, now + i * 0.22 + 0.03);
						g.gain.exponentialRampToValueAtTime(0.0001, now + i * 0.22 + 0.28);
						o.start(now + i * 0.22);
						o.stop(now + i * 0.22 + 0.30);
					});
				} catch(e) { /* ignore */ }
			}

			let preferredVoice = null;
			function pickPreferredVoice() {
				try {
					const vs = window.speechSynthesis.getVoices() || [];
					preferredVoice = vs.find(v => v.name.includes('Google US English'))
						|| vs.find(v => v.name.includes('Samantha'))
						|| vs.find(v => v.name.includes('Alex'))
						|| vs.find(v => v.name.includes('Victoria'))
						|| vs.find(v => (v.lang || '').startsWith('en'))
						|| vs[0] || null;
				} catch(e) { preferredVoice = null; }
			}
			try {
				window.speechSynthesis.onvoiceschanged = pickPreferredVoice;
				pickPreferredVoice();
			} catch(e) {}

			function spellDigits(n) {
				const s = String(n || '').replace(/\D/g, '');
				if (!s) return String(n || '');
				const map = { '0':'zero','1':'one','2':'two','3':'three','4':'four','5':'five','6':'six','7':'seven','8':'eight','9':'nine' };
				return s.split('').map(d => map[d] || d).join(' ');
			}

			let audioReady = false;
			function markAudioReady() {
				audioReady = true;
				const btn = document.getElementById('enableAudio');
				if (btn) btn.classList.add('hidden');
				// overlay removed
			}

			function speakOnce(text) {
				return new Promise(resolve => {
					let resolved = false;
					const finish = () => { if (resolved) return; resolved = true; markAudioReady(); resolve(); };
					try {
						const u = new SpeechSynthesisUtterance(text);
						u.rate = 1; u.pitch = 1; u.volume = 1;
						u.lang = 'en-US';
						if (preferredVoice) u.voice = preferredVoice;
						u.onend = finish;
						u.onerror = finish;
						const timer = setTimeout(finish, 6000); // fallback in case onend never fires
						const clearAll = () => { try { clearTimeout(timer); } catch(_) {} };
						u.onend = () => { clearAll(); finish(); };
						u.onerror = () => { clearAll(); finish(); };
						if (window.speechSynthesis.speaking) {
							try { window.speechSynthesis.cancel(); } catch(_) {}
							setTimeout(() => { try { window.speechSynthesis.speak(u); } catch(_) { finish(); } }, 60);
						} else {
							window.speechSynthesis.speak(u);
						}
					} catch(e) { finish(); }
				});
			}

			let speaking = false;
			const pendingAnnouncements = [];
      const speakDigits = false; // digits toggle removed
      const ANNOUNCE_DELAY_MS = 3000; // pause before speaking next number

      function delay(ms){ return new Promise(res => setTimeout(res, ms)); }
			async function processAnnouncements() {
				if (speaking) return;
				const next = pendingAnnouncements.shift();
				if (!next) return;
				speaking = true;
				try {
					// Ringer removed: voice-only announcement
					// Use exact phrasing as requested
					// Wait briefly before announcing to give agents a pause
					await delay(ANNOUNCE_DELAY_MS);
					const rawNumber = String(next.number || '').trim();
					const numberPhrase = speakDigits ? spellDigits(rawNumber) : rawNumber;
					const counterPhrase = (String(next.counter || '')).replace(/^counter\s*/i, 'Counter ');
					const phrase = `Calling number ${numberPhrase}, Proceed to ${counterPhrase}`;
					await speakOnce(phrase);
				} finally {
					speaking = false;
					if (pendingAnnouncements.length) processAnnouncements();
				}
			}
			async function pollRemoteRing() {
				try {
					const res = await fetch('http://127.0.0.1:8000/ring/last', { cache: 'no-store' });
					if (!res.ok) return;
					const data = await res.json();
					if (!data) return;
					updateNowServingFromPayload(data);
				} catch(e) { /* ignore */ }
			}
			setInterval(pollRemoteRing, 1500);
			pollRemoteRing();

			// Same-origin instant trigger (when board runs on 8000): BroadcastChannel + storage
			try {
				const ch = new BroadcastChannel('queue-events');
				ch.onmessage = (ev) => {
					const msg = (ev && ev.data) || {};
					if (msg && msg.type === 'ring') updateNowServingFromPayload(msg);
				};
			} catch(e) { /* ignore */ }

			window.addEventListener('storage', (e) => {
				if (e.key === 'queue_ring' && e.newValue) {
					try { const payload = JSON.parse(e.newValue); updateNowServingFromPayload(payload); } catch(err) {}
				}
			});

			// Attempt to unlock audio on first interaction and show a helper button if locked
			function tryUnlockAudio() {
				if (audioReady) return;
				try {
					const Ctx = window.AudioContext || window.webkitAudioContext;
					if (Ctx) {
						const ctx = new Ctx();
						if (ctx.state === 'suspended') ctx.resume();
					}
					// short test speak to unlock TTS (mark ready immediately)
					speakOnce('');
					markAudioReady();
				} catch(e) {}
			}
			document.addEventListener('click', tryUnlockAudio, { once: true });
			document.addEventListener('keydown', tryUnlockAudio, { once: true });

			// If not yet unlocked shortly, show the Enable Sound banner
			setTimeout(() => {
				if (!audioReady) {
					const btn = document.getElementById('enableAudio');
					if (btn) {
						btn.classList.remove('hidden');
						btn.addEventListener('click', () => {
							tryUnlockAudio();
							speakOnce('Announcements enabled');
						});
					}
				}
			}, 800);

			// Quick tools removed
		</script>
	</body>
</html>
