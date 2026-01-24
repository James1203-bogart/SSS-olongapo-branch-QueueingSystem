<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

// Queue Display Board Only View (v2)
Route::get('/queue_display_board', function () {
    // Serve legacy display view (design preserved)
    return view('queue_display_board_');
});

Route::get('/', function () {
    return view('First');
});

use App\Http\Controllers\QueueController;
use App\Http\Controllers\CallerController;

// Main entry
Route::get('/', function () { return view('First'); });

// Queueing Info (branch selection + per-branch links)
Route::get('/queueing-info', function () { return view('queueing_info'); })->name('queueing.info');

// Branch-scoped pretty links (unique URL per branch)
Route::prefix('branch/{branch}')->group(function () {
    // Display Board for a branch (legacy design)
    Route::get('/display', function ($branch) {
        return view('queue_display_board_', ['branch' => $branch]);
    })->name('branch.display');

    // New Display Board v2 (light layout)
    Route::get('/display-v2', function ($branch) {
        return view('queue_display_board_v2', ['branch' => $branch]);
    })->name('branch.display.v2');

    // Caller for a branch
    Route::get('/caller', [CallerController::class, 'index'])->name('branch.caller');

    // With thermal (printer)
    Route::get('/printer', [QueueController::class, 'printer'])->name('branch.printer');

    // Without thermal (screen)
    Route::get('/screen', [QueueController::class, 'screen'])->name('branch.screen');
});

// Queue flows
Route::get('/printer', [QueueController::class, 'printer'])->name('printer');
Route::get('/screen', [QueueController::class, 'screen'])->name('screen');
Route::get('/categories/{mode}/{priority}', [QueueController::class, 'categories'])->name('categories');
Route::post('/ticket', [QueueController::class, 'generateTicket'])->name('ticket.generate');
Route::get('/ticket', [QueueController::class, 'showTicket'])->name('ticket.show');

// Categories - add new
Route::post('/categories/add', [QueueController::class, 'addCategory'])
    ->name('categories.add');
// Categories - remove
Route::post('/categories/remove', [QueueController::class, 'removeCategory'])
    ->name('categories.remove');
// Categories - JSON list (fallback for views)
Route::get('/categories/all', function () {
    return response()
        ->json(['categories' => \App\Http\Controllers\QueueController::categoriesList()])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
})->name('categories.all');

// Categories - counters (branch-aware)
Route::get('/categories/counters', function (Request $request) {
    $branch = $request->route('branch') ?? $request->query('branch');
    $ctrl = app(\App\Http\Controllers\QueueController::class);
    try {
        $counters = $ctrl->categoryCounters($branch);
    } catch (\Throwable $e) {
        $counters = [];
    }
    return response()
        ->json(['categoryCounters' => $counters, 'branch' => $branch])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
})->name('categories.counters');

// Caller
Route::get('/caller', [CallerController::class, 'index'])->name('caller.index');
Route::post('/caller/assign', [CallerController::class, 'assign'])->name('caller.assign');
Route::post('/caller/call-next', [CallerController::class, 'callNext'])->name('caller.callNext');
Route::post('/caller/call-specific', [CallerController::class, 'callSpecific'])->name('caller.callSpecific');
// Generate a new number and immediately serve it (manual override)
Route::post('/caller/generate-now-serving', [CallerController::class, 'generateNowServing'])->name('caller.generateNowServing');

// Ring endpoints for cross-device announcements
Route::post('/ring', function (Request $request) {
    // Preserve client-provided ts when present so all channels share one id
    $ts = $request->has('ts') ? (int) $request->input('ts') : (int) round(microtime(true) * 1000);
    $branch = $request->route('branch') ?? $request->input('branch') ?? $request->query('branch');
    $payload = [
        'number' => (string)($request->input('number', '')),
        'category' => (string)($request->input('category', '')),
        'counter' => (string)($request->input('counter', '')),
        'ts' => $ts,
        'branch' => (string)($branch ?? ''),
    ];
    $key = $branch ? ('queue:last_ring:'.$branch) : 'queue:last_ring';
    Cache::put($key, $payload, now()->addMinutes(30));
    // Also write to a global key for cross-browser boards without explicit branch
    try { Cache::put('queue:last_ring', $payload, now()->addMinutes(30)); } catch (\Throwable $e) { /* ignore */ }
    return response()
        ->json(['ok' => true, 'ring' => $payload])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, X-CSRF-TOKEN');
})->name('ring.post');

// Preflight for /ring (CORS)
Route::options('/ring', function () {
    return response('')
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, X-CSRF-TOKEN');
});

Route::get('/ring/last', function () {
    $branch = request()->route('branch') ?? request('branch');
    $key = $branch ? ('queue:last_ring:'.$branch) : 'queue:last_ring';
    $payload = Cache::get($key);
    // Fallback: if branch-scoped key is empty, return the global last ring
    if (!$payload && $branch) {
        $payload = Cache::get('queue:last_ring');
    }
    return response()
        ->json($payload)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
})->name('ring.last');

// Helpers for counters names persistence
function defaultCountersList(): array {
    return ['Counter 1', 'Counter 2', 'Counter 3', 'Backroom', 'E-Center Regular', 'E-Center Priority', 'Priority', 'Medical'];
}
function getCountersNames(?string $branch): array {
    $key = $branch ? ('queue:counters:names:'.$branch) : 'queue:counters:names';
    $names = Cache::get($key);
    if (!is_array($names) || empty($names)) { $names = defaultCountersList(); }
    // ensure unique and trimmed
    $names = array_values(array_unique(array_map(fn($s)=> (string) trim($s), $names)));
    return $names;
}
function setCountersNames(?string $branch, array $names): void {
    $key = $branch ? ('queue:counters:names:'.$branch) : 'queue:counters:names';
    // sanitize + ensure at least one
    $names = array_values(array_filter(array_map(fn($s)=> (string) trim($s), $names), fn($s)=> $s !== ''));
    if (empty($names)) { $names = defaultCountersList(); }
    Cache::put($key, $names, now()->addDays(7));
}

// All Counters Status (branch-aware): returns current serving per counter
Route::get('/counters/status', function () {
    $branch = request()->route('branch') ?? request('branch');
    $COUNTERS = getCountersNames($branch);
    $norm = function ($s) { return strtolower(trim((string) $s)); };
    $statuses = [];
    foreach ($COUNTERS as $name) { $statuses[$name] = null; }

    // Prefer Couchbase when bound, else SQL; fallback to session
    $cbRepo = app()->bound(\App\Services\CouchbaseTicketRepository::class) ? app()->make(\App\Services\CouchbaseTicketRepository::class) : null;
    try {
        if ($cbRepo && $branch) {
            $tickets = $cbRepo->listByBranch($branch);
            $tickets = array_filter($tickets, fn($t) => isset($t['called_at']) && $t['called_at']);
            usort($tickets, fn($a, $b) => strtotime($b['called_at'] ?? '1970-01-01') <=> strtotime($a['called_at'] ?? '1970-01-01'));
            $tickets = array_map(fn($t) => [ 'number'=>$t['number'], 'category'=>$t['category'], 'counter'=>$t['counter'] ], $tickets);
        } else {
            $tickets = \App\Models\Ticket::when($branch, fn($q)=>$q->where('branch',$branch))
                ->whereNotNull('called_at')
                ->orderBy('called_at', 'desc')
                ->get(['number','category','counter'])
                ->map(fn($t)=>[ 'number'=>$t->number, 'category'=>$t->category, 'counter'=>$t->counter ])
                ->all();
        }
    } catch (\Throwable $e) {
        $tickets = $branch ? session('tickets:'.$branch, []) : session('tickets', []);
        $tickets = array_values(array_filter($tickets, fn($t)=>isset($t['called_at'])));
        // Sort by called_at desc if available
        usort($tickets, fn($a, $b) => strtotime($b['called_at'] ?? '1970-01-01') <=> strtotime($a['called_at'] ?? '1970-01-01'));
    }

    $byCounter = [];
    foreach ($tickets as $t) {
        $c = $t['counter'] ?? '';
        if ($c !== '' && !isset($byCounter[$norm($c)])) {
            $byCounter[$norm($c)] = [ 'number' => ($t['number'] ?? '---'), 'category' => ($t['category'] ?? '') ];
        }
    }

    foreach ($COUNTERS as $name) {
        $key = $norm($name);
        if (isset($byCounter[$key])) { $statuses[$name] = $byCounter[$key]; }
    }

    // Fallback: use last ring payload cached per-branch to fill its counter (skip offline)
    try {
        $payload = Cache::get($branch ? ('queue:last_ring:'.$branch) : 'queue:last_ring');
        if ($payload && !empty($payload['counter'])) {
            $num = (string)($payload['number'] ?? '');
            $cat = strtolower((string)($payload['category'] ?? ''));
            $isOffline = ($cat === 'offline') || (stripos($num, 'offline') !== false);
            if (!$isOffline) {
            $k = $norm($payload['counter']);
            foreach ($COUNTERS as $name) {
                if ($norm($name) === $k) { $statuses[$name] = [ 'number' => ($payload['number'] ?? '---'), 'category' => ($payload['category'] ?? '') ]; break; }
            }
            }
        }
    } catch (\Throwable $e) { /* ignore */ }

    return response()
        ->json([
        'branch' => $branch,
        'counters' => $statuses,
        'names' => $COUNTERS,
    ])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
})->name('counters.status');

// Counters: add a name (branch-aware)
Route::post('/counters/add', function (Request $request) {
    $branch = $request->route('branch') ?? $request->input('branch') ?? $request->query('branch');
    $name = trim((string) $request->input('name', ''));
    if ($name === '') { return response()->json(['ok' => false, 'message' => 'Missing name'], 422); }
    $names = getCountersNames($branch);
    if (!in_array($name, $names, true)) { $names[] = $name; }
    setCountersNames($branch, $names);
    // Hint displays to refresh instantly
    try { Cache::put($branch ? ('queue:last_ring:'.$branch) : 'queue:last_ring', Cache::get($branch ? ('queue:last_ring:'.$branch) : 'queue:last_ring'), now()->addMinutes(30)); } catch (\Throwable $e) {}
    return response()
        ->json(['ok' => true, 'names' => $names])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, X-CSRF-TOKEN');
})->name('counters.add');

// Counters: remove a name (branch-aware)
Route::post('/counters/remove', function (Request $request) {
    $branch = $request->route('branch') ?? $request->input('branch') ?? $request->query('branch');
    $name = trim((string) $request->input('name', ''));
    if ($name === '') { return response()->json(['ok' => false, 'message' => 'Missing name'], 422); }
    $names = getCountersNames($branch);
    $names = array_values(array_filter($names, fn($n) => $n !== $name));
    if (empty($names)) { $names = defaultCountersList(); }
    setCountersNames($branch, $names);
    return response()
        ->json(['ok' => true, 'names' => $names])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, X-CSRF-TOKEN');
})->name('counters.remove');

// API: Get all tickets for a branch (for live polling)
Route::get('/api/tickets', function (Request $request) {
    $branch = $request->route('branch') ?? $request->query('branch');
    $cbRepo = app()->bound(\App\Services\CouchbaseTicketRepository::class) ? app()->make(\App\Services\CouchbaseTicketRepository::class) : null;
    try {
        $tickets = ($cbRepo && $branch)
            ? $cbRepo->listByBranch($branch)
            : \App\Models\Ticket::when($branch, fn($q)=>$q->where('branch',$branch))->orderBy('created_at','asc')->get();
    } catch (\Throwable $e) {
        $cacheKey = $branch ? ('tickets:'.$branch) : 'tickets';
        $tickets = Illuminate\Support\Facades\Cache::get($cacheKey);
        if (!is_array($tickets) || empty($tickets)) {
            $tickets = $branch ? session('tickets:'.$branch, []) : session('tickets', []);
        }
    }
    return response()
        ->json(['tickets' => $tickets])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
});

// Crawler: get current text (branch-aware)
Route::get('/crawler', function (Request $request) {
    $branch = $request->route('branch') ?? $request->query('branch');
    $key = $branch ? ('crawler_text:'.$branch) : 'crawler_text';
    $text = (string) (Cache::get($key) ?? 'Hello');
    return response()
        ->json([ 'text' => $text, 'branch' => $branch ])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
})->name('crawler.get');

// New Display Board v2 (non-branch URL)
Route::get('/display-v2', function () {
    return view('queue_display_board_v2');
})->name('display.v2');

// Legacy Display Board (non-branch explicit URL)
Route::get('/display-legacy', function () {
    return view('queue_display_board_');
})->name('display.legacy');

// Crawler: set current text (branch-aware)
Route::post('/crawler', function (Request $request) {
    $branch = $request->route('branch') ?? $request->input('branch') ?? $request->query('branch');
    $text = (string) ($request->input('text', 'Hello'));
    $key = $branch ? ('crawler_text:'.$branch) : 'crawler_text';
    Cache::put($key, $text, now()->addDays(7));
    // Also write to global key so boards without explicit branch see updates
    try { Cache::put('crawler_text', $text, now()->addDays(7)); } catch (\Throwable $e) { /* ignore */ }
    // Nudge listeners to refresh quickly
    try {
        $ts = now()->timestamp;
        Cache::put('crawler_text_last_changed', $ts, now()->addDays(7));
        // branch-scoped timestamp as well
        if ($branch) { Cache::put('crawler_text_last_changed:'.$branch, $ts, now()->addDays(7)); }
    } catch (\Throwable $e) {}
    return response()
        ->json([ 'ok' => true, 'text' => $text, 'branch' => $branch ])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
})->name('crawler.set');

// Preflight for /crawler (CORS)
Route::options('/crawler', function () {
    return response('')
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
});

// Crawler: last changed timestamp (branch-aware)
Route::get('/crawler/last-changed', function (Request $request) {
    $branch = $request->route('branch') ?? $request->query('branch');
    $tsKey = $branch ? ('crawler_text_last_changed:'.$branch) : 'crawler_text_last_changed';
    $ts = Cache::get($tsKey);
    if (!$ts) {
        // Fallback to global timestamp
        $ts = Cache::get('crawler_text_last_changed', 0);
    }
    return response()
        ->json([ 'ts' => (int)($ts ?: 0), 'branch' => $branch ])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
})->name('crawler.last_changed');

// Preflight for categories add/remove (CORS)
Route::options('/categories/add', function () {
    return response('')
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, X-CSRF-TOKEN');
});
Route::options('/categories/remove', function () {
    return response('')
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, X-CSRF-TOKEN');
});

// Preflight for counters add/remove (CORS)
Route::options('/counters/add', function () {
    return response('')
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, X-CSRF-TOKEN');
});
Route::options('/counters/remove', function () {
    return response('')
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, X-CSRF-TOKEN');
});
