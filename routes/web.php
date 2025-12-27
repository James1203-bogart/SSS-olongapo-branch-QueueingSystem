
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

// Queueing Info (branch selection + per-branch links)
Route::get('/queueing-info', function () { return view('queueing_info'); })->name('queueing.info');

// Branch-scoped pretty links (unique URL per branch)
Route::prefix('branch/{branch}')->group(function () {
    // Display Board for a branch
    Route::get('/display', function ($branch) {
        // Optionally pass branch to the view for display
        return view('queue_display_board', ['branch' => $branch]);
    })->name('branch.display');

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
Route::post('/categories/add', [QueueController::class, 'addCategory'])->name('categories.add');
// Categories - remove
Route::post('/categories/remove', [QueueController::class, 'removeCategory'])->name('categories.remove');
// Categories - JSON list (fallback for views)
Route::get('/categories/all', function () {
    return response()->json(['categories' => \App\Http\Controllers\QueueController::categoriesList()]);
})->name('categories.all');

// Caller
Route::get('/caller', [CallerController::class, 'index'])->name('caller.index');
Route::post('/caller/assign', [CallerController::class, 'assign'])->name('caller.assign');
Route::post('/caller/call-next', [CallerController::class, 'callNext'])->name('caller.callNext');
Route::post('/caller/call-specific', [CallerController::class, 'callSpecific'])->name('caller.callSpecific');
// Debug route: returns current DB tickets (scoped by branch) and category counters
Route::get('/debug/queue', function () {
    $branch = request()->route('branch') ?? request('branch');
    $cbRepo = app()->bound(\App\Services\CouchbaseTicketRepository::class) ? app()->make(\App\Services\CouchbaseTicketRepository::class) : null;
    try {
        $tickets = ($cbRepo && $branch)
            ? $cbRepo->listByBranch($branch)
            : \App\Models\Ticket::when($branch, fn($q)=>$q->where('branch',$branch))->orderBy('created_at','asc')->get();
    } catch (\Throwable $e) {
        // Branch-scoped Cache fallback, then session
        $cacheKey = $branch ? ('tickets:'.$branch) : 'tickets';
        $tickets = Illuminate\Support\Facades\Cache::get($cacheKey);
        if (!is_array($tickets) || empty($tickets)) {
            $tickets = $branch ? session('tickets:'.$branch, []) : session('tickets', []);
        }
    }
    // If DB returned empty and Couchbase not used, try Cache fallback to cover non-shared sessions
    if ((!$tickets || (is_array($tickets) && count($tickets) === 0)) && !$cbRepo) {
        $cacheKey = $branch ? ('tickets:'.$branch) : 'tickets';
        $fromCache = Illuminate\Support\Facades\Cache::get($cacheKey);
        if (is_array($fromCache) && !empty($fromCache)) { $tickets = $fromCache; }
    }
    $qc = app()->make(\App\Http\Controllers\QueueController::class);
    return response()->json([
        'tickets' => $tickets,
        'last_ticket' => $branch ? session('last_ticket:'.$branch) : session('last_ticket'),
        'categoryCounters' => $qc->categoryCounters($branch),
    ]);
});

// Debug route: create sample tickets for Corporate Priority and E-Center Priority
Route::get('/debug/queue/issue-sample', function () {
    $all = \App\Http\Controllers\QueueController::categoriesList();
    $qc = app()->make(\App\Http\Controllers\QueueController::class);
    $counters = $qc->categoryCounters(null);
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
            'timestamp' => now()->toIso8601String(),
            'status' => 'waiting',
        ];
        $tickets[] = $ticket;
        // increment local counter so next sample won't reuse same number
        $counters[$sid] = $next + 1;
    }

    session(['tickets' => $tickets, 'last_ticket' => end($tickets)]);

    $qc = app()->make(\App\Http\Controllers\QueueController::class);
    return response()->json([
        'tickets' => $tickets,
        'categoryCounters' => $qc->categoryCounters(null),
    ]);
});

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
    return response()->json(['ok' => true, 'ring' => $payload]);
})->name('ring.post');

Route::get('/ring/last', function () {
    $branch = request()->route('branch') ?? request('branch');
    $key = $branch ? ('queue:last_ring:'.$branch) : 'queue:last_ring';
    return response()
        ->json(Cache::get($key))
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
})->name('ring.last');

// All Counters Status (branch-aware): returns current serving per counter
Route::get('/counters/status', function () {
    $branch = request()->route('branch') ?? request('branch');
    $COUNTERS = ['Counter 1', 'Counter 2', 'Counter 3', 'Backroom', 'E-Center Regular', 'E-Center Priority', 'Priority'];
    $norm = function ($s) { return strtolower(trim((string) $s)); };
    $statuses = [];
    foreach ($COUNTERS as $name) { $statuses[$name] = null; }

    // Prefer Couchbase when bound, else SQL; fallback to session
    $cbRepo = app()->bound(\App\Services\CouchbaseTicketRepository::class) ? app()->make(\App\Services\CouchbaseTicketRepository::class) : null;
    try {
        if ($cbRepo && $branch) {
            $tickets = $cbRepo->listByBranch($branch);
        } else {
            $tickets = \App\Models\Ticket::when($branch, fn($q)=>$q->where('branch',$branch))
                ->where('status','serving')
                ->get(['number','category','counter'])
                ->map(fn($t)=>[ 'number'=>$t->number, 'category'=>$t->category, 'counter'=>$t->counter ])
                ->all();
        }
    } catch (\Throwable $e) {
        $tickets = $branch ? session('tickets:'.$branch, []) : session('tickets', []);
        $tickets = array_values(array_filter($tickets, fn($t)=>($t['status'] ?? '') === 'serving'));
    }

    $byCounter = [];
    foreach ($tickets as $t) {
        $c = $t['counter'] ?? '';
        if ($c !== '') { $byCounter[$norm($c)] = [ 'number' => ($t['number'] ?? '---'), 'category' => ($t['category'] ?? '') ]; }
    }

    foreach ($COUNTERS as $name) {
        $key = $norm($name);
        if (isset($byCounter[$key])) { $statuses[$name] = $byCounter[$key]; }
    }

    // Fallback: use last ring payload cached per-branch to fill its counter (skip Offline message)
    try {
        $payload = Cache::get($branch ? ('queue:last_ring:'.$branch) : 'queue:last_ring');
        $isOffline = false;
        if ($payload) {
            $cat = strtolower(trim((string)($payload['category'] ?? '')));
            $num = strtolower(trim((string)($payload['number'] ?? '')));
            $isOffline = ($cat === 'offline') || (strpos($num, 'offline') !== false);
        }
        if ($payload && !empty($payload['counter']) && !$isOffline) {
            $k = $norm($payload['counter']);
            foreach ($COUNTERS as $name) {
                if ($norm($name) === $k) { $statuses[$name] = [ 'number' => ($payload['number'] ?? '---'), 'category' => ($payload['category'] ?? '') ]; break; }
            }
        }
    } catch (\Throwable $e) { /* ignore */ }

    return response()->json([
        'branch' => $branch,
        'counters' => $statuses,
    ]);
})->name('counters.status');
