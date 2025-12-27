<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title>Queue Display Board</title>
		<script src="https://cdn.tailwindcss.com"></script>
		<link rel="stylesheet" href="{{ asset('css/caller.css') }}">
		<style>
			@keyframes crawler-scroll {
				0% { transform: translateX(100%); }
				100% { transform: translateX(-100%); }
			}
			.crawler-animate { animation: crawler-scroll 18s linear infinite; white-space: nowrap; }
		</style>
	</head>
	<body class="bg-gray-900 min-h-screen flex items-center justify-center">
		<div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-3xl p-12 shadow-2xl border-4 border-yellow-500 w-full max-w-[1800px] relative flex flex-col justify-start" style="height: 95vh;">
			<div class="flex items-center justify-between mb-4">
				<h1 class="text-white text-3xl font-semibold text-center w-full">Queue Display Board <span id="branchLabel" class="text-yellow-400 text-xl font-normal"></span></h1>
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
				<h2 class="text-yellow-500 text-center text-3xl mb-4">All Counters Status</h2>
				<div id="displayAllCounters" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 2xl:grid-cols-7 gap-4"></div>
			</div>

			<!-- Crawler at bottom -->
			<div class="absolute left-0 right-0 bottom-0 bg-black py-4 overflow-hidden rounded-b-3xl border-t border-gray-700">
				<div id="displayCrawler" class="text-yellow-400 text-5xl font-semibold px-8 crawler-animate">Hello</div>
			</div>
		</div>

		<!-- One-time Audio Unlock (required by some browsers) -->
		<button id="enableAudio" class="fixed top-4 left-1/2 -translate-x-1/2 px-6 py-3 rounded-full bg-yellow-400 text-black font-bold shadow-2xl z-50 animate-pulse hidden">Enable Sound for Announcements</button>

		<!-- Quick tools removed as requested -->

		<script>
			// Detect branch from URL path (/branch/{slug}/...) or query (?branch=slug)
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
			// Branch-scoped storage/channel helpers
			const lsKey = (base) => BRANCH ? `${base}:${BRANCH}` : base;
			const CHANNEL_NAME = lsKey('queue-events');
			function setCrawlerFromStorage(){
				try {
					const el = document.getElementById('displayCrawler');
					if (!el) return;
					const text = localStorage.getItem(lsKey('crawler_text')) || 'Hello';
					el.classList.remove('crawler-animate');
					void el.offsetWidth;
					el.textContent = text;
					el.classList.add('crawler-animate');
				} catch(e) {}
			}
			window.addEventListener('storage', (e) => { if (e.key === lsKey('crawler_text')) setCrawlerFromStorage(); });
			window.addEventListener('DOMContentLoaded', setCrawlerFromStorage);
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

			// Pre-render counter tiles so the grid is always visible
			(function initCountersGrid(){
				try {
					const el = document.getElementById('displayAllCounters');
					if (!el) return;
					const COUNTERS = ['Counter 1', 'Counter 2', 'Counter 3', 'Backroom', 'E-Center Regular', 'E-Center Priority', 'Priority'];
					el.innerHTML = COUNTERS.map(name => `
						<div class="bg-black rounded-xl p-8 text-center">
							<span class="text-gray-200 text-xl">${name}</span>
							<div class="text-white text-5xl mt-4">---</div>
						</div>
					`).join('');
				} catch(e) {}
			})();

			// Initialize NOW SERVING from saved last ring (no speech) and show branch label
			window.addEventListener('DOMContentLoaded', () => {
				try {
					const bl = document.getElementById('branchLabel');
					if (bl) { bl.textContent = BRANCH ? `(Branch: ${BRANCH})` : ''; }
					// Persist active branch for issuer pages opened without /branch prefix
					if (BRANCH) { try { localStorage.setItem('lastBranchSlug', BRANCH); } catch (e) {} }
					const saved = JSON.parse(localStorage.getItem(lsKey('last_now_serving')) || 'null');
					if (saved) {
						const num = saved.number || '- - -';
						const ctr = saved.counter || '-';
						const cat = saved.category || '';
						document.getElementById('displayNowNumber').textContent = num;
						document.getElementById('displayNowCounter').innerHTML = `<p class="text-yellow-500 text-4xl">to ${ctr}</p>`;
						if (cat) {
							document.getElementById('displayNowCategory').textContent = cat;
							document.getElementById('displayNowCategoryWrap').classList.remove('hidden');
						} else {
							document.getElementById('displayNowCategoryWrap').classList.add('hidden');
						}
					}
				} catch(e) {}
			});

			// Fetch queue data and update visuals
			const USE_QUEUE_FOR_NOW_SERVING = true; // temporary: use queue data to verify pipeline
			async function refreshBoard(){
				try {
					const url = BRANCH ? ('/debug/queue?branch=' + encodeURIComponent(BRANCH)) : '/debug/queue';
					const res = await fetch(url, { cache: 'no-store' });
					if (!res.ok) return;
					const data = await res.json();
					const tickets = data.tickets || [];

					// Waiting
					document.getElementById('displayWaitingCount').textContent = tickets.filter(t => t.status === 'waiting').length;

					// Now Serving (optional from queue) — disabled to avoid flicker; ring/local storage drives NOW SERVING
					if (USE_QUEUE_FOR_NOW_SERVING) {
						// If an Offline message was just shown, temporarily skip overriding from queue
						if (window.__offlineHoldUntil && Date.now() < window.__offlineHoldUntil) {
							// do nothing; Offline will remain on screen until hold expires
						} else {
							const serving = tickets.find(t => t.status === 'serving');
							let current = null;
							if (serving) {
								current = { number: serving.number || '- - -', counter: serving.counter || '-', category: serving.category || '' };
								try { window.lastNowServing = { ...current, ts: Date.now() }; localStorage.setItem(lsKey('last_now_serving'), JSON.stringify(window.lastNowServing)); } catch(e) {}
							} else {
								try { current = window.lastNowServing || JSON.parse(localStorage.getItem(lsKey('last_now_serving')) || 'null'); } catch(e) { current = null; }
							}

							const num = current && current.number ? current.number : '- - -';
							const ctr = current && current.counter ? current.counter : '-';
							const cat = current && current.category ? current.category : '';
							document.getElementById('displayNowNumber').textContent = num;
							document.getElementById('displayNowCounter').innerHTML = `<p class=\"text-yellow-500 text-4xl\">to ${ctr}</p>`;
							if (cat) {
								document.getElementById('displayNowCategory').textContent = cat;
								document.getElementById('displayNowCategoryWrap').classList.remove('hidden');
							} else {
								document.getElementById('displayNowCategoryWrap').classList.add('hidden');
							}
						}
					}

					// All Counters Status via dedicated endpoint (more reliable than scanning tickets)
					try {
						const countersUrl = BRANCH ? ('/counters/status?branch=' + encodeURIComponent(BRANCH)) : '/counters/status';
						const cRes = await fetch(countersUrl, { cache: 'no-store' });
						if (cRes.ok) {
							const cData = await cRes.json();
							const statusesRaw = cData.counters || {};
							const norm = (s) => String(s || '').trim().toLowerCase();
							// Normalize keys from server
							const statuses = {};
							Object.keys(statusesRaw).forEach(k => { statuses[norm(k)] = statusesRaw[k]; });
							// Merge in latest ring payload on the client to ensure the called counter shows immediately (skip Offline)
							try {
								const r = window.latestRing;
								const isOffline = r && ((String(r.category || '').toLowerCase() === 'offline') || /offline/i.test(String(r.number || '')));
								if (r && r.counter && !isOffline) {
									statuses[norm(r.counter)] = { number: r.number, category: r.category };
								}
							} catch(e) {}
							const COUNTERS = ['Counter 1', 'Counter 2', 'Counter 3', 'Backroom', 'E-Center Regular', 'E-Center Priority', 'Priority'];
							document.getElementById('displayAllCounters').innerHTML = COUNTERS.map(name => {
								const t = statuses[norm(name)];
								const num = t ? (t.number || '---') : '---';
								const cat = t ? (t.category || '') : '';
								return `
									<div class="bg-black rounded-xl p-8 text-center">
										<span class="text-gray-200 text-xl">${name}</span>
										<div class="text-white text-5xl mt-4">${num}</div>
										${cat ? `<div class=\"text-gray-500 text-base mt-2 truncate\">${cat}</div>` : ''}
									</div>
								`;
							}).join('');
						} else {
							// Hard fallback: show latest ring on its counter even if status endpoint failed
							const COUNTERS = ['Counter 1', 'Counter 2', 'Counter 3', 'Backroom', 'E-Center Regular', 'E-Center Priority', 'Priority'];
							const norm = (s) => String(s || '').trim().toLowerCase();
							let fallbackStatuses = {};
							COUNTERS.forEach(n => { fallbackStatuses[norm(n)] = null; });
							try {
								const r = window.latestRing;
								const isOffline = r && ((String(r.category || '').toLowerCase() === 'offline') || /offline/i.test(String(r.number || '')));
								if (r && r.counter && !isOffline) { fallbackStatuses[norm(r.counter)] = { number: r.number, category: r.category }; }
							} catch(e) {}
							document.getElementById('displayAllCounters').innerHTML = COUNTERS.map(name => {
								const t = fallbackStatuses[norm(name)];
								const num = t ? (t.number || '---') : '---';
								const cat = t ? (t.category || '') : '';
								return `
									<div class="bg-black rounded-xl p-8 text-center">
										<span class="text-gray-200 text-xl">${name}</span>
										<div class="text-white text-5xl mt-4">${num}</div>
										${cat ? `<div class=\"text-gray-500 text-base mt-2 truncate\">${cat}</div>` : ''}
									</div>`;
							}).join('');
						}
					} catch (e) { /* ignore */ }
				} catch (e) {}
			}
			setInterval(refreshBoard, 2000); refreshBoard();

			// Cross-instance ring: poll Caller app (port 8000) to trigger updates here (port 8001)
			let lastRingTs = 0;
			let lastEventSig = '';

			function updateNowServingFromPayload(data) {
				if (!data) return;
				// Remember latest ring to help fill counters grid even if persistence lags
				try { window.latestRing = { number: String(data.number || ''), counter: String(data.counter || ''), category: String(data.category || '') }; } catch(e) {}
				const ts = data.ts || 0;
				const sig = `${data.number || ''}|${data.counter || ''}|${data.category || ''}|${ts}`;
				const isDuplicate = (sig === lastEventSig);
				lastEventSig = sig;
				lastRingTs = ts || Date.now();
				// Merge with previously known values to avoid flicker when payload is partial
				let prev = null;
				try { prev = window.lastNowServing || JSON.parse(localStorage.getItem(lsKey('last_now_serving')) || 'null'); } catch(e) { prev = null; }
				const number = (data.number && String(data.number).trim()) ? String(data.number).trim() : (prev && prev.number ? prev.number : '- - -');
				const counter = (data.counter && String(data.counter).trim()) ? String(data.counter).trim() : (prev && prev.counter ? prev.counter : '-');
				const category = (data.category && String(data.category).trim()) ? String(data.category).trim() : (prev && prev.category ? prev.category : '');
				const nowNumberEl = document.getElementById('displayNowNumber');
				if (nowNumberEl) nowNumberEl.textContent = number;
				document.getElementById('displayNowCounter').innerHTML = `<p class="text-yellow-500 text-4xl">to ${counter}</p>`;
				if (category) {
					document.getElementById('displayNowCategory').textContent = category;
					document.getElementById('displayNowCategoryWrap').classList.remove('hidden');
				} else {
					document.getElementById('displayNowCategoryWrap').classList.add('hidden');
				}

				// Make Offline message appear much smaller on the board
				const isOfflineVisual = (String(category || '').toLowerCase() === 'offline') || /offline/i.test(String(number || ''));
				if (nowNumberEl) {
					// Remove any previously set sizes
					nowNumberEl.classList.remove('text-9xl', 'text-6xl', 'text-5xl', 'text-4xl', 'text-3xl');
					if (isOfflineVisual) {
						// Use text-6xl for offline message
						nowNumberEl.classList.add('text-6xl');
					} else {
						nowNumberEl.classList.add('text-9xl');
					}
				}

				// persist last called locally for fallback display
				try { window.lastNowServing = { number, counter, category, ts: Date.now() }; localStorage.setItem(lsKey('last_now_serving'), JSON.stringify(window.lastNowServing)); } catch(e) {}

				// immediately reflect in counters grid tile (without waiting for polling) — skip Offline message
				try {
					if (!isOfflineVisual) updateCounterTile(counter, number, category);
				} catch (e) { /* ignore */ }

				// Hold Offline text on NOW SERVING for a short duration so it doesn't get replaced by queue polling
				try { if (isOfflineVisual) { window.__offlineHoldUntil = Date.now() + 8000; } } catch(_) {}

				// queue automated voice announcement (skip duplicates)
				if (!isDuplicate && window.speechSynthesis) {
					pendingAnnouncements.push({ number, counter, category });
					processAnnouncements();
				}
			}

			// Update a single counter tile by name, setting its number and category
			function updateCounterTile(counterName, num, cat) {
				if (!counterName) return;
				const norm = (s) => String(s || '').trim().toLowerCase();
				const container = document.getElementById('displayAllCounters');
				if (!container) return;
				const tiles = Array.from(container.children || []);
				for (const tile of tiles) {
					const label = tile.querySelector('span.text-gray-200');
					const numberEl = tile.querySelector('div.text-white');
					let catEl = tile.querySelector('div.text-gray-500');
					if (!label || !numberEl) continue;
					if (norm(label.textContent) === norm(counterName)) {
						numberEl.textContent = num || '---';
						if (cat) {
							if (!catEl) {
								catEl = document.createElement('div');
								catEl.className = 'text-gray-500 text-base mt-2 truncate';
								tile.appendChild(catEl);
							}
							catEl.textContent = cat;
						} else if (catEl) {
							catEl.textContent = '';
						}
						break;
					}
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
					  const isOffline = (String(next.category || '').toLowerCase() === 'offline') || /offline/i.test(numberPhrase);
					  const phrase = isOffline ? 'Sorry we are offline, please be patient' : `Calling number ${numberPhrase}, Proceed to ${counterPhrase}`;
					await speakOnce(phrase);
				} finally {
					speaking = false;
					if (pendingAnnouncements.length) processAnnouncements();
				}
			}
			async function pollRemoteRing() {
				const endpoints = [
					BRANCH ? ('/ring/last?branch=' + encodeURIComponent(BRANCH)) : '/ring/last',
					BRANCH ? ('http://127.0.0.1:8000/ring/last?branch=' + encodeURIComponent(BRANCH)) : 'http://127.0.0.1:8000/ring/last',
					BRANCH ? ('http://localhost:8000/ring/last?branch=' + encodeURIComponent(BRANCH)) : 'http://localhost:8000/ring/last'
				];
				for (const url of endpoints) {
					try {
						const res = await fetch(url, { cache: 'no-store' });
						if (!res.ok) continue;
						const data = await res.json();
						if (data && (data.number || data.counter || data.category)) {
							updateNowServingFromPayload(data);
							break;
						}
					} catch(e) { /* ignore and try next */ }
				}
			}
			setInterval(pollRemoteRing, 1500);
			pollRemoteRing();

			// Same-origin instant trigger (when board runs on 8000): BroadcastChannel + storage
			try {
				const ch = new BroadcastChannel(CHANNEL_NAME);
				ch.onmessage = (ev) => {
					const msg = (ev && ev.data) || {};
					if (msg && msg.type === 'ring') {
						updateNowServingFromPayload(msg);
					} else if (msg && (msg.type === 'queue-update' || msg.type === 'ticket-issued')) {
						refreshBoard();
					}
				};
			} catch(e) { /* ignore */ }

			window.addEventListener('storage', (e) => {
				if (e.key === lsKey('queue_ring') && e.newValue) {
					try { const payload = JSON.parse(e.newValue); updateNowServingFromPayload(payload); } catch(err) {}
				}
				if (e.key === lsKey('queue_updated')) {
					refreshBoard();
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
