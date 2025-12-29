<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Models\Ticket;
use App\Services\CouchbaseTicketRepository;
use Illuminate\Support\Carbon;

class QueueController extends Controller
{
    protected ?CouchbaseTicketRepository $cbRepo = null;

    public function __construct()
    {
        try {
            $repo = app()->bound(CouchbaseTicketRepository::class) ? app()->make(CouchbaseTicketRepository::class) : null;
            $this->cbRepo = $repo instanceof CouchbaseTicketRepository ? $repo : null;
        } catch (\Throwable $e) {
            $this->cbRepo = null;
        }
        // Prefer PostgreSQL when configured as default DB
        try {
            $default = config('database.default');
            if (strtolower((string) $default) === 'pgsql') {
                $this->cbRepo = null;
            }
        } catch (\Throwable $e) {}
    }
    public static function categoriesList()
    {
        // Base categories
        $base = [
            [ 'id' => 'acop', 'name' => 'ACOP', 'priority' => 'regular', 'rangeStart' => 100, 'rangeEnd' => 199, 'color' => 'blue' ],
            [ 'id' => 'membership-priority', 'name' => 'Membership Priority', 'priority' => 'priority', 'rangeStart' => 201, 'rangeEnd' => 299, 'color' => 'red' ],
            [ 'id' => 'medical', 'name' => 'Medical', 'priority' => 'regular', 'rangeStart' => 301, 'rangeEnd' => 399, 'color' => 'blue' ],
            [ 'id' => 'membership-regular', 'name' => 'Membership Regular', 'priority' => 'regular', 'rangeStart' => 401, 'rangeEnd' => 499, 'color' => 'blue' ],
            [ 'id' => 'corporate', 'name' => 'Corporate', 'priority' => 'regular', 'rangeStart' => 501, 'rangeEnd' => 599, 'color' => 'blue' ],
            [ 'id' => 'corporate-priority', 'name' => 'Corporate Priority', 'priority' => 'priority', 'rangeStart' => 601, 'rangeEnd' => 699, 'color' => 'red' ],
            [ 'id' => 'pension-regular', 'name' => 'Pension Care (RDF) Regular', 'priority' => 'regular', 'rangeStart' => 701, 'rangeEnd' => 799, 'color' => 'blue' ],
            [ 'id' => 'pension-priority', 'name' => 'Pension Care (RDF) Priority', 'priority' => 'priority', 'rangeStart' => 801, 'rangeEnd' => 899, 'color' => 'red' ],
            [ 'id' => 'ecenter-regular', 'name' => 'E-Center Regular', 'priority' => 'regular', 'rangeStart' => 901, 'rangeEnd' => 999, 'color' => 'blue' ],
            [ 'id' => 'ecenter-priority', 'name' => 'E-Center Priority', 'priority' => 'priority', 'rangeStart' => 1001, 'rangeEnd' => 1099, 'color' => 'red' ],
            [ 'id' => 'mysss', 'name' => 'My.SSS Appointment', 'priority' => 'regular', 'rangeStart' => 1101, 'rangeEnd' => 1199, 'color' => 'blue' ],
            [ 'id' => 'welcome-back', 'name' => 'Welcome Back Ka-SSS', 'priority' => 'regular', 'rangeStart' => 1201, 'rangeEnd' => 1299, 'color' => 'blue' ],
        ];

        // Merge with any user-added categories stored in session
        $extras = Session::get('extra_categories', []);
        if (is_array($extras) && !empty($extras)) {
            // ensure each extra has required keys and assign ranges when missing
            $BASE_START = 1301; // first dynamic block
            $STEP = 100;       // 1301-1399, 1401-1499, ...
            $SPAN = 98;        // start + 98 => xx99

            // Determine highest assigned index among extras that already have a range
            $maxIdx = -1;
            foreach ($extras as $e) {
                if (!empty($e['rangeStart']) && $e['rangeStart'] >= $BASE_START) {
                    $idx = intdiv(($e['rangeStart'] - $BASE_START), $STEP);
                    if ($idx > $maxIdx) $maxIdx = $idx;
                }
            }

            foreach ($extras as $k => &$e) {
                $e['id'] = $e['id'] ?? strtolower(preg_replace('/[^a-z0-9\-]+/i', '-', $e['name'] ?? uniqid('cat')));
                $e['priority'] = $e['priority'] ?? 'regular';
                $e['color'] = $e['color'] ?? ($e['priority'] === 'priority' ? 'red' : 'blue');
                if (empty($e['rangeStart']) || empty($e['rangeEnd'])) {
                    $nextIdx = $maxIdx + 1;
                    $start = $BASE_START + ($STEP * $nextIdx);
                    $end = $start + $SPAN;
                    $e['rangeStart'] = $start;
                    $e['rangeEnd'] = $end;
                    $maxIdx = $nextIdx;
                }
            }
            unset($e);
            // Persist any backfilled ranges
            Session::put('extra_categories', $extras);
            $base = array_merge($base, $extras);
        }

        return $base;
    }

    // Return next number per category based on DB tickets and category ranges, scoped by branch
    public function categoryCounters(?string $branch = null)
    {
        $all = self::categoriesList();
        $counters = [];

        foreach ($all as $c) {
            $id = $c['id'];
            $start = $c['rangeStart'] ?? 1;
            $end = $c['rangeEnd'] ?? PHP_INT_MAX;

            // Find max number already used for this category using efficient query
            $query = Ticket::when($branch, fn($q)=>$q->where('branch', $branch))
                ->where(function($q) use ($id, $c) {
                    $q->where('category_id', $id)
                      ->orWhere(function($sub) use ($c, $id) {
                          $sub->whereNull('category_id')->where('category', $c['name']);
                      })
                      ->orWhere(function($sub) use ($id) {
                          $sub->whereNull('category_id')->where('category', $id);
                      });
                })
                ->whereRaw("number REGEXP '^[0-9]+$'")
                ->selectRaw('MAX(CAST(number AS UNSIGNED)) as max_num')
                ->first();

            $max = $query ? $query->max_num : null;

            if ($max === null) {
                $next = $start;
            } else {
                $next = $max + 1;
            }

            if ($next > $end) {
                // wrap around to start of range
                $next = $start;
            }

            $counters[$id] = $next;
        }

        return $counters;
    }
    // Show view for thermal printer option
    public function printer(Request $request)
    {
        $branch = $request->route('branch') ?? $request->query('branch');
        $tickets = Ticket::when($branch, fn($q)=>$q->where('branch',$branch))->orderBy('created_at','asc')->get()->map(fn($t)=>$t->toArray())->all();
        return view('with_printer_new', ['categories' => self::categoriesList(), 'categoryCounters' => $this->categoryCounters($branch), 'branch' => $branch, 'tickets' => $tickets]);
    }

    // Show view for screen-only option
    public function screen(Request $request)
    {
        $branch = $request->route('branch') ?? $request->query('branch');
        $tickets = Ticket::when($branch, fn($q)=>$q->where('branch',$branch))->orderBy('created_at','asc')->get()->map(fn($t)=>$t->toArray())->all();
        return view('without_printer_new', ['categories' => self::categoriesList(), 'categoryCounters' => $this->categoryCounters($branch), 'branch' => $branch, 'tickets' => $tickets]);
    }

    // Show categories for given mode and priority
    public function categories($mode, $priority)
    {
        // filter categories by priority
        $all = self::categoriesList();
        $transactions = array_values(array_filter($all, function($c) use ($priority) {
            return ($c['priority'] ?? 'regular') === $priority;
        }));

        return view('categories', [
            'mode' => $mode,
            'priority' => $priority,
            'transactions' => $transactions,
        ]);
    }

    // Generate ticket and store in session
    public function generateTicket(Request $request)
    {
        $mode = $request->input('mode'); // 'printer' or 'screen'
        $priority = $request->input('priority'); // 'regular' or 'priority'
        $transaction = $request->input('transaction'); // this should be category id
        $branch = $request->route('branch') ?? $request->query('branch');

        // resolve category if provided as id
        $categoryName = $transaction;
        $categoryId = null;
        $allCategories = self::categoriesList();
        foreach ($allCategories as $c) {
            if ($c['id'] === $transaction) {
                $categoryName = $c['name'];
                $categoryId = $c['id'];
                // if priority not provided, derive from category
                $priority = $c['priority'] ?? $priority;
                break;
            }
        }

        // If a category id is provided, use per-category numbering based on category ranges
        $ticketNumber = null;
        if ($categoryId) {
            $counters = self::categoryCounters($branch);
            $next = $counters[$categoryId] ?? null;
            if ($next === null) {
                // fallback to rangeStart
                foreach ($allCategories as $c) {
                    if ($c['id'] === $categoryId) { $next = $c['rangeStart'] ?? 1; break; }
                }
            }
            $ticketNumber = (string) $next; // store as string for consistency
        } else {
            // session-based fallback counters per priority
            $key = "counters.{$priority}";
            $current = Session::get($key, 0) + 1;
            Session::put($key, $current);

            // Ticket number format: [P/R]-000X
            $prefix = strtoupper(substr($priority,0,1));
            $ticketNumber = sprintf('%s-%04d', $prefix, $current);
        }

        // Only use MySQL (Ticket model) for all ticket creation
        $t = new Ticket();
        $t->id = uniqid('t');
        $t->number = (string) $ticketNumber;
        $t->priority = $priority;
        $t->category = $categoryName;
        $t->category_id = $categoryId;
        $t->mode = $mode;
        $t->status = 'waiting';
        $t->branch = $branch;
        $t->save();
        $ticket = $t->toArray();

        // If the request expects JSON (AJAX from the no-thermal UI), return JSON
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            $tickets = Ticket::when($branch, fn($q)=>$q->where('branch',$branch))->orderBy('created_at','asc')->get();
            return response()->json([
                'ticket' => $ticket,
                'tickets' => $tickets,
                'categoryCounters' => $this->categoryCounters($branch),
            ], 201);
        }

        return redirect()->route('ticket.show');
    }

    public function showTicket()
    {
        $ticket = Session::get('last_ticket');
        if (! $ticket) {
            return redirect('/');
        }
        return view('ticket', ['ticket' => $ticket]);
    }

    /**
     * Add a new transaction category (stored in session for this app run).
     * Expects: name (string), priority ('regular'|'priority')
     */
    public function addCategory(Request $request)
    {
        $name = trim((string) $request->input('name', ''));
        $priority = $request->input('priority', 'regular');
        if ($name === '' || !in_array($priority, ['regular', 'priority'])) {
            return response()->json(['ok' => false, 'message' => 'Invalid name or priority'], 422);
        }

        // slug id from name
        $slug = strtolower(trim(preg_replace('/[^a-z0-9\-]+/i', '-', $name), '-'));
        if ($slug === '') $slug = uniqid('cat');

        // ensure unique id against existing categories
        $existing = array_map(function($c){ return $c['id']; }, self::categoriesList());
        $baseSlug = $slug;
        $i = 2;
        while (in_array($slug, $existing)) {
            $slug = $baseSlug.'-'.$i;
            $i++;
        }

        // Assign next range block: 1301-1399, 1401-1499, ...
        $BASE_START = 1301; // first block
        $STEP = 100;        // block step size
        $SPAN = 98;         // start + 98 => xx99
        $extras = Session::get('extra_categories', []);
        $maxIdx = -1;
        foreach ($extras as $e) {
            if (!empty($e['rangeStart']) && $e['rangeStart'] >= $BASE_START) {
                $idx = intdiv(($e['rangeStart'] - $BASE_START), $STEP);
                if ($idx > $maxIdx) $maxIdx = $idx;
            }
        }
        $nextIdx = $maxIdx + 1;
        $rangeStart = $BASE_START + ($STEP * $nextIdx);
        $rangeEnd = $rangeStart + $SPAN;

        $newCat = [
            'id' => $slug,
            'name' => $name,
            'priority' => $priority,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'color' => $priority === 'priority' ? 'red' : 'blue',
        ];

        $extras[] = $newCat;
        Session::put('extra_categories', $extras);

        return response()->json([
            'ok' => true,
            'category' => $newCat,
            'categories' => self::categoriesList(),
        ], 201);
    }

    /**
     * Remove a category by id. Built-ins are soft-removed via a session blacklist,
     * while session-added categories are deleted from the extras array.
     */
    public function removeCategory(Request $request)
    {
        $id = (string) $request->input('id', '');
        if ($id === '') {
            return response()->json(['ok' => false, 'message' => 'Missing category id'], 422);
        }

        // Prevent removal of built-in categories
        $baseIds = ['acop', 'membership-priority', 'medical', 'membership-regular', 'corporate', 'corporate-priority', 'pension-regular', 'pension-priority', 'ecenter-regular', 'ecenter-priority', 'mysss', 'welcome-back'];
        if (in_array($id, $baseIds)) {
            return response()->json(['ok' => false, 'message' => 'Cannot remove built-in categories'], 422);
        }

        // Remove from extras if present
        $extras = Session::get('extra_categories', []);
        $beforeCount = count($extras);
        $extras = array_values(array_filter($extras, function ($c) use ($id) { return ($c['id'] ?? '') !== $id; }));
        if (count($extras) !== $beforeCount) {
            Session::put('extra_categories', $extras);
        } else {
            // Not in extras: mark as removed (blacklist)
            $removed = Session::get('removed_categories', []);
            if (!in_array($id, $removed)) {
                $removed[] = $id;
                Session::put('removed_categories', $removed);
            }
        }

        return response()->json([
            'ok' => true,
            'categories' => self::categoriesList(),
        ]);
    }
}
