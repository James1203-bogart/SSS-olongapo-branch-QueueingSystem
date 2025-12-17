
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

// Queue Display Board Only View
Route::get('/queue_display_board', function () {
    return view('queue_display_board');
});

Route::get('/', function () {
    return view('First');
});

use App\Http\Controllers\QueueController;
use App\Http\Controllers\CallerController;

// Main entry
Route::get('/', function () { return view('First'); });

// Queue flows
Route::get('/printer', [QueueController::class, 'printer'])->name('printer');
Route::get('/screen', [QueueController::class, 'screen'])->name('screen');
Route::get('/categories/{mode}/{priority}', [QueueController::class, 'categories'])->name('categories');
Route::post('/ticket', [QueueController::class, 'generateTicket'])->name('ticket.generate');
Route::get('/ticket', [QueueController::class, 'showTicket'])->name('ticket.show');

// Caller
Route::get('/caller', [CallerController::class, 'index'])->name('caller.index');
Route::post('/caller/assign', [CallerController::class, 'assign'])->name('caller.assign');
Route::post('/caller/call-next', [CallerController::class, 'callNext'])->name('caller.callNext');
Route::post('/caller/call-specific', [CallerController::class, 'callSpecific'])->name('caller.callSpecific');
// Debug route: returns current session tickets and category counters
Route::get('/debug/queue', function () {
    return response()->json([
        'tickets' => session('tickets', []),
        'last_ticket' => session('last_ticket'),
        'categoryCounters' => \App\Http\Controllers\QueueController::categoryCounters(),
    ]);
});

// Debug route: create sample tickets for Corporate Priority and E-Center Priority
Route::get('/debug/queue/issue-sample', function () {
    $all = \App\Http\Controllers\QueueController::categoriesList();
    $counters = \App\Http\Controllers\QueueController::categoryCounters();
    $tickets = session('tickets', []);

    $sampleIds = ['corporate-priority', 'ecenter-priority'];
    foreach ($sampleIds as $sid) {
        $cat = null;
        foreach ($all as $c) if ($c['id'] === $sid) { $cat = $c; break; }
        if (! $cat) continue;
        $next = $counters[$sid] ?? ($cat['rangeStart'] ?? 1);
        $ticket = [
            'id' => uniqid('t'),
            'number' => (string) $next,
            'priority' => $cat['priority'] ?? 'priority',
            'category' => $cat['name'],
            'category_id' => $sid,
            'mode' => 'printer',
            'timestamp' => now()->toDateTimeString(),
            'status' => 'waiting',
        ];
        $tickets[] = $ticket;
        // increment local counter so next sample won't reuse same number
        $counters[$sid] = $next + 1;
    }

    session(['tickets' => $tickets, 'last_ticket' => end($tickets)]);

    return response()->json([
        'tickets' => $tickets,
        'categoryCounters' => \App\Http\Controllers\QueueController::categoryCounters(),
    ]);
});

// Ring endpoints for cross-device announcements
Route::post('/ring', function (Request $request) {
    // Preserve client-provided ts when present so all channels share one id
    $ts = $request->has('ts') ? (int) $request->input('ts') : (int) round(microtime(true) * 1000);
    $payload = [
        'number' => (string)($request->input('number', '')),
        'category' => (string)($request->input('category', '')),
        'counter' => (string)($request->input('counter', '')),
        'ts' => $ts,
    ];
    Cache::put('queue:last_ring', $payload, now()->addMinutes(30));
    return response()->json(['ok' => true, 'ring' => $payload]);
})->name('ring.post');

Route::get('/ring/last', function () {
    return response()
        ->json(Cache::get('queue:last_ring'))
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
})->name('ring.last');
