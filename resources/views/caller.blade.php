<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Caller</title>
    <!-- Tailwind CDN for quick prototyping (class names in the provided design) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/caller.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
      @keyframes crawlerMove {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
      }
      .crawler-animate { animation: crawlerMove 30s linear infinite; }
    </style>
  </head>
  <body class="bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto p-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <button onclick="location.href='{{ url('/') }}'" class="flex items-center gap-2 text-gray-700 hover:text-gray-900 transition-colors">
          <!-- simple left arrow -->
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
          Back to Selection
        </button>

        <div class="flex items-center gap-3">
          <div class="px-4 py-2 rounded-lg bg-orange-500 text-white">Caller</div>
          <!-- Add Category (Plus) Button -->
          <button id="addCategoryBtn" title="Add Category" class="flex items-center justify-center w-10 h-10 rounded-lg bg-orange-100 text-orange-600 hover:bg-orange-200 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16M4 12h16"/></svg>
          </button>
          <!-- Remove Category Controls -->
          <div class="hidden md:flex items-center gap-2">
            <select id="removeCategorySelect" class="px-3 py-2 border rounded-lg text-sm">
              <option value="">Select category to remove</option>
            </select>
            <button id="removeCategoryBtn" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">Remove</button>
            <button id="crawlerTextBtn" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Crawler Text</button>
          </div>
        </div>
      </div>
      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Left Panel -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Counter Selection -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-gray-900 mb-4">Counter Number</h2>
            <select id="currentCounter" class="w-full px-4 py-3 border-2 border-orange-300 rounded-lg text-gray-900 text-center text-2xl focus:outline-none focus:border-orange-500">
              @foreach($counters as $c)
                <option value="{{ $c }}" @if(isset($assigned) && $assigned === $c) selected @endif>{{ $c }}</option>
              @endforeach
            </select>
            <div class="mt-4 flex items-center gap-3">
              <button id="addCounterBtn" class="px-4 py-2 rounded-lg bg-orange-100 text-orange-700 hover:bg-orange-200 border border-orange-300">Add Counter</button>
              <button id="removeCounterBtn" class="px-4 py-2 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 border border-red-300">Remove Counter</button>
            </div>
          </div>
          <!-- Category Filter (single selection) -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-gray-900 mb-4">Handle Category</h2>
            <select id="categorySelect" class="w-full px-4 py-3 border-2 border-orange-300 rounded-lg text-gray-900 focus:outline-none focus:border-orange-500">
              <option value="">Select one category</option>
            </select>
          </div>

          <!-- Currently Serving -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-gray-900 mb-4">Currently Serving at <span id="currentCounterLabel">{{ isset($assigned) && $assigned ? $assigned : ($counters[0] ?? '') }}</span></h2>
            <div class="text-center py-8" id="currentlyServing">
              <div class="text-gray-400 text-4xl">No Active Call</div>
            </div>
          </div>

          <!-- Call Next -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <button id="callNextBtn" class="w-full bg-green-500 text-white py-6 rounded-lg hover:bg-green-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-xl">Call Next Customer</button>
            <button id="callAgainBtn" class="w-full bg-amber-500 text-white py-4 mt-4 rounded-lg hover:bg-amber-600 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed text-xl">Call Again</button>
            <button id="offlineBtn" class="w-full bg-gray-600 text-white py-4 mt-4 rounded-lg hover:bg-gray-700 transition-colors text-xl">Sorry we are offline, please be patient</button>
            <p class="text-center text-gray-600 mt-4"><span id="waitingCount">0</span> customer(s) waiting</p>
            <p id="categoryWarning" class="text-center text-red-500 text-sm mt-2 hidden">Select at least one category</p>
          </div>

          <!-- Call Specific Number -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center gap-2">
              <input id="specificNumberInput" type="text" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:border-gray-500" placeholder="Enter ticket number or id" />
              <button id="callSpecificBtn" class="bg-blue-500 text-white py-3 px-4 rounded-lg hover:bg-blue-600 transition-colors">Call</button>
            </div>
            <p class="text-sm text-gray-500 mt-2">You may enter the ticket number (visible on ticket) or internal id.</p>
          </div>

          

          <!-- View Waiting Categories Button -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <button id="viewWaitingCategoriesBtn" class="w-full bg-indigo-500 text-white py-4 rounded-lg hover:bg-indigo-600 transition-colors flex items-center justify-center gap-2">
              <!-- simple list icon -->
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
              View Waiting Categories
            </button>
          </div>

          <!-- Reports Button -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <button id="viewReportsBtn" class="w-full bg-purple-600 text-white py-4 rounded-lg hover:bg-purple-700 transition-colors flex items-center justify-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h8M9 17a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10h10a2 2 0 002-2V9a2 2 0 00-2-2h-8"></path></svg>
              Reports
            </button>
          </div>

          <!-- Queue Stats -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-gray-900 mb-4">Queue Statistics</h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                <span class="text-gray-700">Priority Waiting</span>
                <span id="priorityWaiting" class="text-red-600">0</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                <span class="text-gray-700">Regular Waiting</span>
                <span id="regularWaiting" class="text-blue-600">0</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-gray-700">Total Served</span>
                <span id="totalServed" class="text-gray-600">0</span>
              </div>
            </div>
            <script>
            async function fetchQueueStats() {
                try {
                // TODO: Replace with production endpoint for queue stats
                // Example: fetch('/api/queue-stats')
                } catch (e) {}
            }
            setInterval(fetchQueueStats, 2000);
            fetchQueueStats();
            </script>
          </div>
        </div>

        <!-- Right Panel - Queue List -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
              <svg class="w-7 h-7 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h8M9 17a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10h10a2 2 0 002-2V9a2 2 0 00-2-2h-8"></path></svg>
              Queue Status
            </h2>
            <div id="queueList" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 max-h-[600px] overflow-y-auto">
              <!-- Tickets will be rendered here by JS -->
              <div class="text-center py-12 text-gray-400">No tickets in queue yet</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Display Board Modal/Panel -->
      <div id="displayBoard" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-6 hidden">
             <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-3xl p-12 shadow-2xl border-4 border-yellow-500 relative"
               style="width: 1311px; height: 700px; max-width: 100vw; max-height: 100vh; display: flex; flex-direction: column; justify-content: flex-start;">
          <button id="closeDisplay" class="absolute top-4 right-4 text-gray-300">Close</button>
          <div class="text-center mb-8">
            <h1 class="text-white mb-2 text-2xl">Queue Display Board</h1>
            <div class="h-1 w-32 bg-yellow-500 mx-auto rounded-full"></div>
          </div>

          <div class="flex gap-8 mb-8">
            <div class="flex-1 bg-black rounded-2xl p-12 flex flex-col justify-center">
              <p class="text-yellow-500 text-center mb-4">NOW SERVING</p>
              <div id="displayNowBlock" class="text-center">
                <div id="displayNowNumber" class="text-white text-9xl text-center tracking-wider animate-pulse">- - -</div>
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
            <div class="flex flex-col gap-6 justify-center w-64">
              <div class="bg-gray-800 rounded-xl p-6 text-center">
                <div class="flex items-center justify-center gap-2 mb-2">
                  <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M13 7a3 3 0 11-6 0 3 3 0 016 0zM2 14s1-4 8-4 8 4 8 4v1H2v-1z"/></svg>
                  <p class="text-gray-300">Waiting</p>
                </div>
                <p id="displayWaitingCount" class="text-yellow-500 text-5xl">0</p>
              </div>
              <div class="bg-gray-800 rounded-xl p-6 text-center">
                <p class="text-gray-300 mb-2">Time</p>
                <p id="displayTime" class="text-yellow-500 text-5xl">--:--:--</p>
              </div>
            </div>
          </div>

          <div class="bg-gray-800 rounded-xl p-6 mb-6">
            <h2 class="text-yellow-500 text-center text-3xl mb-4">All Counters Status</h2>
            <div id="displayAllCounters" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4"></div>
          </div>

          <!-- Crawler below All Counters Status (fixed at bottom of board) -->
          <div class="absolute left-0 right-0 bottom-0 px-6 pb-4">
            <div class="bg-black/90 rounded-xl p-5 overflow-hidden border border-yellow-500/40">
              <div id="displayCrawler" class="text-yellow-400 text-4xl font-semibold whitespace-nowrap crawler-animate">Hello</div>
            </div>
          </div>

          <!-- Removed Waiting and Time panels below the Queue Display Board -->
            <!-- Waiting panel removed as requested -->

            <!-- Time panel removed as requested -->
          </div>
        </div>
      </div>
      
      <!-- Waiting Categories Modal -->
      <div id="waitingCategoriesModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-6 hidden">
        <div class="bg-white rounded-xl w-full max-w-md p-6 relative">
          <button id="closeWaitingCategories" class="absolute top-4 right-4 text-gray-600">Close</button>
          <h3 class="text-xl font-semibold mb-4">Waiting Categories</h3>
          <div id="waitingCategoriesList" class="space-y-2 max-h-64 overflow-y-auto">
            <div class="text-gray-400">No waiting categories</div>
          </div>
        </div>
      </div>

      <!-- Crawler Text Modal -->
      <div id="crawlerTextModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-6">
        <div class="bg-white rounded-xl w-full max-w-lg p-6 relative">
          <button id="closeCrawlerText" class="absolute top-4 right-4 text-gray-600">Close</button>
          <h3 class="text-xl font-semibold mb-4">Set Crawler Text</h3>
          <input id="crawlerTextInput" type="text" class="w-full border rounded-lg p-3" placeholder="Enter crawler text">
          <div class="mt-4 flex justify-end gap-2">
            <button id="saveCrawlerText" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
          </div>
        </div>
      </div>

      <!-- Reports Modal -->
      <div id="reportsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-6 hidden">
        <div class="bg-white rounded-xl w-full max-w-4xl p-6 relative">
          <h3 class="text-xl font-semibold mb-4">Reports</h3>
          <div class="flex items-center gap-3 mb-4">
            <button id="reportsTabAll" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-800">All Transactions</button>
            <button id="exportAllCsvBtn" class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">Export CSV</button>
            <button id="reportsTabHour" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700">Transactions per Hour</button>
            <button id="exportHourCsvBtn" class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">Export CSV</button>
            <!-- Place Close beside Transactions per Hour -->
            <button id="closeReports" class="ml-auto px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300">Close</button>
          </div>
          <div id="reportsAll" class="block">
            <div class="overflow-auto max-h-[420px] border rounded">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-50 sticky top-0">
                  <tr>
                    <th class="px-3 py-2 text-left">Time</th>
                    <th class="px-3 py-2 text-left">Number</th>
                    <th class="px-3 py-2 text-left">Category</th>
                    <th class="px-3 py-2 text-left">Priority</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Counter</th>
                  </tr>
                </thead>
                <tbody id="reportsAllBody"></tbody>
              </table>
            </div>
          </div>
          <div id="reportsHour" class="hidden">
            <div class="overflow-auto max-h-[420px] border rounded">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-50 sticky top-0">
                  <tr>
                    <th class="px-3 py-2 text-left">Hour</th>
                    <th class="px-3 py-2 text-left">Total Transactions</th>
                  </tr>
                </thead>
                <tbody id="reportsHourBody"></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Category Modal -->
      <div id="addCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-6 hidden">
        <div class="bg-white rounded-xl w-full max-w-md p-6 relative">
          <button id="closeAddCategory" class="absolute top-4 right-4 text-gray-600">Close</button>
          <h3 class="text-xl font-semibold mb-4">Add Transaction Category</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm text-gray-700 mb-1">Category Name</label>
              <input id="addCatName" type="text" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-gray-400" placeholder="e.g., Verification" />
            </div>
            <div>
              <label class="block text-sm text-gray-700 mb-1">Type</label>
              <select id="addCatPriority" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-gray-400">
                <option value="regular">Regular</option>
                <option value="priority">Priority</option>
              </select>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
              <button id="saveAddCategory" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">Save</button>
            </div>
            <p id="addCatError" class="text-sm text-red-600 hidden">Please enter a name and select a type.</p>
          </div>
        </div>
      </div>
          <!-- Generate New Number Modal -->
          <div id="generateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-6">
            <div class="bg-white rounded-xl w-full max-w-md p-6 relative">
              <button id="closeGenerateModal" class="absolute top-4 right-4 text-gray-600">Close</button>
              <h3 class="text-xl font-semibold mb-4">Generate New Number</h3>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm text-gray-700 mb-1">Current Number</label>
                  <input id="generateCurrentNumber" type="text" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-gray-50 text-gray-700" readonly />
                </div>
                <div>
                  <label class="block text-sm text-gray-700 mb-1">New Number</label>
                  <input id="generateNewNumber" type="text" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-gray-50 text-gray-700" readonly />
                </div>
                <div>
                  <label class="block text-sm text-gray-700 mb-1">Transaction Type</label>
                  <select id="generateCategorySelect" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-gray-400"></select>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                  <button id="generateSaveBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">Generate</button>
                </div>
                <p id="generateError" class="text-sm text-red-600 hidden">Please fill in all fields.</p>
              </div>
            </div>
          </div>
    </div>

    <script>
            // Resolve active branch from /branch/{slug}/caller or ?branch=...
            const BRANCH = (() => {
        const qs = new URLSearchParams(location.search).get('branch');
        if (qs) return qs;
        const m = location.pathname.match(/\/branch\/([^\/]+)\//);
        if (m) return m[1];
        try { const saved = localStorage.getItem('lastBranchSlug'); if (saved) return saved; } catch(e) {}
        return null;
            })();
      // Branch-scoped storage/channel helpers
      const lsKey = (base) => BRANCH ? `${base}:${BRANCH}` : base;
      const CHANNEL_NAME = lsKey('queue-events');
      // initial data from DB for immediate display (tickets from server)
       const initialTickets = @json($tickets ?? []);
  const CATEGORIES = @json($categories ?? []);
  const ALL_COUNTERS = (() => {
    const counters = @json($counters ?? []);
    return counters.includes('Medical') ? counters : [...counters, 'Medical'];
  })();
  // server-calculated next numbers per category (id => nextNumber)
  const CATEGORY_COUNTERS = {};
        // Persist chosen branch for issuer pages to read when opened without /branch
        if (BRANCH) { try { localStorage.setItem('lastBranchSlug', BRANCH); } catch (e) {} }

    let tickets = initialTickets;
  let selectedCategories = []; // single selection stored as [id] or []
  let lastCalledTicket = null; // temporary holder for immediate UI update after callNext
  let lastCallAgainTs = 0; // cooldown tracker for Call Again

      const categorySelect = document.getElementById('categorySelect');
      const waitingCountEl = document.getElementById('waitingCount');
      const priorityWaitingEl = document.getElementById('priorityWaiting');
      const regularWaitingEl = document.getElementById('regularWaiting');
      const totalServedEl = document.getElementById('totalServed');
      const queueList = document.getElementById('queueList');
      const currentlyServing = document.getElementById('currentlyServing');
      const callNextBtn = document.getElementById('callNextBtn');
        const callAgainBtn = document.getElementById('callAgainBtn');
  const currentCounterSelect = document.getElementById('currentCounter');
  const currentCounterLabelEl = document.getElementById('currentCounterLabel');

      // --- Dynamic Counter Add/Remove helpers ---
      function getNumericCounterIndex(name) {
        const m = String(name || '').match(/^Counter\s+(\d+)$/i);
        return m ? parseInt(m[1], 10) : null;
      }

      function getCurrentCounterNames() {
        const names = [];
        if (!currentCounterSelect) return names;
        for (let i = 0; i < currentCounterSelect.options.length; i++) {
          names.push(currentCounterSelect.options[i].value);
        }
        return names;
      }

      async function persistAddedCounters(names) {
        // Persist to server so all browsers see the same list
        try {
          await fetch('/counters/list' + (BRANCH ? `?branch=${encodeURIComponent(BRANCH)}` : ''), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ names, branch: BRANCH })
          });
        } catch(e) { /* ignore */ }
        // Also persist locally as a fallback
        try { localStorage.setItem(lsKey('added_counters'), JSON.stringify(names)); } catch(e) {}
        // Broadcast update so boards refresh immediately
        try { const ch = new BroadcastChannel(CHANNEL_NAME); ch.postMessage({ type: 'queue-update', branch: BRANCH }); } catch(e) {}
        try { localStorage.setItem(lsKey('queue_updated'), String(Date.now())); } catch(e) {}
      }

      async function loadAddedCounters() {
        // Prefer server-provided list; fallback to localStorage
        let namesFromServer = null;
        try {
          const res = await fetch('/counters/list' + (BRANCH ? `?branch=${encodeURIComponent(BRANCH)}` : ''), { cache: 'no-store' });
          if (res.ok) { const data = await res.json(); namesFromServer = Array.isArray(data.names) ? data.names : null; }
        } catch(e) { namesFromServer = null; }
        let added = namesFromServer || [];
        if (!added.length) {
          try { added = JSON.parse(localStorage.getItem(lsKey('added_counters')) || '[]'); } catch(e) { added = []; }
        }
        if (!Array.isArray(added)) added = [];
        if (currentCounterSelect) {
          const existing = new Set(getCurrentCounterNames());
          added.forEach(name => {
            if (!existing.has(name)) {
              const opt = document.createElement('option');
              opt.value = name; opt.textContent = name;
              currentCounterSelect.appendChild(opt);
            }
          });
        }
        const existingAll = new Set(ALL_COUNTERS);
        added.forEach(name => { if (!existingAll.has(name)) ALL_COUNTERS.push(name); });
      }

      async function addCounter() {
        const names = getCurrentCounterNames();
        const nums = names.map(getNumericCounterIndex).filter(n => typeof n === 'number');
        const next = (nums.length ? Math.max(...nums) : 0) + 1;
        const name = `Counter ${next}`;
        if (currentCounterSelect) {
          const opt = document.createElement('option');
          opt.value = name; opt.textContent = name;
          currentCounterSelect.appendChild(opt);
          currentCounterSelect.value = name;
          if (currentCounterLabelEl) currentCounterLabelEl.textContent = name;
        }
        if (!ALL_COUNTERS.includes(name)) ALL_COUNTERS.push(name);
        const added = names.filter(n => getNumericCounterIndex(n) !== null);
        added.push(name);
        await persistAddedCounters(added);
        renderDisplay();
      }

      async function removeCounter() {
        const names = getCurrentCounterNames();
        const numeric = names.filter(n => getNumericCounterIndex(n) !== null);
        if (numeric.length <= 1) {
          alert('At least one counter must remain');
          return;
        }
        const nums = numeric.map(getNumericCounterIndex);
        const maxNum = Math.max(...nums);
        const target = `Counter ${maxNum}`;
        if (currentCounterSelect) {
          for (let i = 0; i < currentCounterSelect.options.length; i++) {
            if (currentCounterSelect.options[i].value === target) {
              currentCounterSelect.remove(i);
              break;
            }
          }
          if (currentCounterSelect.value === target) {
            const remNumeric = getCurrentCounterNames().filter(n => getNumericCounterIndex(n) !== null);
            const remNums = remNumeric.map(getNumericCounterIndex);
            const newMax = Math.max(...remNums);
            const newSel = `Counter ${newMax}`;
            currentCounterSelect.value = newSel;
            if (currentCounterLabelEl) currentCounterLabelEl.textContent = newSel;
          }
        }
        const idx = ALL_COUNTERS.indexOf(target);
        if (idx >= 0) ALL_COUNTERS.splice(idx, 1);
        const remainingNumeric = getCurrentCounterNames().filter(n => getNumericCounterIndex(n) !== null);
        await persistAddedCounters(remainingNumeric);
        renderDisplay();
      }

      (function bindCounterButtons(){
        const addBtn = document.getElementById('addCounterBtn');
        const removeBtn = document.getElementById('removeCounterBtn');
        if (addBtn) addBtn.addEventListener('click', addCounter);
        if (removeBtn) removeBtn.addEventListener('click', removeCounter);
      })();

      function renderCategories() {
        if (!categorySelect) return;
        categorySelect.innerHTML = '<option value="">Select one category</option>';
        CATEGORIES.forEach(cat => {
          const opt = document.createElement('option');
          opt.value = cat.id;
          opt.textContent = cat.name;
          categorySelect.appendChild(opt);
        });
        // also refresh remover select
        const remSel = document.getElementById('removeCategorySelect');
        if (remSel) {
          remSel.innerHTML = '<option value="">Select category to remove</option>';
          CATEGORIES.forEach(cat => {
            const o = document.createElement('option');
            o.value = cat.id; o.textContent = cat.name; remSel.appendChild(o);
          });
        }
      // Pull categories from the server and re-render dropdowns
      async function fetchCategories() {
        try {
          const res = await fetch('/categories/all', { cache: 'no-store' });
          if (!res.ok) return;
          const data = await res.json();
          if (Array.isArray(data.categories)) {
            // Replace in-place to keep references predictable
            CATEGORIES.length = 0;
            data.categories.forEach(c => CATEGORIES.push(c));
            renderCategories();
            renderCurrentlyServing();
          }
        } catch (e) { /* ignore */ }
      }
      function computeStats() {
        const waiting = tickets.filter(t => t.status === 'waiting');
        const priorityWaiting = waiting.filter(t => t.priority === 'priority').length;
        const regularWaiting = waiting.filter(t => t.priority !== 'priority').length;
        const served = tickets.filter(t => t.status === 'completed').length;
        waitingCountEl.textContent = waiting.length;
        priorityWaitingEl.textContent = priorityWaiting;
        regularWaitingEl.textContent = regularWaiting;
        totalServedEl.textContent = served;
      }

      // Fetch server-calculated next numbers per category (branch-aware)
      async function fetchCategoryCounters() {
        try {
          const url = BRANCH ? `/api/category-counters?branch=${encodeURIComponent(BRANCH)}` : '/api/category-counters';
          const res = await fetch(url, { cache: 'no-store' });
          if (!res.ok) return;
          const data = await res.json();
          const map = data && data.categoryCounters ? data.categoryCounters : {};
          Object.keys(CATEGORY_COUNTERS).forEach(k => { delete CATEGORY_COUNTERS[k]; });
          Object.entries(map).forEach(([k,v]) => { CATEGORY_COUNTERS[k] = v; });
        } catch (e) { /* ignore */ }
      }

      function renderQueue() {
        if (tickets.length === 0) {
          queueList.innerHTML = '<div class="text-center py-12 text-gray-400">No tickets in queue yet</div>';
          return;
        }
        queueList.innerHTML = '';
        // Group tickets by status for visual clarity
        const statusOrder = ['waiting', 'serving', 'completed'];
        const grouped = { waiting: [], serving: [], completed: [] };
        tickets.forEach(t => {
          if (grouped[t.status]) grouped[t.status].push(t);
        });
        statusOrder.forEach(status => {
          if (grouped[status].length > 0) {
            const header = document.createElement('div');
            header.className = 'col-span-full mt-2 mb-1';
            header.innerHTML = `<div class="text-lg font-semibold text-${status === 'waiting' ? 'blue' : status === 'serving' ? 'green' : 'gray'}-700 flex items-center gap-2">${status.charAt(0).toUpperCase() + status.slice(1)}
              <span class="inline-block w-2 h-2 rounded-full ${status === 'waiting' ? 'bg-blue-400' : status === 'serving' ? 'bg-green-400' : 'bg-gray-400'}"></span>
            </div>`;
            queueList.appendChild(header);
          }
          grouped[status].slice().reverse().forEach(ticket => {
            const div = document.createElement('div');
            div.className = `p-5 rounded-xl border-2 shadow-sm flex flex-col gap-2 transition-all ${
              status === 'serving' ? 'bg-green-50 border-green-500' :
              status === 'waiting' ? (ticket.priority === 'priority' ? 'bg-red-50 border-red-300' : 'bg-blue-50 border-blue-300') :
              'bg-gray-50 border-gray-200 opacity-60'
            }`;
            div.innerHTML = `
              <div class="flex items-center justify-between">
                <div class="flex flex-col gap-1">
                  <span class="text-2xl font-bold text-gray-900">${ticket.number}</span>
                  <span class="text-sm text-gray-600">${ticket.category}</span>
                  <span class="text-xs text-gray-400">${new Date(ticket.timestamp).toLocaleTimeString()}</span>
                </div>
                <div class="flex flex-col items-end gap-2">
                  <span class="px-3 py-1 rounded-full text-xs font-semibold ${
                    status === 'serving' ? 'bg-green-500 text-white' :
                    status === 'waiting' ? (ticket.priority === 'priority' ? 'bg-red-500 text-white' : 'bg-blue-500 text-white') :
                    'bg-gray-400 text-white'
                  }">${status.charAt(0).toUpperCase() + status.slice(1)}</span>
                  <span class="text-xs text-gray-500">${ticket.counter ? 'Counter: ' + ticket.counter : ''}</span>
                </div>
              </div>
            `;
            queueList.appendChild(div);
          });
        });
      }

      function renderCurrentlyServing() {
        const currentCounterValue = currentCounterSelect ? currentCounterSelect.value : (currentCounterLabelEl ? currentCounterLabelEl.textContent : null);
        if (currentCounterLabelEl && currentCounterValue) currentCounterLabelEl.textContent = currentCounterValue;

        // prefer actual serving ticket at this counter
        const currentCounterTicket = tickets.find(t => (t.status === 'serving') && (String(t.counter) === String(currentCounterValue)));
        if (currentCounterTicket) {
          // if categories are selected, ensure the serving ticket matches one of them
          if (!selectedCategories || selectedCategories.length === 0) {
            currentlyServing.innerHTML = `<div class="text-green-600 text-6xl mb-2">${currentCounterTicket.number}</div><p class="text-gray-600">${currentCounterTicket.category}</p><div class="mt-4"><button id="openGenerateFromServing" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Generate New Number</button></div>`;
            const btnG = document.getElementById('openGenerateFromServing'); if (btnG) btnG.addEventListener('click', () => openGenerateModal(currentCounterTicket));
            return;
          }
          // check category match
          const matchByCategory = (selectedCategories.includes(String(currentCounterTicket.category_id)) || selectedCategories.includes(currentCounterTicket.category));
          if (matchByCategory) {
            currentlyServing.innerHTML = `<div class="text-green-600 text-6xl mb-2">${currentCounterTicket.number}</div><p class="text-gray-600">${currentCounterTicket.category}</p><div class="mt-4"><button id="openGenerateFromServing" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Generate New Number</button></div>`;
            const btnG = document.getElementById('openGenerateFromServing'); if (btnG) btnG.addEventListener('click', () => openGenerateModal(currentCounterTicket));
            return;
          }
          // otherwise, fall through to preview or lastCalledTicket
        }

        // fallback: if we just called a ticket for this counter, show it
        if (lastCalledTicket && String(lastCalledTicket.counter) === String(currentCounterValue)) {
          // if categories are selected, ensure the lastCalledTicket belongs to one of them
          if (!selectedCategories || selectedCategories.length === 0 || selectedCategories.includes(String(lastCalledTicket.category_id)) || selectedCategories.includes(lastCalledTicket.category)) {
            currentlyServing.innerHTML = `<div class="text-green-600 text-6xl mb-2">${lastCalledTicket.number}</div><p class="text-gray-600">${lastCalledTicket.category || ''}</p><div class="mt-4"><button id="openGenerateFromServing" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Generate New Number</button></div>`;
            const btnG = document.getElementById('openGenerateFromServing'); if (btnG) btnG.addEventListener('click', () => openGenerateModal(lastCalledTicket));
            return;
          }
        }

        // If categories are selected, show a preview next number for the first selected category
        if (selectedCategories && selectedCategories.length > 0) {
          const firstCatId = selectedCategories[0];
          const catObj = CATEGORIES.find(c => c.id == firstCatId) || {};
          const nextNum = (CATEGORY_COUNTERS && (CATEGORY_COUNTERS[firstCatId] !== undefined)) ? CATEGORY_COUNTERS[firstCatId] : (catObj.rangeStart ?? '-');
          currentlyServing.innerHTML = `
            <div class="text-gray-700 text-6xl mb-2">${nextNum}</div>
            <p class="text-gray-600">Preview next for <strong>${catObj.name || firstCatId}</strong></p>
            <div class="mt-4"><button id="openGenerateFromServing" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Generate New Number</button></div>
          `;
          const btnG = document.getElementById('openGenerateFromServing'); if (btnG) btnG.addEventListener('click', () => openGenerateModal(null));
          return;
        }

        currentlyServing.innerHTML = '<div class="text-gray-400 text-4xl">No Active Call</div><div class="mt-4"><button id="openGenerateFromServing" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Generate New Number</button></div>';
        const btnG = document.getElementById('openGenerateFromServing'); if (btnG) btnG.addEventListener('click', () => openGenerateModal(null));
      }

      // update currentlyServing when counter selection changes
      if (currentCounterSelect) {
        currentCounterSelect.addEventListener('change', () => {
          if (currentCounterLabelEl) currentCounterLabelEl.textContent = currentCounterSelect.value;
          renderCurrentlyServing();
        });
      }

      function refreshAll() {
        computeStats();
        renderQueue();
        renderCurrentlyServing();
        renderDisplay();
      }

      // initialize
      renderCategories();
      // restore any previously added counters for this branch
      loadAddedCounters();
      // single-select category dropdown handler
      if (categorySelect) {
        categorySelect.addEventListener('change', () => {
          const val = categorySelect.value;
          selectedCategories = val ? [val] : [];
          renderCurrentlyServing();
        });
      }
      refreshAll();

        // Keep Caller view live by polling server state
        async function syncFromServer() {
          try {
            const url = BRANCH ? `/api/tickets?branch=${encodeURIComponent(BRANCH)}` : '/api/tickets';
            const res = await fetch(url, { cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();
            const incoming = data.tickets || [];
            // Only re-render when something changed to reduce flicker
            const currentSig = JSON.stringify(tickets);
            const nextSig = JSON.stringify(incoming);
            tickets = incoming;
            if (currentSig !== nextSig) {
              refreshAll();
            }
          } catch (e) { /* ignore */ }
        }
        setInterval(syncFromServer, 2000);
        // Instant refresh on local events: BroadcastChannel + storage
        try {
          const ch = new BroadcastChannel(CHANNEL_NAME);
          ch.onmessage = (ev) => {
            const msg = ev && ev.data;
            if (!msg) return;
            if (msg.type === 'ring' || msg.type === 'crawler' || msg.type === 'queue-update' || msg.type === 'ticket-issued' || msg.type === 'categories-update') {
              syncFromServer();
              fetchCategories();
            }
          };
        } catch (e) {}
        window.addEventListener('storage', (e) => {
          if (e.key === lsKey('queue_ring') || e.key === lsKey('last_now_serving') || e.key === lsKey('queue_updated')) {
            syncFromServer();
            fetchCategories();
          }
        });
      // category selection handling moved to dropdown change event above

      // Call next - send selected categories and current counter to server
      callNextBtn.addEventListener('click', async () => {
        if (selectedCategories.length === 0) {
          document.getElementById('categoryWarning').classList.remove('hidden');
          return;
        }
        document.getElementById('categoryWarning').classList.add('hidden');
        callNextBtn.disabled = true;
        const counter = document.getElementById('currentCounter').value;
        try {
          const res = await fetch('{{ route('caller.callNext') }}' + (BRANCH ? `?branch=${encodeURIComponent(BRANCH)}` : ''), {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ categories: selectedCategories, counter, branch: BRANCH })
          });
          const data = await res.json();
          if (data.success) {
            // set lastCalledTicket for immediate UI feedback (only for this counter)
            if (data.ticket) {
              lastCalledTicket = data.ticket;
              // clear after short delay unless tickets array shows real serving for that counter
              setTimeout(() => {
                const realServing = (tickets || []).find(t => t.status === 'serving' && String(t.counter) === String(data.ticket.counter));
                if (!realServing) {
                  lastCalledTicket = null;
                  renderCurrentlyServing();
                }
              }, 8000);
            }

            tickets = data.tickets;
            refreshAll();
            // No auto-popup of display board on call
            // Broadcast the call so Display Board announces automatically (single shared ts)
            try {
              const sharedTs = Date.now();
              const payload = { type: 'ring', number: data.ticket.number, category: data.ticket.category || '', counter: data.ticket.counter || (document.getElementById('currentCounter')?.value || ''), ts: sharedTs, branch: BRANCH };
              try { const ch = new BroadcastChannel(CHANNEL_NAME); ch.postMessage(payload); } catch(e) {}
              try { localStorage.setItem(lsKey('queue_ring'), JSON.stringify(payload)); } catch(e) {}
              try { localStorage.setItem(lsKey('last_now_serving'), JSON.stringify({ number: payload.number, counter: payload.counter, category: payload.category, ts: payload.ts })); } catch(e) {}
              try {
                fetch('/ring', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                  body: JSON.stringify(payload)
                }).catch(() => {});
              } catch(e) {}
              // also broadcast a queue-update so boards refresh counters/waiting instantly
              try { const ch2 = new BroadcastChannel(CHANNEL_NAME); ch2.postMessage({ type: 'queue-update', branch: BRANCH }); } catch(e) {}
              try { localStorage.setItem(lsKey('queue_updated'), String(Date.now())); } catch(e) {}
            } catch(e) {}
          } else {
            alert(data.message || 'No matching ticket');
          }
        } catch (err) {
          console.error(err);
          alert('Error calling next');
        } finally {
          callNextBtn.disabled = false;
        }
      });

      // Call Again - re-announce current or last-called ticket for selected counter
      if (callAgainBtn) {
        callAgainBtn.addEventListener('click', async () => {
          const now = Date.now();
          if (now - lastCallAgainTs < 3000) { return; }
          lastCallAgainTs = now;
          callAgainBtn.disabled = true;
          try {
            const counter = currentCounterSelect ? currentCounterSelect.value : (currentCounterLabelEl ? currentCounterLabelEl.textContent : '');
            let ticket = (tickets || []).find(t => t.status === 'serving' && String(t.counter) === String(counter));
            if (!ticket && lastCalledTicket && String(lastCalledTicket.counter) === String(counter)) {
              ticket = lastCalledTicket;
            }
            if (!ticket) { alert('No active call for this counter'); return; }
            const sharedTs = Date.now();
            const payload = { type: 'ring', number: ticket.number, category: ticket.category || '', counter, ts: sharedTs, branch: BRANCH };
            try { const ch = new BroadcastChannel(CHANNEL_NAME); ch.postMessage(payload); } catch(e) {}
            try { localStorage.setItem(lsKey('queue_ring'), JSON.stringify(payload)); } catch(e) {}
            try { localStorage.setItem(lsKey('last_now_serving'), JSON.stringify({ number: payload.number, counter: payload.counter, category: payload.category, ts: payload.ts })); } catch(e) {}
            try {
              fetch('/ring', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: JSON.stringify(payload)
              }).catch(() => {});
            } catch(e) {}
            // broadcast queue-update so boards refresh immediately
            try { const ch2 = new BroadcastChannel(CHANNEL_NAME); ch2.postMessage({ type: 'queue-update', branch: BRANCH }); } catch(e) {}
            try { localStorage.setItem(lsKey('queue_updated'), String(Date.now())); } catch(e) {}
          } catch(e) {
            console.error(e);
            alert('Error calling again');
          } finally {
            setTimeout(() => { callAgainBtn.disabled = false; }, 3000);
          }
        });
      }

      // Offline notice: ring like a number to Display Board
      const offlineBtn = document.getElementById('offlineBtn');
      if (offlineBtn) {
        offlineBtn.addEventListener('click', () => {
          const msg = 'Sorry we are offline, please be patient';
          try {
            const counter = currentCounterSelect ? currentCounterSelect.value : (currentCounterLabelEl ? currentCounterLabelEl.textContent : '-');
            const sharedTs = Date.now();
            const payload = { type: 'ring', number: msg, category: 'Offline', counter, ts: sharedTs, branch: BRANCH };
            try { const ch = new BroadcastChannel(CHANNEL_NAME); ch.postMessage(payload); } catch(e) {}
            try { localStorage.setItem(lsKey('queue_ring'), JSON.stringify(payload)); } catch(e) {}
            try { localStorage.setItem(lsKey('last_now_serving'), JSON.stringify({ number: payload.number, counter: payload.counter, category: payload.category, ts: payload.ts })); } catch(e) {}
            try {
              fetch('/ring', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: JSON.stringify(payload)
              }).catch(() => {});
            } catch(e) {}
          } catch(e) { /* ignore */ }
        });
      }

      // Call specific number
      const specificInput = document.getElementById('specificNumberInput');
      const callSpecificBtn = document.getElementById('callSpecificBtn');

      // Ring Current Serving feature removed per request
      if (callSpecificBtn) {
        callSpecificBtn.addEventListener('click', async () => {
          const val = specificInput ? specificInput.value.trim() : '';
          if (!val) { alert('Enter a ticket number or id'); return; }
          callSpecificBtn.disabled = true;
          const counter = document.getElementById('currentCounter').value;
          try {
            const res = await fetch('{{ route('caller.callSpecific') }}' + (BRANCH ? `?branch=${encodeURIComponent(BRANCH)}` : ''), {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({ number: val, counter, branch: BRANCH })
            });

            // ring binding already initialized globally
            const data = await res.json();
            if (data.success) {
              if (data.ticket) lastCalledTicket = data.ticket;
              tickets = data.tickets;
              refreshAll();
              // No auto-popup of display board on call
              // Broadcast the call so Display Board announces automatically (single shared ts)
              try {
                const sharedTs = Date.now();
                const payload = { type: 'ring', number: data.ticket.number, category: data.ticket.category || '', counter: data.ticket.counter || (document.getElementById('currentCounter')?.value || ''), ts: sharedTs, branch: BRANCH };
                const payload = { type: 'ring', number: data.ticket.number, category: data.ticket.category || '', counter: data.ticket.counter || (document.getElementById('currentCounter')?.value || ''), ts: sharedTs, branch: BRANCH };
                try { const ch = new BroadcastChannel(CHANNEL_NAME); ch.postMessage(payload); } catch(e) {}
                try { localStorage.setItem(lsKey('queue_ring'), JSON.stringify(payload)); } catch(e) {}
                try { localStorage.setItem(lsKey('last_now_serving'), JSON.stringify({ number: payload.number, counter: payload.counter, category: payload.category, ts: payload.ts })); } catch(e) {}
                try {
                  fetch('/ring', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    body: JSON.stringify(payload)
                  }).catch(() => {});
                } catch(e) {}
              } catch(e) {}
            } else {
              alert(data.message || 'Ticket not found or cannot be served');
            }
          } catch (err) {
            console.error(err);
            alert('Error calling specific ticket');
          } finally {
            callSpecificBtn.disabled = false;
          }
        });
      }

      // Display board
      const displayBoard = document.getElementById('displayBoard');
      const displayBtnText = document.getElementById('displayBtnText');
      const toggleDisplayBtn = document.getElementById('toggleDisplayBtn');
      if (toggleDisplayBtn) {
        toggleDisplayBtn.addEventListener('click', () => {
          displayBoard.classList.toggle('hidden');
          if (displayBtnText) displayBtnText.textContent = displayBoard.classList.contains('hidden') ? 'Show Display Board' : 'Hide Display Board';
          renderDisplay();
        });
      }
      const closeDisplayBtn = document.getElementById('closeDisplay');
      if (closeDisplayBtn) {
        closeDisplayBtn.addEventListener('click', () => {
          displayBoard.classList.add('hidden');
          if (displayBtnText) displayBtnText.textContent = 'Show Display Board';
        });
      }

      function renderDisplay() {
        // populate Now Serving
        const serving = tickets.find(t => t.status === 'serving');
        const nowNumberEl = document.getElementById('displayNowNumber');
        const nowCounterEl = document.getElementById('displayNowCounter');
        const nowCategoryEl = document.getElementById('displayNowCategory');
        const nowCategoryWrap = document.getElementById('displayNowCategoryWrap');
        if (serving) {
          if (nowNumberEl) nowNumberEl.textContent = serving.number;
          if (nowCounterEl) nowCounterEl.innerHTML = `<p class="text-yellow-500 text-4xl">to ${serving.counter || document.getElementById('currentCounter').value}</p>`;
          if (nowCategoryEl) nowCategoryEl.textContent = serving.category || '';
          if (nowCategoryWrap) nowCategoryWrap.classList.remove('hidden');
        } else {
          if (nowNumberEl) nowNumberEl.textContent = '- - -';
          if (nowCounterEl) nowCounterEl.innerHTML = `<p class="text-yellow-500 text-4xl">to -</p>`;
          if (nowCategoryEl) nowCategoryEl.textContent = '';
          if (nowCategoryWrap) nowCategoryWrap.classList.add('hidden');
        }

        // populate All Counters grid
        const allCountersEl = document.getElementById('displayAllCounters');
        if (allCountersEl) {
          allCountersEl.innerHTML = '';
          const servingTickets = tickets.filter(t => t.status === 'serving');
          ALL_COUNTERS.forEach(counterName => {
            const servingTicket = servingTickets.find(st => st.counter === counterName);
            const div = document.createElement('div');
            div.className = 'bg-black rounded-lg p-4 text-center';
            div.innerHTML = `
              <p class="text-gray-400 text-xl mb-2">${counterName}</p>
              <p class="text-white text-5xl">${servingTicket ? servingTicket.number : '---'}</p>
              ${servingTicket ? `<p class="text-gray-500 text-base mt-2 truncate">${servingTicket.category}</p>` : ''}
            `;
            allCountersEl.appendChild(div);
          });
        }

        // waiting count
        const waitingCountEl = document.getElementById('displayWaitingCount');
        if (waitingCountEl) {
          waitingCountEl.textContent = tickets.filter(t => t.status === 'waiting').length;
        }

        // update display time when rendering display
        const timeEl = document.getElementById('displayTime');
        if (timeEl) {
          const now = new Date();
          timeEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }

        // crawler text from localStorage with default 'Hello'
        const crawlerEl = document.getElementById('displayCrawler');
        if (crawlerEl) {
          const text = localStorage.getItem(lsKey('crawler_text')) || 'Hello';
          // restart animation by toggling class
          crawlerEl.classList.remove('crawler-animate');
          // force reflow
          void crawlerEl.offsetWidth;
          crawlerEl.textContent = text;
          crawlerEl.classList.add('crawler-animate');
        }
      }

      function showDisplayBoard() {
        displayBoard.classList.remove('hidden');
        displayBtnText.textContent = 'Hide Display Board';
        renderDisplay();
        setTimeout(() => {
          displayBoard.classList.add('hidden');
          displayBtnText.textContent = 'Show Display Board';
        }, 3500);
      }

      // Waiting Categories modal logic
      const waitingCategoriesModal = document.getElementById('waitingCategoriesModal');
      const waitingCategoriesList = document.getElementById('waitingCategoriesList');
      const viewWaitingCategoriesBtn = document.getElementById('viewWaitingCategoriesBtn');
      const closeWaitingCategories = document.getElementById('closeWaitingCategories');

      function computeWaitingCategories() {
        const waiting = tickets.filter(t => t.status === 'waiting');
        const map = {};
        waiting.forEach(t => {
          const key = t.category || t.category_id || 'Unknown';
          map[key] = (map[key] || 0) + 1;
        });
        return map; // { 'Corporate': 3, ... }
      }

      function showWaitingCategories() {
        const map = computeWaitingCategories();
        waitingCategoriesList.innerHTML = '';
        const entries = Object.entries(map);
        if (entries.length === 0) {
          waitingCategoriesList.innerHTML = '<div class="text-gray-400">No waiting categories</div>';
        } else {
          entries.forEach(([name, count]) => {
            const el = document.createElement('div');
            el.className = 'flex justify-between items-center p-3 bg-gray-50 rounded';
            el.innerHTML = `<div class="text-sm font-medium">${name}</div><div class="text-sm text-gray-600">${count}</div>`;
            waitingCategoriesList.appendChild(el);
          });
        }
        waitingCategoriesModal.classList.remove('hidden');
      }

      viewWaitingCategoriesBtn.addEventListener('click', showWaitingCategories);
      closeWaitingCategories.addEventListener('click', () => waitingCategoriesModal.classList.add('hidden'));

      // Live clock updater for Display Board
      function updateClock() {
        const timeEl = document.getElementById('displayTime');
        if (timeEl) {
          const now = new Date();
          timeEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
      }
      updateClock();
      setInterval(updateClock, 1000);

      // Reports helpers
      const reportsModal = document.getElementById('reportsModal');
      const viewReportsBtn = document.getElementById('viewReportsBtn');
      const closeReportsBtn = document.getElementById('closeReports');
      const reportsAllBody = document.getElementById('reportsAllBody');
      const reportsHourBody = document.getElementById('reportsHourBody');
      const reportsTabAll = document.getElementById('reportsTabAll');
      const reportsTabHour = document.getElementById('reportsTabHour');
      const reportsAll = document.getElementById('reportsAll');
      const reportsHour = document.getElementById('reportsHour');
      const exportHourCsvBtn = document.getElementById('exportHourCsvBtn');
      const exportAllCsvBtn = document.getElementById('exportAllCsvBtn');

      function fmtDateTime(ts) {
        try { const d = new Date(ts); return d.toLocaleString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }); } catch (e) { return ts; }
      }
      function hourKey(ts) {
        const d = new Date(ts);
        const y = d.getFullYear();
        const m = String(d.getMonth()+1).padStart(2,'0');
        const day = String(d.getDate()).padStart(2,'0');
        const h = String(d.getHours()).padStart(2,'0');
        return `${y}-${m}-${day} ${h}:00`;
      }

      function renderReportsTables(sourceTickets) {
        // All transactions
        reportsAllBody.innerHTML = '';
        const list = (sourceTickets || []).slice().sort((a,b)=> new Date(a.timestamp) - new Date(b.timestamp));
        // cache for export
        window.__reportsAllList = list;
        list.forEach(t => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td class="px-3 py-2">${fmtDateTime(t.timestamp)}</td>
            <td class="px-3 py-2">${t.number}</td>
            <td class="px-3 py-2">${t.category || t.category_id || ''}</td>
            <td class="px-3 py-2">${t.priority || ''}</td>
            <td class="px-3 py-2">${t.status || ''}</td>
            <td class="px-3 py-2">${t.counter || ''}</td>
          `;
          reportsAllBody.appendChild(tr);
        });

        // Per-hour aggregation
        const map = {};
        list.forEach(t => { const k = hourKey(t.timestamp); map[k] = (map[k]||0) + 1; });
        reportsHourBody.innerHTML = '';
        Object.entries(map).sort((a,b)=> a[0].localeCompare(b[0])).forEach(([k, cnt]) => {
          const tr = document.createElement('tr');
          tr.innerHTML = `<td class="px-3 py-2">${k}</td><td class="px-3 py-2">${cnt}</td>`;
          reportsHourBody.appendChild(tr);
        });
        // cache for export
        window.__reportsHourMap = map;
      }

      async function showReports() {
        try {
          // TODO: Replace with production endpoint for reports
          renderReportsTables(tickets);
        } catch (e) { renderReportsTables(tickets); }
        reportsModal.classList.remove('hidden');
        // default tab
        reportsAll.classList.remove('hidden');
        reportsHour.classList.add('hidden');
        reportsTabAll.classList.add('bg-gray-200');
        reportsTabHour.classList.remove('bg-gray-200');
      }
      function closeReports() { reportsModal.classList.add('hidden'); }
      function showAllTab() {
        reportsAll.classList.remove('hidden');
        reportsHour.classList.add('hidden');
        reportsTabAll.classList.add('bg-gray-200');
        reportsTabHour.classList.remove('bg-gray-200');
      }
      function showHourTab() {
        reportsAll.classList.add('hidden');
        reportsHour.classList.remove('hidden');
        reportsTabHour.classList.add('bg-gray-200');
        reportsTabAll.classList.remove('bg-gray-200');
      }
      viewReportsBtn.addEventListener('click', showReports);
      closeReportsBtn.addEventListener('click', closeReports);
      reportsTabAll.addEventListener('click', showAllTab);
      reportsTabHour.addEventListener('click', showHourTab);

      async function exportHourCsv() {
        try {
          // Refresh data quickly to ensure latest counts
          try {
            // TODO: Replace with production endpoint for reports
            renderReportsTables(tickets);
          } catch (e) {}
          const map = window.__reportsHourMap || {};
          const rows = [['Hour', 'Total Transactions']];
          Object.entries(map).sort((a,b)=> a[0].localeCompare(b[0])).forEach(([hour, cnt]) => rows.push([hour, String(cnt)]));
          const csv = rows.map(r => r.map(v => '"'+String(v).replace(/"/g,'""')+'"').join(',')).join('\n');
          const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
          const url = URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = 'transactions_per_hour.csv';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          URL.revokeObjectURL(url);
        } catch (e) { alert('Failed to export CSV'); }
      }
      if (exportHourCsvBtn) exportHourCsvBtn.addEventListener('click', exportHourCsv);

      function exportAllCsv() {
        try {
          const rows = [['Time','Number','Category','Priority','Status','Counter']];
          const list = window.__reportsAllList || [];
          list.forEach(t => {
            const time = fmtDateTime(t.timestamp);
            const number = t.number ?? '';
            const category = (t.category ?? t.category_id ?? '');
            const priority = t.priority ?? '';
            const status = t.status ?? '';
            const counter = t.counter ?? '';
            rows.push([time, number, category, priority, status, counter]);
          });
          const csv = rows.map(r => r.map(v => '"'+String(v).replace(/"/g,'""')+'"').join(',')).join('\n');
          const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
          const url = URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = 'transactions_all.csv';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          URL.revokeObjectURL(url);
        } catch (e) { alert('Failed to export CSV'); }
      }
      if (exportAllCsvBtn) exportAllCsvBtn.addEventListener('click', exportAllCsv);

      // Add Category interactions
      const addCategoryBtn = document.getElementById('addCategoryBtn');
      const addCategoryModal = document.getElementById('addCategoryModal');
      const closeAddCategory = document.getElementById('closeAddCategory');
      const saveAddCategory = document.getElementById('saveAddCategory');
      const addCatName = document.getElementById('addCatName');
      const addCatPriority = document.getElementById('addCatPriority');
      const addCatError = document.getElementById('addCatError');

      function openAddCategory() {
        addCatName.value = '';
        addCatPriority.value = 'regular';
        addCatError.classList.add('hidden');
        addCategoryModal.classList.remove('hidden');
      }
      function closeAddCategoryModal() {
        addCategoryModal.classList.add('hidden');
      }
      addCategoryBtn.addEventListener('click', openAddCategory);
      closeAddCategory.addEventListener('click', closeAddCategoryModal);

      async function submitAddCategory() {
        const name = (addCatName.value || '').trim();
        const priority = addCatPriority.value || 'regular';
        if (!name || !priority) { addCatError.classList.remove('hidden'); return; }
        addCatError.classList.add('hidden');
        saveAddCategory.disabled = true;
        try {
          const res = await fetch('{{ route('categories.add') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ name, priority })
          });
          const data = await res.json();
          if (!res.ok || !data.ok) {
            alert(data.message || 'Failed to add category');
            return;
          }
          // Update local categories and re-render dropdown
          if (data.category) {
            CATEGORIES.push(data.category);
            renderCategories();
            // auto-select the newly added category
            if (categorySelect) {
              categorySelect.value = data.category.id;
              selectedCategories = [data.category.id];
            }
            renderCurrentlyServing();
          // Broadcast to other tabs and trigger server-only boards to refresh
          try { const ch = new BroadcastChannel(CHANNEL_NAME); ch.postMessage({ type: 'categories-update', branch: BRANCH }); } catch(e) {}
          try { localStorage.setItem(lsKey('queue_updated'), String(Date.now())); } catch(e) {}
          closeAddCategoryModal();
        } catch (e) {
          alert('Error adding category');
        } finally {
          saveAddCategory.disabled = false;
        }
      }
      saveAddCategory.addEventListener('click', submitAddCategory);

      // Remove category logic
      const removeCategoryBtn = document.getElementById('removeCategoryBtn');
      const removeCategorySelect = document.getElementById('removeCategorySelect');
      async function removeSelectedCategory() {
        const id = removeCategorySelect.value;
        if (!id) { alert('Select a category to remove'); return; }
        if (!confirm('Remove this category?')) return;
        removeCategoryBtn.disabled = true;
        try {
          const res = await fetch('{{ route('categories.remove') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id })
          });
          const data = await res.json();
          if (!data.ok) { alert(data.message || 'Failed to remove'); return; }
          // mutate local CATEGORIES array
          const idx = CATEGORIES.findIndex(c => c.id === id);
          if (idx >= 0) CATEGORIES.splice(idx, 1);
          renderCategories();
          // if removed one was selected for handling, clear selection
          if (selectedCategories.includes(id)) {
            selectedCategories = [];
            if (categorySelect) categorySelect.value = '';
            renderCurrentlyServing();
          // Broadcast to other tabs and trigger server-only boards to refresh
          try { const ch = new BroadcastChannel(CHANNEL_NAME); ch.postMessage({ type: 'categories-update', branch: BRANCH }); } catch(e) {}
          try { localStorage.setItem(lsKey('queue_updated'), String(Date.now())); } catch(e) {}
        } catch (e) {
          alert('Error removing category');
        } finally {
          removeCategoryBtn.disabled = false;
        }
      }
      if (removeCategoryBtn) removeCategoryBtn.addEventListener('click', removeSelectedCategory);

      // Crawler Text controls
      const crawlerTextBtn = document.getElementById('crawlerTextBtn');
      const crawlerTextModal = document.getElementById('crawlerTextModal');
      const closeCrawlerText = document.getElementById('closeCrawlerText');
      const saveCrawlerText = document.getElementById('saveCrawlerText');
      const crawlerTextInput = document.getElementById('crawlerTextInput');

      function openCrawlerModal() {
        const current = localStorage.getItem(lsKey('crawler_text')) || 'Hello';
        crawlerTextInput.value = current;
        crawlerTextModal.classList.remove('hidden');
        crawlerTextModal.classList.add('flex');
      }
      function closeCrawlerModal() {
        crawlerTextModal.classList.add('hidden');
        crawlerTextModal.classList.remove('flex');
      }
      function applyCrawlerTextToDom(text) {
        const el = document.getElementById('displayCrawler');
        if (!el) return;
        el.classList.remove('crawler-animate');
        void el.offsetWidth;
        el.textContent = text;
        el.classList.add('crawler-animate');
      }
      function saveCrawler() {
        const val = (crawlerTextInput.value || '').trim() || 'Hello';
        localStorage.setItem(lsKey('crawler_text'), val);
        applyCrawlerTextToDom(val);
        // also broadcast to other tabs if available
        try {
          const ch = new BroadcastChannel(CHANNEL_NAME);
          ch.postMessage({ type: 'crawler', text: val });
        } catch(e) { /* ignore */ }
        // persist to server so other browsers stay in sync
        try {
          fetch('/crawler' + (BRANCH ? `?branch=${encodeURIComponent(BRANCH)}` : ''), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ text: val, branch: BRANCH })
          }).catch(()=>{});
        } catch(e) {}
        // hint boards to refresh immediately
        try { localStorage.setItem(lsKey('queue_updated'), String(Date.now())); } catch(e) {}
        closeCrawlerModal();
      }

      if (crawlerTextBtn) crawlerTextBtn.addEventListener('click', openCrawlerModal);
      if (closeCrawlerText) closeCrawlerText.addEventListener('click', closeCrawlerModal);
      if (saveCrawlerText) saveCrawlerText.addEventListener('click', saveCrawler);

      // --- Generate New Number Logic ---
      const generateModal = document.getElementById('generateModal');
      const closeGenerateModalBtn = document.getElementById('closeGenerateModal');
      const generateCurrentNumberInput = document.getElementById('generateCurrentNumber');
      const generateNewNumberInput = document.getElementById('generateNewNumber');
      const generateCategorySelect = document.getElementById('generateCategorySelect');
      const generateSaveBtn = document.getElementById('generateSaveBtn');
      const generateErrorEl = document.getElementById('generateError');


      function populateGenerateCategoryOptions() {
        if (!generateCategorySelect) return;
        generateCategorySelect.innerHTML = '<option value="">Select category</option>';
        CATEGORIES.forEach(cat => {
          const opt = document.createElement('option');
          // Use category id so backend derives numbering/priority
          opt.value = cat.id;
          opt.textContent = cat.name || cat.id;
          generateCategorySelect.appendChild(opt);
        });
      }

      function parseTicketNumber(raw) {
        try { const m = String(raw || '').match(/\d+/); return m ? parseInt(m[0], 10) : NaN; } catch(e) { return NaN; }
      }
      function nextNumberForCategory(cat) {
        try {
          const nums = (tickets || [])
            .filter(t => String(t.category || t.category_id) === String(cat))
            .map(t => parseTicketNumber(t.number))
            .filter(n => Number.isFinite(n));
          const max = nums.length ? Math.max(...nums) : NaN;
          if (Number.isFinite(max)) return String(max + 1);
        } catch(e) {}
        return '';
      }
      function computeAutoNewNumber(ticket, selectedCategory) {
        // Prefer server-provided next number for the selected category
        const cat = selectedCategory || (ticket && (ticket.category_id || ticket.category)) || '';
        if (cat && CATEGORY_COUNTERS && CATEGORY_COUNTERS.hasOwnProperty(cat)) {
          const v = CATEGORY_COUNTERS[cat];
          if (v !== undefined && v !== null) return String(v);
        }
        // Fallback: derive next using local tickets (may be incomplete if older tickets lack category_id)
        const catNext = cat ? nextNumberForCategory(cat) : '';
        if (catNext) return catNext;
        // Fallback: current serving + 1
        const base = parseTicketNumber(ticket && ticket.number ? ticket.number : '');
        if (Number.isFinite(base)) return String(base + 1);
        return '';
      }

      async function openGenerateModal(ticket) {
        generateErrorEl.classList.add('hidden');
        populateGenerateCategoryOptions();
        await fetchCategoryCounters();
        const curTicket = ticket || lastCalledTicket || {};
        const curNum = curTicket && curTicket.number ? String(curTicket.number) : '';
        generateCurrentNumberInput.value = curNum;
        // default category to current ticket's category id/name if possible
        let selectedCat = '';
        try {
          const catVal = (curTicket && (curTicket.category_id || curTicket.category)) || '';
          if (catVal) {
            const match = Array.from(generateCategorySelect.options).find(o => o.value === catVal || o.textContent === catVal);
            if (match) { generateCategorySelect.value = match.value; selectedCat = match.value; }
          }
        } catch(e) {}
        // compute auto new based on server counters
        const autoNew = computeAutoNewNumber(curTicket, selectedCat || generateCategorySelect.value || '');
        generateNewNumberInput.value = autoNew || '';
        generateModal.classList.remove('hidden');
        generateModal.classList.add('flex');
      }

      function closeGenerateModal() {
        generateModal.classList.add('hidden');
        generateModal.classList.remove('flex');
      }
      if (closeGenerateModalBtn) closeGenerateModalBtn.addEventListener('click', closeGenerateModal);
      if (generateCategorySelect) {
        generateCategorySelect.addEventListener('change', () => {
          // Recompute suggestion when category changes to keep number in the correct line
          const curTicket = lastCalledTicket || {};
          const autoNew = computeAutoNewNumber(curTicket, generateCategorySelect.value || '');
          if (autoNew) generateNewNumberInput.value = autoNew;
        });
      }

      async function saveGenerateCall() {
        const currentNumber = (generateCurrentNumberInput.value || '').trim();
        const categoryId = generateCategorySelect ? generateCategorySelect.value : '';
        if (!categoryId) { generateErrorEl.classList.remove('hidden'); return; }
        generateErrorEl.classList.add('hidden');
        generateSaveBtn.disabled = true;
        try {
          // Persist a new WAITING ticket via backend using category id
          const url = `{{ route('ticket.generate') }}` + (BRANCH ? `?branch=${encodeURIComponent(BRANCH)}` : '');
          const res = await fetch(url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              mode: 'screen',
              transaction: categoryId
            })
          });
          // Parse JSON only when server responds with JSON
          const ct = (res.headers.get('content-type') || '').toLowerCase();
          let data = null;
          if (ct.includes('application/json')) {
            data = await res.json();
          } else {
            const text = await res.text();
            throw new Error('Server returned non-JSON response.\n' + text.slice(0, 200));
          }
          if (!res.ok) { throw new Error(data && data.message ? data.message : 'Failed to generate ticket'); }

          if (data && Array.isArray(data.tickets)) {
            tickets = data.tickets;
          }

          // Broadcast update so other views refresh (no ring for waiting)
          try { const ch2 = new BroadcastChannel(CHANNEL_NAME); ch2.postMessage({ type: 'queue-update', branch: BRANCH }); } catch(e) {}
          try { localStorage.setItem(lsKey('queue_updated'), String(Date.now())); } catch(e) {}

          lastCalledTicket = null;
          refreshAll();
          closeGenerateModal();
        } catch (e) {
          alert(e && e.message ? e.message : 'Failed to generate ticket');
        } finally {
          generateSaveBtn.disabled = false;
        }
      }
      if (generateSaveBtn) generateSaveBtn.addEventListener('click', saveGenerateCall);

    </script>
  </body>
</html>
