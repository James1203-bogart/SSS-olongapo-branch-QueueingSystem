<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title>Queue Display Board</title>
		<script src="https://cdn.tailwindcss.com"></script>
		<link rel="stylesheet" href="{{ asset('css/caller.css') }}">
		<style>
<<<<<<< HEAD
=======
			html, body { height: 100%; width: 100%; margin: 0; }
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
			@keyframes crawler-scroll {
				0% { transform: translateX(100%); }
				100% { transform: translateX(-100%); }
			}
			.crawler-animate { animation: crawler-scroll 18s linear infinite; white-space: nowrap; }
<<<<<<< HEAD
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
=======
			/* Blink red while calling */
			@keyframes call-blink {
				0%, 100% { color: #ef4444; opacity: 1; }
				50% { color: #ef4444; opacity: 0.35; }
			}
			.calling-blink { color: #ef4444 !important; animation: call-blink 0.7s ease-in-out 3; }
		</style>
	</head>
	<body class="bg-gray-100 h-screen w-screen overflow-hidden">
		<div class="w-screen h-screen mx-auto p-0">
			<div class="mb-2">
				<h1 class="text-slate-800 text-2xl font-semibold">Queue Display Board <span id="branchLabel" class="text-slate-500 text-lg font-normal"></span></h1>
			</div>

 

			<div class="grid grid-cols-4 grid-rows-3 gap-1">
				<!-- Left: Counters (span 3 columns, multiple rows) -->
				<div id="displayAllCounters" class="col-span-3 row-span-3 grid grid-cols-3 gap-2"></div>

				<!-- Right: Time (row 1) -->
				<div class="bg-white rounded-2xl p-3 shadow-sm flex flex-col items-start justify-center">
					<div class="text-xs uppercase tracking-wider font-semibold text-gray-600 mb-1">Time</div>
					<div id="displayTime" class="text-5xl font-mono text-slate-900 leading-tight">--:--:--<br>AM</div>
				</div>

				<!-- Right: Offline Calling (row 2) -->
				<div class="bg-white rounded-2xl p-3 shadow-sm flex flex-col">
					<div class="text-xs uppercase tracking-wider font-semibold text-gray-600 mb-1">Offline Calling</div>
					<div id="offlineList" class="flex-1">
						<div class="w-full rounded-lg bg-slate-100 text-slate-600 text-center py-2">No offline calls</div>
					</div>
				</div>

				<!-- Right: Now Serving (row 3) -->
					<div class="bg-white rounded-2xl p-3 shadow-sm text-center flex flex-col items-center justify-center">
					<div class="text-xs uppercase tracking-wider font-semibold text-gray-600 mb-1">Now Serving</div>
						<div id="displayNowNumber" class="text-7xl font-bold text-slate-900 tracking-wide leading-tight">- - -</div>
						<div id="displayNowCounter" class="mt-2 text-slate-700 text-5xl font-semibold">Counter -</div>
						<div id="displayNowCategoryWrap" class="hidden mt-0 text-4xl text-gray-500"><span id="displayNowCategory"></span></div>
				</div>
			</div>

			<!-- Crawler at bottom -->
					<div class="mt-3 bg-slate-900 rounded-2xl overflow-hidden">
						<div class="py-3 overflow-hidden">
						<div id="displayCrawler" class="text-yellow-400 text-6xl md:text-7xl lg:text-8xl font-bold px-6 whitespace-nowrap crawler-animate">Hello</div>
				</div>
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
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
<<<<<<< HEAD
=======

			// Voice overrides via URL/localStorage for cross-browser consistency
			function getVoiceOverrideName() {
				try {
					const qp = new URLSearchParams(window.location.search);
					const qv = qp.get('voice') || qp.get('voiceName') || '';
					if (qv) {
						try { localStorage.setItem(lsKey('voice_name'), qv); } catch(_) {}
						return qv;
					}
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
			// Offline voice: always enabled (no UI or query param needed)
			function isSpeakOfflineEnabled(){ return true; }
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
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
<<<<<<< HEAD
			window.addEventListener('storage', (e) => { if (e.key === lsKey('crawler_text')) setCrawlerFromStorage(); });
			window.addEventListener('DOMContentLoaded', setCrawlerFromStorage);
=======
			// Server-backed crawler fetch for cross-browser sync
			let lastCrawlerFetch = 0;
			async function fetchCrawlerFromServer(force){
				const now = Date.now();
				if (!force && now - lastCrawlerFetch < 12000) return;
				lastCrawlerFetch = now;
				try {
					const url = BRANCH ? ('/crawler?branch=' + encodeURIComponent(BRANCH)) : '/crawler';
					const res = await fetch(url, { cache: 'no-store' });
					if (!res.ok) return;
					const data = await res.json();
					const text = (data && data.text) ? String(data.text) : 'Hello';
					try { localStorage.setItem(lsKey('crawler_text'), text); } catch(e) {}
					setCrawlerFromStorage();
				} catch(e) {}
			}
			window.addEventListener('storage', (e) => {
				if (e.key === lsKey('crawler_text')) setCrawlerFromStorage();
				if (e.key === lsKey('queue_updated')) fetchCrawlerFromServer(true);
			});
			window.addEventListener('DOMContentLoaded', () => { setCrawlerFromStorage(); fetchCrawlerFromServer(true); });
			setInterval(() => fetchCrawlerFromServer(false), 15000);
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
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
<<<<<<< HEAD
					const COUNTERS = ['Counter 1', 'Counter 2', 'Counter 3', 'Backroom', 'E-Center Regular', 'E-Center Priority', 'Priority'];
					el.innerHTML = COUNTERS.map(name => `
						<div class="bg-black rounded-xl p-8 text-center">
							<span class="text-gray-200 text-xl">${name}</span>
							<div class="text-white text-5xl mt-4">---</div>
=======
					const COUNTERS = ['Counter 1', 'Counter 2', 'Counter 3', 'Backroom', 'Medical', 'E-Center Regular', 'E-Center Priority', 'Priority'];
					el.innerHTML = COUNTERS.map(name => `
						<div class="bg-white rounded-2xl p-3 shadow-sm text-center" data-name="${String(name).trim().toLowerCase()}">
							<div class="text-2xl uppercase tracking-wider font-semibold text-gray-600">${name}</div>
							<div class="qdb-num text-8xl font-bold text-slate-900 mt-0 leading-tight">---</div>
							<div class="qdb-cat text-3xl text-gray-500 mt-0 font-medium"></div>
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
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
					// Clear old now serving data on reset
					try { localStorage.removeItem(lsKey('last_now_serving')); } catch(e) {}
					// No initial load from localStorage
<<<<<<< HEAD
=======
					// Lock default voice to "Michael" for this branch; set human pitch and slower rate unless URL overrides are present
					try {
						const qp = new URLSearchParams(window.location.search);
						const hasOverride = qp.has('voice') || qp.has('voiceName');
						if (!hasOverride) {
							const cur = localStorage.getItem(lsKey('voice_name')) || '';
							if (!cur || cur.toLowerCase() !== 'michael') {
								localStorage.setItem(lsKey('voice_name'), 'Michael');
							}
						}
						if (!localStorage.getItem(lsKey('voice_pitch'))) {
							localStorage.setItem(lsKey('voice_pitch'), '1.0'); // natural human pitch
						}
						if (!localStorage.getItem(lsKey('voice_rate'))) {
							localStorage.setItem(lsKey('voice_rate'), '0.85'); // slower default rate
						}
					} catch(_) {}
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
				} catch(e) {}
			});

			// Fetch queue data and update visuals
			const USE_QUEUE_FOR_NOW_SERVING = true; // temporary: use queue data to verify pipeline
			async function refreshBoard(){
				try {
					// TODO: Replace with production endpoint for queue data
					const tickets = [];

					// Waiting card removed

<<<<<<< HEAD
					// Now Serving (optional from queue) — disabled to avoid flicker; ring/local storage drives NOW SERVING
					if (USE_QUEUE_FOR_NOW_SERVING) {
=======
					// Now Serving (optional from queue) — skip while offline to avoid flicker
					if (USE_QUEUE_FOR_NOW_SERVING && !window.__isOfflineState) {
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
						const serving = tickets.find(t => t.status === 'serving');
						let current = null;
						if (serving) {
							current = { number: serving.number || '- - -', counter: serving.counter || '-', category: serving.category || '' };
							try { window.lastNowServing = { ...current, ts: Date.now() }; localStorage.setItem(lsKey('last_now_serving'), JSON.stringify(window.lastNowServing)); } catch(e) {}
						} else {
							try { current = window.lastNowServing || JSON.parse(localStorage.getItem(lsKey('last_now_serving')) || 'null'); } catch(e) { current = null; }
							// If no serving ticket and no current, clear localStorage to reset
							if (!current) {
								try { localStorage.removeItem(lsKey('last_now_serving')); } catch(e) {}
							}
						}

						const num = current && current.number ? current.number : '- - -';
						const ctr = current && current.counter ? current.counter : '-';
						const cat = current && current.category ? current.category : '';
<<<<<<< HEAD
						document.getElementById('displayNowNumber').textContent = num;
						document.getElementById('displayNowCounter').innerHTML = `<p class="text-yellow-500 text-4xl">to ${ctr}</p>`;
						if (cat) {
							document.getElementById('displayNowCategory').textContent = cat;
							document.getElementById('displayNowCategoryWrap').classList.remove('hidden');
						} else {
							document.getElementById('displayNowCategoryWrap').classList.add('hidden');
						}
					}

					// All Counters Status via dedicated endpoint (more reliable than scanning tickets)
=======
						// If Offline, route to Offline Calling card; otherwise update Now Serving
						const isOfflineVisual = (String(cat || '').toLowerCase() === 'offline') || /offline/i.test(String(num || ''));
						if (isOfflineVisual) {
							// mark offline state so other loops won't clear the card
							try { window.__isOfflineState = true; } catch(_) {}
							const offlineList = document.getElementById('offlineList');
							if (offlineList) {
								offlineList.innerHTML = `
									<div class="text-center">
										<div class="text-6xl font-bold text-slate-900 tracking-wide">OFFLINE</div>
									</div>
								`;
							}
							// Do not overwrite Now Serving during offline
						} else {
							try { window.__isOfflineState = false; } catch(_) {}
							document.getElementById('displayNowNumber').textContent = num;
							// ensure larger size for Now Serving
							try {
								const el = document.getElementById('displayNowNumber');
								el.classList.remove('text-9xl', 'text-8xl', 'text-7xl', 'text-6xl', 'text-5xl');
								el.classList.add('text-9xl');
							} catch(_) {}
							document.getElementById('displayNowCounter').textContent = `Counter ${ctr.toString().replace(/^counter\s*/i,'')}`;
							if (cat) {
								document.getElementById('displayNowCategory').textContent = cat;
								document.getElementById('displayNowCategoryWrap').classList.remove('hidden');
							} else {
								document.getElementById('displayNowCategoryWrap').classList.add('hidden');
							}
							// Clear Offline card when back to normal
							const offlineList = document.getElementById('offlineList');
									if (offlineList) {
										offlineList.innerHTML = '<div class="w-full rounded-lg bg-slate-100 text-slate-600 text-center py-2">No offline calls</div>';
							}
						}
					}

					// All Counters Status via dedicated endpoint (update existing tiles only; do not add new sections)
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
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
							// Merge in latest ring payload on the client to ensure the called counter shows immediately
							try {
								const r = window.latestRing;
								const isOffline = r && (String(r.category||'').toLowerCase()==='offline' || /offline/i.test(String(r.number||'')));
								if (r && r.counter && !isOffline) {
									statuses[norm(r.counter)] = { number: r.number, category: r.category };
								}
							} catch(e) {}
<<<<<<< HEAD
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
=======
							const container = document.getElementById('displayAllCounters');
							if (container) {
								const tiles = Array.from(container.querySelectorAll('[data-name]'));
								tiles.forEach(tile => {
									const key = tile.getAttribute('data-name');
									const t = statuses[key];
									const numEl = tile.querySelector('.qdb-num');
									const catEl = tile.querySelector('.qdb-cat');
									if (numEl) numEl.textContent = t ? (t.number || '---') : '---';
									if (catEl) catEl.textContent = t ? (t.category || '') : '';
								});
							}
						} else {
							// Hard fallback: show latest ring on its counter even if status endpoint failed
							const COUNTERS = ['Counter 1', 'Counter 2', 'Counter 3', 'Backroom', 'Medical', 'E-Center Regular', 'E-Center Priority', 'Priority'];
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
							const norm = (s) => String(s || '').trim().toLowerCase();
							let fallbackStatuses = {};
							COUNTERS.forEach(n => { fallbackStatuses[norm(n)] = null; });
							try {
								const r = window.latestRing;
								const isOffline = r && (String(r.category||'').toLowerCase()==='offline' || /offline/i.test(String(r.number||'')));
								if (r && r.counter && !isOffline) { fallbackStatuses[norm(r.counter)] = { number: r.number, category: r.category }; }
							} catch(e) {}
<<<<<<< HEAD
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
=======
							const container = document.getElementById('displayAllCounters');
							if (container) {
								const tiles = Array.from(container.querySelectorAll('[data-name]'));
								tiles.forEach(tile => {
									const key = tile.getAttribute('data-name');
									const t = fallbackStatuses[key];
									const numEl = tile.querySelector('.qdb-num');
									const catEl = tile.querySelector('.qdb-cat');
									if (numEl) numEl.textContent = t ? (t.number || '---') : '---';
									if (catEl) catEl.textContent = t ? (t.category || '') : '';
								});
							}
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
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
<<<<<<< HEAD
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

				// immediately reflect in counters grid tile (without waiting for polling)
				try {
					updateCounterTile(counter, number, category);
				} catch (e) { /* ignore */ }

				// queue automated voice announcement (skip duplicates)
				if (!isDuplicate && window.speechSynthesis) {
=======
				
				// Check if this is an offline call
				const isOfflineVisual = (String(category || '').toLowerCase() === 'offline') || /offline/i.test(String(number || ''));
				
				// If offline, show in Offline Calling section instead of Now Serving
				if (isOfflineVisual) {
					// mark offline state
					try { window.__isOfflineState = true; } catch(_) {}
					const offlineList = document.getElementById('offlineList');
					if (offlineList) {
						offlineList.innerHTML = `
							<div class="text-center">
								<div class="text-6xl font-bold text-slate-900 tracking-wide">OFFLINE</div>
							</div>
						`;
					}
					// Auto-clear Offline message after a configurable delay
					try {
						const msRaw = localStorage.getItem(lsKey('offline_auto_clear_ms')) || '';
						const ms = parseInt(msRaw, 10) || 30000; // default 30s
						if (window.__offlineClearTimer) clearTimeout(window.__offlineClearTimer);
						window.__offlineClearTimer = setTimeout(() => {
							const ol = document.getElementById('offlineList');
										if (ol) {
											ol.innerHTML = '<div class="w-full rounded-lg bg-slate-100 text-slate-600 text-center py-2">No offline calls</div>';
							}
						}, ms);
					} catch(e) {}
					// Keep NOW SERVING showing the previous non-offline ticket (or default)
					// Don't update Now Serving section for offline calls
				} else {
					// clear offline state
					try { window.__isOfflineState = false; } catch(_) {}
					// Regular call - update Now Serving section
					const nowNumberEl = document.getElementById('displayNowNumber');
					if (nowNumberEl) {
						nowNumberEl.textContent = number;
						// Enlarge and blink red briefly when calling
						nowNumberEl.classList.remove('text-9xl', 'text-8xl', 'text-7xl', 'text-6xl', 'text-5xl', 'text-4xl', 'text-3xl');
						nowNumberEl.classList.add('text-9xl');
						startBlink(nowNumberEl);
					}
					document.getElementById('displayNowCounter').textContent = `Counter ${counter.replace(/^counter\s*/i,'')}`;
					if (category) {
						document.getElementById('displayNowCategory').textContent = category;
						document.getElementById('displayNowCategoryWrap').classList.remove('hidden');
					} else {
						document.getElementById('displayNowCategoryWrap').classList.add('hidden');
					}
					// Clear offline list when serving normally
							const offlineList = document.getElementById('offlineList');
							if (offlineList) {
								offlineList.innerHTML = '<div class="w-full rounded-lg bg-slate-100 text-slate-600 text-center py-3">No offline calls</div>';
					}
				}

				// Persist last called locally for fallback display (skip Offline so Now Serving remains a regular ticket)
				try {
					const isOfflinePersist = (String(category || '').toLowerCase() === 'offline') || /offline/i.test(String(number || ''));
					if (!isOfflinePersist) {
						window.lastNowServing = { number, counter, category, ts: Date.now() };
						localStorage.setItem(lsKey('last_now_serving'), JSON.stringify(window.lastNowServing));
					}
				} catch(e) {}

				// immediately reflect in counters grid tile (without waiting for polling)
				// Skip updating tiles for Offline announcements
				if (!isOfflineVisual) {
					try { updateCounterTile(counter, number, category); } catch (e) { /* ignore */ }
					// Blink the specific counter tile number briefly
					try {
						const norm = (s) => String(s || '').trim().toLowerCase();
						const container = document.getElementById('displayAllCounters');
						const tile = container ? container.querySelector(`[data-name="${norm(counter)}"]`) : null;
						const numEl = tile ? tile.querySelector('.qdb-num') : null;
							if (numEl) { startBlink(numEl); }
					} catch(_) {}
				}

				// queue automated voice announcement (skip duplicates; allow Offline if enabled)
				if (!isDuplicate && window.speechSynthesis && (!isOfflineVisual || isSpeakOfflineEnabled())) {
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
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
<<<<<<< HEAD
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
=======
				const tile = container.querySelector(`[data-name="${norm(counterName)}"]`);
				if (!tile) return;
				const numberEl = tile.querySelector('.qdb-num');
				const catEl = tile.querySelector('.qdb-cat');
				if (numberEl) numberEl.textContent = num || '---';
				if (catEl) catEl.textContent = cat || '';
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
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
<<<<<<< HEAD
			function pickPreferredVoice() {
				try {
					const vs = window.speechSynthesis.getVoices() || [];
					preferredVoice = vs.find(v => v.name.includes('Google US English'))
						|| vs.find(v => v.name.includes('Samantha'))
						|| vs.find(v => v.name.includes('Alex'))
						|| vs.find(v => v.name.includes('Victoria'))
						|| vs.find(v => (v.lang || '').startsWith('en'))
						|| vs[0] || null;
=======
			function isEdgeBrowser(){ try { return /\bEdg\//.test(navigator.userAgent || ''); } catch(_) { return false; } }
			function pickPreferredVoice() {
				try {
					const vs = window.speechSynthesis.getVoices() || [];
					const overrideName = (getVoiceOverrideName() || '').toLowerCase().trim();
					if (overrideName) {
						preferredVoice = vs.find(v => v.name.toLowerCase().includes(overrideName)) || null;
						if (preferredVoice) return; // honor override if found
					}
					// Explicitly prefer "Michael" if available
					const preferName = (name) => vs.find(v => v.name.toLowerCase().includes(String(name).toLowerCase()));
					preferredVoice = preferName('michael');
					if (preferredVoice) {
						// Persist Edge's voice choice for cross-browser consistency
						try { if (isEdgeBrowser() && !overrideName) {
							localStorage.setItem(lsKey('voice_name'), preferredVoice.name);
							if (preferredVoice.lang) localStorage.setItem(lsKey('voice_lang'), preferredVoice.lang);
						}} catch(_) {}
						return;
					}
					// Prefer male/deeper voices across browsers
					const prefer = (regex) => vs.find(v => regex.test(v.name));
					preferredVoice = prefer(/guy/i)
						|| prefer(/male/i)
						|| prefer(/google uk english male/i)
						|| prefer(/alex/i)
						|| prefer(/daniel/i)
						|| prefer(/george/i)
						|| prefer(/brian/i)
						|| prefer(/matt/i)
						|| prefer(/james/i)
						|| prefer(/john/i)
						|| prefer(/david/i)
						|| vs.find(v => /Microsoft/i.test(v.name) && /^en/.test(v.lang || ''))
						|| vs.find(v => /Google/i.test(v.name) && /^en/.test(v.lang || ''))
						|| vs.find(v => (v.lang || '').startsWith('en'))
						|| vs[0] || null;
					// Persist Edge's selected voice if no explicit override
					try { if (preferredVoice && isEdgeBrowser() && !overrideName) {
						localStorage.setItem(lsKey('voice_name'), preferredVoice.name);
						if (preferredVoice.lang) localStorage.setItem(lsKey('voice_lang'), preferredVoice.lang);
					}} catch(_) {}
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
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

<<<<<<< HEAD
=======
			// Convert integers to words (British style with "and") up to 999,999
			function numberToWords(num) {
				try {
					const n = parseInt(num, 10);
					if (!isFinite(n)) return String(num);
					if (n === 0) return 'zero';
					const ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
					const teens = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
					const tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

					function under100(x) {
						if (x < 10) return ones[x];
						if (x < 20) return teens[x - 10];
						const t = Math.floor(x / 10);
						const o = x % 10;
						return o ? `${tens[t]} ${ones[o]}` : `${tens[t]}`;
					}

					function under1000(x) {
						const h = Math.floor(x / 100);
						const r = x % 100;
						// US-style for hundreds: no "and" inside the hundred block
						if (h && r) return `${ones[h]} hundred ${under100(r)}`;
						if (h && !r) return `${ones[h]} hundred`;
						return under100(r);
					}

					const thousands = Math.floor(n / 1000);
					const rem = n % 1000;
					if (!thousands) return under1000(n);
					const left = `${under1000(thousands)} thousand`;
					if (!rem) return left;
					// Requested style: always include "and" after the thousands part
					if (rem < 100) return `${left} and ${under100(rem)}`;
					return `${left} and ${under1000(rem)}`;
				} catch (_) { return String(num); }
			}

			// Replace each digit block in a ticket string with words
			function numberStringToWords(raw) {
				try {
					return String(raw || '').replace(/\d+/g, (m) => numberToWords(m));
				} catch (_) { return String(raw || ''); }
			}

>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
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
<<<<<<< HEAD
						u.rate = 1; u.pitch = 1; u.volume = 1;
						u.lang = 'en-US';
=======
						const rateOverride = getRateOverride();
						const pitchOverride = getPitchOverride();
						u.rate = (rateOverride && isFinite(rateOverride)) ? rateOverride : 0.85; // slower default
						u.pitch = (pitchOverride && isFinite(pitchOverride)) ? pitchOverride : 1.0; // natural human pitch default
						u.volume = 1;
						const langOverride = getVoiceOverrideLang();
						u.lang = langOverride || (preferredVoice && preferredVoice.lang) || 'en-GB';
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
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
<<<<<<< HEAD
      const speakDigits = false; // digits toggle removed
      const ANNOUNCE_DELAY_MS = 3000; // pause before speaking next number

      function delay(ms){ return new Promise(res => setTimeout(res, ms)); }
=======
			const speakDigits = false; // use word-based pronunciation (e.g., "one thousand and three hundred four")
      const ANNOUNCE_DELAY_MS = 3000; // pause before speaking next number

			function delay(ms){ return new Promise(res => setTimeout(res, ms)); }
			// Helper to (re)start red blink and stop exactly after CSS iterations
			function startBlink(el){
				try {
					if (!el) return;
					// Restart animation if already running
					el.classList.remove('calling-blink');
					void el.offsetWidth;
					el.classList.add('calling-blink');
					const onEnd = () => {
						try { el.classList.remove('calling-blink'); } catch(_) {}
						try { el.removeEventListener('animationend', onEnd); } catch(_) {}
					};
					el.addEventListener('animationend', onEnd, { once: true });
				} catch(_) {}
			}
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
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
<<<<<<< HEAD
					const rawNumber = String(next.number || '').trim();
					const numberPhrase = speakDigits ? spellDigits(rawNumber) : rawNumber;
					const counterPhrase = (String(next.counter || '')).replace(/^counter\s*/i, 'Counter ');
					  const isOffline = (String(next.category || '').toLowerCase() === 'offline') || /offline/i.test(numberPhrase);
=======
						const rawNumber = String(next.number || '').trim();
						const numberPhrase = speakDigits ? spellDigits(rawNumber) : numberStringToWords(rawNumber);
						const counterPhrase = (String(next.counter || '')).replace(/^counter\s*/i, 'Counter ');
					  const isOffline = (String(next.category || '').toLowerCase() === 'offline') || /offline/i.test(rawNumber);
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
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
<<<<<<< HEAD
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
=======
			const ONLY_SERVER_SYNC = true; // enforce server-based sync across all browsers
			if (!ONLY_SERVER_SYNC) {
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
			}

			// Always listen for server refresh hints via localStorage to speed up updates across browsers
			window.addEventListener('storage', (e) => {
				if (e.key === lsKey('queue_updated')) {
					refreshBoard();
				}
				if (!ONLY_SERVER_SYNC && e.key === lsKey('queue_ring') && e.newValue) {
					try { const payload = JSON.parse(e.newValue); updateNowServingFromPayload(payload); } catch(err) {}
				}
>>>>>>> 6af1558 (Initial commit: SSS Olongapo Branch Queueing System)
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
