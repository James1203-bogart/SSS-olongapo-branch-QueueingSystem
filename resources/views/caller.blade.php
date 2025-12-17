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
          <button id="toggleDisplayBtn" class="flex items-center gap-2 px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors">
            <!-- monitor icon -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17h4.5M4 7h16v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7z"></path></svg>
            <span id="displayBtnText">Show Display Board</span>
          </button>
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
                    const res = await fetch('/debug/queue');
                    if (!res.ok) return;
                    const data = await res.json();
                    const tickets = data.tickets || [];
                    const priorityWaiting = tickets.filter(t => t.status === 'waiting' && t.priority === 'priority').length;
                    const regularWaiting = tickets.filter(t => t.status === 'waiting' && t.priority !== 'priority').length;
                    const totalServed = tickets.filter(t => t.status === 'completed').length;
                    document.getElementById('priorityWaiting').textContent = priorityWaiting;
                    document.getElementById('regularWaiting').textContent = regularWaiting;
                    document.getElementById('totalServed').textContent = totalServed;
                } catch (e) {}
            }
            setInterval(fetchQueueStats, 2000);
            fetchQueueStats();
            </script>
          </div>
        </div>

        <!-- Right Panel - Queue List -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-gray-900 mb-6">Queue Status</h2>
            <div class="space-y-3 max-h-[600px] overflow-y-auto" id="queueList">
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
            <h2 class="text-yellow-500 text-center text-2xl mb-4">All Counters Status</h2>
            <div id="displayAllCounters" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4"></div>
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
    </div>

    <script>
      // initial data from session (tickets may be present)
    const initialTickets = @json(Session::get('tickets', []));
  const CATEGORIES = @json($categories ?? []);
  const ALL_COUNTERS = @json($counters ?? []);
  // server-calculated next numbers per category (id => nextNumber)
  const CATEGORY_COUNTERS = @json(\App\Http\Controllers\QueueController::categoryCounters());

    let tickets = initialTickets; // array of ticket objects
  let selectedCategories = []; // single selection stored as [id] or []
  let lastCalledTicket = null; // temporary holder for immediate UI update after callNext

      const categorySelect = document.getElementById('categorySelect');
      const waitingCountEl = document.getElementById('waitingCount');
      const priorityWaitingEl = document.getElementById('priorityWaiting');
      const regularWaitingEl = document.getElementById('regularWaiting');
      const totalServedEl = document.getElementById('totalServed');
      const queueList = document.getElementById('queueList');
      const currentlyServing = document.getElementById('currentlyServing');
      const callNextBtn = document.getElementById('callNextBtn');
  const currentCounterSelect = document.getElementById('currentCounter');
  const currentCounterLabelEl = document.getElementById('currentCounterLabel');

      function renderCategories() {
        if (!categorySelect) return;
        categorySelect.innerHTML = '<option value="">Select one category</option>';
        CATEGORIES.forEach(cat => {
          const opt = document.createElement('option');
          opt.value = cat.id;
          opt.textContent = cat.name;
          categorySelect.appendChild(opt);
        });
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

      function renderQueue() {
        if (tickets.length === 0) {
          queueList.innerHTML = '<div class="text-center py-12 text-gray-400">No tickets in queue yet</div>';
          return;
        }
        queueList.innerHTML = '';
        const list = tickets.slice().reverse();
        list.forEach(ticket => {
          const div = document.createElement('div');
          div.className = `p-4 rounded-lg border-2 transition-all ${ticket.status === 'serving' ? 'bg-green-50 border-green-500' : ticket.status === 'waiting' ? (ticket.priority === 'priority' ? 'bg-red-50 border-red-300' : 'bg-blue-50 border-blue-300') : 'bg-gray-50 border-gray-200 opacity-50'}`;
          div.innerHTML = `
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div>
                  <p class="text-gray-900">${ticket.number}</p>
                  <p class="text-gray-600">${ticket.category}</p>
                  <p class="text-gray-500">${new Date(ticket.timestamp).toLocaleTimeString()}</p>
                </div>
              </div>
              <div>
                <span class="px-3 py-1 rounded-full text-white ${ticket.status === 'serving' ? 'bg-green-500' : ticket.status === 'waiting' ? (ticket.priority === 'priority' ? 'bg-red-500' : 'bg-blue-500') : 'bg-gray-400'}">${ticket.status.charAt(0).toUpperCase() + ticket.status.slice(1)}</span>
              </div>
            </div>`;
          queueList.appendChild(div);
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
            currentlyServing.innerHTML = `<div class="text-green-600 text-6xl mb-2">${currentCounterTicket.number}</div><p class="text-gray-600">${currentCounterTicket.category}</p>`;
            return;
          }
          // check category match
          const matchByCategory = (selectedCategories.includes(String(currentCounterTicket.category_id)) || selectedCategories.includes(currentCounterTicket.category));
          if (matchByCategory) {
            currentlyServing.innerHTML = `<div class="text-green-600 text-6xl mb-2">${currentCounterTicket.number}</div><p class="text-gray-600">${currentCounterTicket.category}</p>`;
            return;
          }
          // otherwise, fall through to preview or lastCalledTicket
        }

        // fallback: if we just called a ticket for this counter, show it
        if (lastCalledTicket && String(lastCalledTicket.counter) === String(currentCounterValue)) {
          // if categories are selected, ensure the lastCalledTicket belongs to one of them
          if (!selectedCategories || selectedCategories.length === 0 || selectedCategories.includes(String(lastCalledTicket.category_id)) || selectedCategories.includes(lastCalledTicket.category)) {
            currentlyServing.innerHTML = `<div class="text-green-600 text-6xl mb-2">${lastCalledTicket.number}</div><p class="text-gray-600">${lastCalledTicket.category || ''}</p>`;
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
          `;
          return;
        }

        currentlyServing.innerHTML = '<div class="text-gray-400 text-4xl">No Active Call</div>';
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
            const res = await fetch('/debug/queue', { cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();
            const incoming = data.tickets || [];
            // Only re-render when something changed to reduce flicker
            const currentSig = JSON.stringify(tickets);
            const nextSig = JSON.stringify(incoming);
            tickets = incoming;
            if (data.categoryCounters) {
              // update preview counters used in currentlyServing preview
              Object.assign(CATEGORY_COUNTERS, data.categoryCounters);
            }
            if (currentSig !== nextSig) {
              refreshAll();
            }
          } catch (e) { /* ignore */ }
        }
        setInterval(syncFromServer, 2000);
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
          const res = await fetch('{{ route('caller.callNext') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ categories: selectedCategories, counter })
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
              const payload = { type: 'ring', number: data.ticket.number, category: data.ticket.category || '', counter: data.ticket.counter || (document.getElementById('currentCounter')?.value || ''), ts: sharedTs };
              try { const ch = new BroadcastChannel('queue-events'); ch.postMessage(payload); } catch(e) {}
              try { localStorage.setItem('queue_ring', JSON.stringify(payload)); } catch(e) {}
              try {
                fetch('/ring', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                  body: JSON.stringify(payload)
                }).catch(() => {});
              } catch(e) {}
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
            const res = await fetch('{{ route('caller.callSpecific') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({ number: val, counter })
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
                const payload = { type: 'ring', number: data.ticket.number, category: data.ticket.category || '', counter: data.ticket.counter || (document.getElementById('currentCounter')?.value || ''), ts: sharedTs };
                try { const ch = new BroadcastChannel('queue-events'); ch.postMessage(payload); } catch(e) {}
                try { localStorage.setItem('queue_ring', JSON.stringify(payload)); } catch(e) {}
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
      document.getElementById('toggleDisplayBtn').addEventListener('click', () => {
        displayBoard.classList.toggle('hidden');
        displayBtnText.textContent = displayBoard.classList.contains('hidden') ? 'Show Display Board' : 'Hide Display Board';
        renderDisplay();
      });
      document.getElementById('closeDisplay').addEventListener('click', () => {
        displayBoard.classList.add('hidden');
        displayBtnText.textContent = 'Show Display Board';
      });

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
              <p class="text-gray-400 text-sm mb-2">${counterName}</p>
              <p class="text-white text-3xl">${servingTicket ? servingTicket.number : '---'}</p>
              ${servingTicket ? `<p class="text-gray-500 text-xs mt-2 truncate">${servingTicket.category}</p>` : ''}
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
    </script>
  </body>
</html>
<!DOCTYPE html>
<html>
<head>
   