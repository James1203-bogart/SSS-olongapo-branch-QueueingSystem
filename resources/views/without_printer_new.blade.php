<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Number Issuer (Screen)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/without_printer.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 min-h-screen">
<div class="max-w-7xl mx-auto p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <button onclick="location.href='{{ url('/') }}'" class="flex items-center gap-2 text-gray-700 hover:text-gray-900 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to Selection
        </button>
        <div class="flex items-center gap-3">
            <div class="px-4 py-2 rounded-lg bg-purple-500 text-white">Number Issuer</div>
            <div id="printerTypeBadge" class="px-4 py-2 rounded-lg bg-green-500 text-white">Screen Display</div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Left Panel - Ticket Generation -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Priority Ticket -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-2 border-red-200">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927C9.349 2.06 10.651 2.06 10.951 2.927l.847 2.545a1 1 0 00.95.69h2.675c.969 0 1.371 1.24.588 1.81l-2.16 1.57a1 1 0 00-.364 1.118l.827 2.548c.3.867-.755 1.588-1.54 1.018l-2.16-1.57a1 1 0 00-1.176 0l-2.16 1.57c-.785.57-1.84-.151-1.54-1.018l.827-2.548a1 1 0 00-.364-1.118L2.889 8.97c-.783-.57-.38-1.81.588-1.81h2.675a1 1 0 00.95-.69l.847-2.545z"/></svg>
                    <h2 class="text-gray-900">Priority Ticket</h2>
                </div>
                <select id="selectedPriorityCategory" class="w-full px-4 py-3 border-2 border-red-300 rounded-lg mb-4 text-gray-900 focus:outline-none focus:border-red-500">
                    <option value="">Select Transaction Type</option>
                </select>
                <div id="priorityNextNumber" class="mb-4 p-3 bg-red-50 rounded-lg hidden">
                    <p class="text-gray-600">Next Number:</p>
                    <p class="text-red-600 text-2xl font-semibold">P-0001</p>
                </div>
                <button id="generatePriorityBtn" disabled class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white py-4 rounded-lg hover:from-red-600 hover:to-red-700 transition-all duration-300 flex items-center justify-center gap-2 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Generate Priority Ticket
                </button>
            </div>
            <!-- Regular Ticket -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-2 border-blue-200">
                <h2 class="text-gray-900 mb-4">Regular Ticket</h2>
                <select id="selectedRegularCategory" class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg mb-4 text-gray-900 focus:outline-none focus:border-blue-500">
                    <option value="">Select Transaction Type</option>
                </select>
                <div id="regularNextNumber" class="mb-4 p-3 bg-blue-50 rounded-lg hidden">
                    <p class="text-gray-600">Next Number:</p>
                    <p class="text-blue-600 text-2xl font-semibold">R-0001</p>
                </div>
                <button id="generateRegularBtn" disabled class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-4 rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 flex items-center justify-center gap-2 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Generate Regular Ticket
                </button>
            </div>
            <!-- Queue Stats -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-gray-900 mb-4">Queue Statistics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                        <span class="text-gray-700">Priority</span>
                        <span id="priorityWaiting" class="text-red-600">0</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                        <span class="text-gray-700">Regular</span>
                        <span id="regularWaiting" class="text-blue-600">0</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                        <span class="text-gray-700">Serving</span>
                        <span id="servingNumber" class="text-green-600">-</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-700">Total Served</span>
                        <span id="totalServed" class="text-gray-600">0</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Right Panel - Queue List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-gray-900 mb-6">Queue Status</h2>
                <div id="queueList" class="space-y-3 max-h-[600px] overflow-y-auto">
                    <div class="text-center py-12 text-gray-400">No tickets generated yet</div>
                </div>
            </div>
        </div>
    </div>
    <!-- Ticket Display Modal (for no-thermal mode) -->
    <div id="ticketModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded-xl w-80 text-center">
            <div class="text-sm text-gray-600">Queue Ticket</div>
            <div id="modalNumber" class="text-4xl font-bold mt-2">-</div>
            <div id="modalCategory" class="text-gray-600 mt-1">-</div>
            <div class="mt-4">
                <button id="closeModal" class="px-4 py-2 bg-gray-200 rounded">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
let CATEGORIES = @json($categories ?? []);
let categoryCounters = @json($categoryCounters ?? []);
let tickets = @json($tickets ?? []);
let latestTicket = null;
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
const CHANNEL_NAME = lsKey('queue-events');
if (BRANCH) { try { localStorage.setItem('lastBranchSlug', BRANCH); } catch (e) {} }
const selectedPriority = document.getElementById('selectedPriorityCategory');
const selectedRegular = document.getElementById('selectedRegularCategory');
const generatePriorityBtn = document.getElementById('generatePriorityBtn');
const generateRegularBtn = document.getElementById('generateRegularBtn');
const priorityNextNumber = document.getElementById('priorityNextNumber');
const regularNextNumber = document.getElementById('regularNextNumber');
const queueList = document.getElementById('queueList');
async function ensureCategories() {
    try {
        const res = await fetch('/categories/all', { cache: 'no-store' });
        if (res.ok) {
            const data = await res.json();
            const incoming = data.categories || [];
            const changed = JSON.stringify(incoming) !== JSON.stringify(CATEGORIES);
            CATEGORIES = incoming;
            return changed;
        }
    } catch (e) {}
    return false;
}
async function fetchCategoryCounters() {
    try {
        const url = BRANCH ? `/api/category-counters?branch=${encodeURIComponent(BRANCH)}` : '/api/category-counters';
        const res = await fetch(url, { cache: 'no-store' });
        if (res.ok) {
            const data = await res.json();
            const next = data.categoryCounters || {};
            categoryCounters = next;
        }
    } catch (e) {}
}

async function populateCategorySelects() {
    const changed = await ensureCategories();
    await fetchCategoryCounters();
    while (selectedPriority.options.length > 1) selectedPriority.remove(1);
    while (selectedRegular.options.length > 1) selectedRegular.remove(1);
    CATEGORIES.filter(c => c.priority === 'priority').forEach(c => {
        const opt = document.createElement('option'); opt.value = c.id; opt.textContent = `${c.name} (${c.rangeStart}-${c.rangeEnd})`;
        selectedPriority.appendChild(opt);
    });
    CATEGORIES.filter(c => c.priority === 'regular').forEach(c => {
        const opt = document.createElement('option'); opt.value = c.id; opt.textContent = `${c.name} (${c.rangeStart}-${c.rangeEnd})`;
        selectedRegular.appendChild(opt);
    });
    if (selectedPriority.value) {
        const next = getNextForCategory(selectedPriority.value);
        priorityNextNumber.classList.remove('hidden');
        priorityNextNumber.querySelector('p.text-red-600').textContent = next !== null ? next : '-';
    }
    if (selectedRegular.value) {
        const next = getNextForCategory(selectedRegular.value);
        regularNextNumber.classList.remove('hidden');
        regularNextNumber.querySelector('p.text-blue-600').textContent = next !== null ? next : '-';
    }
}
function getNextForCategory(catId) {
    return categoryCounters[catId] ?? null;
}
selectedPriority.addEventListener('change', () => {
    if (selectedPriority.value) {
        priorityNextNumber.classList.remove('hidden');
        const next = getNextForCategory(selectedPriority.value);
        priorityNextNumber.querySelector('p.text-red-600').textContent = next !== null ? next : '-';
        generatePriorityBtn.disabled = false;
    } else {
        priorityNextNumber.classList.add('hidden');
        generatePriorityBtn.disabled = true;
    }
});
selectedRegular.addEventListener('change', () => {
    if (selectedRegular.value) {
        regularNextNumber.classList.remove('hidden');
        const next = getNextForCategory(selectedRegular.value);
        regularNextNumber.querySelector('p.text-blue-600').textContent = next !== null ? next : '-';
        generateRegularBtn.disabled = false;
    } else {
        regularNextNumber.classList.add('hidden');
        generateRegularBtn.disabled = true;
    }
});
async function generateTicket(categoryObj, priority) {
    const payload = { mode: 'screen', priority, transaction: categoryObj.id, branch: BRANCH };
    try {
        const url = BRANCH ? ('{{ route('ticket.generate') }}' + '?branch=' + encodeURIComponent(BRANCH)) : '{{ route('ticket.generate') }}';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        });
        if (!res.ok) {
            const text = await res.text();
            throw new Error('Server returned ' + res.status + ': ' + text.substring(0,200));
        }
        let data = await res.json();
        latestTicket = data.ticket;
        tickets = data.tickets;
        categoryCounters = data.categoryCounters || categoryCounters;
        renderQueue();
        showTicketModal(latestTicket);
        try { const ch = new BroadcastChannel(CHANNEL_NAME); ch.postMessage({ type: 'queue-update', branch: BRANCH }); } catch(e) {}
        try { localStorage.setItem(lsKey('queue_updated'), String(Date.now())); } catch(e) {}
    } catch (err) {
        alert('Failed to generate ticket: ' + (err.message || err));
    }
}
generatePriorityBtn.addEventListener('click', () => {
    const categoryId = selectedPriority.value;
    const cat = CATEGORIES.find(c => c.id === categoryId);
    if (cat) generateTicket(cat, 'priority');
});
generateRegularBtn.addEventListener('click', () => {
    const categoryId = selectedRegular.value;
    const cat = CATEGORIES.find(c => c.id === categoryId);
    if (cat) generateTicket(cat, 'regular');
});
function renderQueue() {
    if (tickets.length === 0) {
        queueList.innerHTML = '<div class="text-center py-12 text-gray-400">No tickets generated yet</div>';
        return;
    }
    queueList.innerHTML = '';
    tickets.slice().reverse().forEach(ticket => {
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
    updateStats();
}
function updateStats() {
    document.getElementById('priorityWaiting').textContent = tickets.filter(t => t.status === 'waiting' && t.priority === 'priority').length;
    document.getElementById('regularWaiting').textContent = tickets.filter(t => t.status === 'waiting' && t.priority === 'regular').length;
    const serving = tickets.find(t => t.status === 'serving');
    document.getElementById('servingNumber').textContent = serving ? serving.number : '-';
    document.getElementById('totalServed').textContent = tickets.filter(t => t.status === 'completed').length;
}
function showTicketModal(ticket) {
    if (!ticket) return;
    document.getElementById('modalNumber').textContent = ticket.number;
    document.getElementById('modalCategory').textContent = ticket.category;
    document.getElementById('ticketModal').classList.remove('hidden');
}
document.getElementById('closeModal').addEventListener('click', () => {
    document.getElementById('ticketModal').classList.add('hidden');
});
// init
populateCategorySelects();
renderQueue();
// Keep screen-only issuer live by polling server state
async function syncFromServer() {
    try {
        const url = BRANCH ? `/api/tickets?branch=${encodeURIComponent(BRANCH)}` : '/api/tickets';
        const res = await fetch(url, { cache: 'no-store' });
        if (!res.ok) return;
        const data = await res.json();
        const incoming = data.tickets || [];
        const currentSig = JSON.stringify(tickets);
        const nextSig = JSON.stringify(incoming);
        tickets = incoming;
        if (currentSig !== nextSig) {
            renderQueue();
        }
    } catch (e) {}
}
setInterval(syncFromServer, 2000);
try {
    const ch = new BroadcastChannel(CHANNEL_NAME);
    ch.onmessage = (ev) => {
        const msg = ev && ev.data;
        if (!msg) return;
        if (msg.type === 'ring' || msg.type === 'crawler') {
            syncFromServer();
        }
        if (msg.type === 'categories-update') {
            populateCategorySelects();
        }
    };
} catch (e) {}
window.addEventListener('storage', (e) => {
    if (e.key === lsKey('queue_ring') || e.key === lsKey('last_now_serving')) {
        syncFromServer();
    }
    if (e.key === lsKey('queue_updated')) {
        populateCategorySelects();
    }
});
</script>
</body>
</html>
