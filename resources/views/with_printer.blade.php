<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Number Issuer (Thermal)</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="{{ asset('css/with_printer.css') }}">
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
                    <div id="printerTypeBadge" class="px-4 py-2 rounded-lg bg-blue-500 text-white">Thermal Printer</div>
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
                            <p id="priorityNextNumberValue" class="text-red-600 text-2xl font-semibold">P-0001</p>
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
                            <p id="regularNextNumberValue" class="text-blue-600 text-2xl font-semibold">R-0001</p>
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
                        <script>
                        async function fetchQueueStats() {
                            try {
                                const res = await fetch('/debug/queue');
                                if (!res.ok) return;
                                const data = await res.json();
                                const tickets = data.tickets || [];
                                const priorityWaiting = tickets.filter(t => t.status === 'waiting' && t.priority === 'priority').length;
                                const regularWaiting = tickets.filter(t => t.status === 'waiting' && t.priority !== 'priority').length;
                                const serving = tickets.find(t => t.status === 'serving');
                                const totalServed = tickets.filter(t => t.status === 'completed').length;
                                document.getElementById('priorityWaiting').textContent = priorityWaiting;
                                document.getElementById('regularWaiting').textContent = regularWaiting;
                                document.getElementById('servingNumber').textContent = serving ? serving.number : '-';
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
                                <div id="queueList" class="space-y-3 max-h-[600px] overflow-y-auto">
                                    @php $tickets = session('tickets', []); @endphp
                                    @if(count($tickets) === 0)
                                        <div class="text-center py-12 text-gray-400">No tickets generated yet</div>
                                    @else
                                        @foreach(array_reverse($tickets) as $ticket)
                                            @php
                                                $status = $ticket['status'] ?? '';
                                                if ($status === 'serving') {
                                                    $ticketClass = 'bg-green-50 border-green-500';
                                                } elseif ($status === 'waiting') {
                                                    $ticketClass = (($ticket['priority'] ?? '') === 'priority') ? 'bg-red-50 border-red-300' : 'bg-blue-50 border-blue-300';
                                                } else {
                                                    $ticketClass = 'bg-gray-50 border-gray-200 opacity-50';
                                                }
                                            @endphp
                                            @php
                                                $badgeClass = ($status === 'serving') ? 'bg-green-500' : (($status === 'waiting') ? ((($ticket['priority'] ?? '') === 'priority') ? 'bg-red-500' : 'bg-blue-500') : 'bg-gray-400');
                                            @endphp

                                            <div class="p-4 rounded-lg border-2 transition-all {{ $ticketClass }}">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        @if(($ticket['priority'] ?? '') === 'priority')
                                                            <!-- star icon -->
                                                            <svg class="w-5 h-5 text-red-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.867 1.603-.867 1.902 0l.847 2.545a1 1 0 00.95.69h2.675c.969 0 1.371 1.24.588 1.81l-2.16 1.57a1 1 0 00-.364 1.118l.827 2.548c.3.867-.755 1.588-1.54 1.018l-2.16-1.57a1 1 0 00-1.176 0l-2.16 1.57c-.785.57-1.84-.151-1.54-1.018l.827-2.548a1 1 0 00-.364-1.118L2.889 8.97c-.783-.57-.38-1.81.588-1.81h2.675a1 1 0 00.95-.69l.847-2.545z"/></svg>
                                                        @endif
                                                        <div>
                                                            <p class="text-gray-900">{{ $ticket['number'] }}</p>
                                                            <p class="text-gray-600">{{ $ticket['category'] }}</p>
                                                            <p class="text-gray-500">{{ \Carbon\Carbon::parse($ticket['timestamp'])->format('h:i:s A') }}</p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        @php /* $status already set above */ @endphp
                                                        <span class="px-3 py-1 rounded-full text-white {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                </div>
            </div>

            <!-- Note: thermal printing will open the printable ticket page in a new window/tab -->
        </div>

        <script>
            const CATEGORIES = @json($categories ?? []);
            let categoryCounters = @json($categoryCounters ?? []);
            let tickets = @json(Session::get('tickets', []));
            let latestTicket = null;

            const selectedPriority = document.getElementById('selectedPriorityCategory');
            const selectedRegular = document.getElementById('selectedRegularCategory');
            const generatePriorityBtn = document.getElementById('generatePriorityBtn');
            const generateRegularBtn = document.getElementById('generateRegularBtn');
            const priorityNextNumber = document.getElementById('priorityNextNumber');
            const regularNextNumber = document.getElementById('regularNextNumber');
            const priorityNextNumberValue = document.getElementById('priorityNextNumberValue');
            const regularNextNumberValue = document.getElementById('regularNextNumberValue');
            const queueList = document.getElementById('queueList');

            function populateCategorySelects() {
                CATEGORIES.filter(c => c.priority === 'priority').forEach(c => {
                    const opt = document.createElement('option'); opt.value = c.id; opt.textContent = `${c.name} (${c.rangeStart}-${c.rangeEnd})`;
                    selectedPriority.appendChild(opt);
                });
                CATEGORIES.filter(c => c.priority === 'regular').forEach(c => {
                    const opt = document.createElement('option'); opt.value = c.id; opt.textContent = `${c.name} (${c.rangeStart}-${c.rangeEnd})`;
                    selectedRegular.appendChild(opt);
                });
            }

                    function getNextForCategory(catId) {
                        return categoryCounters[catId] ?? null;
                    }

                    selectedPriority.addEventListener('change', () => {
                        if (selectedPriority.value) {
                            priorityNextNumber.classList.remove('hidden');
                            const next = getNextForCategory(selectedPriority.value);
                            priorityNextNumberValue.textContent = next !== null ? next : '-';
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
                            regularNextNumberValue.textContent = next !== null ? next : '-';
                            generateRegularBtn.disabled = false;
                        } else {
                            regularNextNumber.classList.add('hidden');
                            generateRegularBtn.disabled = true;
                        }
                    });

            async function generateTicket(categoryObj, priority) {
                const payload = { mode: 'printer', priority, transaction: categoryObj.id };
                try {
                    const res = await fetch('{{ route('ticket.generate') }}', {
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
                        throw new Error('Server returned ' + res.status + ': ' + text.substring(0, 200));
                    }

                    let data = null;
                    try {
                        data = await res.json();
                    } catch (parseErr) {
                        // Non-JSON response - try to refresh session tickets via debug endpoint and fallback gracefully
                        try {
                            const dbg = await fetch('/debug/queue');
                            if (dbg.ok) {
                                const dbgData = await dbg.json();
                                tickets = dbgData.tickets || tickets;
                                categoryCounters = dbgData.categoryCounters || categoryCounters;
                                renderQueue();
                                // open printable ticket page as thermal fallback
                                const printWindow = window.open('{{ route('ticket.show') }}', '_blank');
                                if (printWindow) printWindow.focus();
                                alert('Ticket was queued on the server; the UI has been refreshed.');
                                return;
                            }
                        } catch (dbgErr) {
                            console.warn('Debug refresh failed', dbgErr);
                        }
                        throw new Error('Server returned a non-JSON response');
                    }

                    latestTicket = data.ticket;
                    tickets = data.tickets;
                    categoryCounters = data.categoryCounters || categoryCounters;
                    renderQueue();
                    // open printable ticket page which will auto-print for thermal mode
                    const printWindow = window.open('{{ route('ticket.show') }}', '_blank');
                    if (printWindow) printWindow.focus();
                } catch (err) {
                    console.error(err);
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

            // init
            populateCategorySelects();
            renderQueue();

            // Keep issuer view live by polling server state
            async function syncFromServer() {
                try {
                    const res = await fetch('/debug/queue', { cache: 'no-store' });
                    if (!res.ok) return;
                    const data = await res.json();
                    const incoming = data.tickets || [];
                    const currentSig = JSON.stringify(tickets);
                    const nextSig = JSON.stringify(incoming);
                    tickets = incoming;
                    if (data.categoryCounters) categoryCounters = data.categoryCounters;
                    if (currentSig !== nextSig) {
                        renderQueue();
                    }
                } catch (e) {}
            }
            setInterval(syncFromServer, 2000);
        </script>
    </body>
</html>
