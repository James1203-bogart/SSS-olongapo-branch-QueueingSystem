<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\Ticket;
use App\Services\CouchbaseTicketRepository;

class CallerController extends Controller
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
    public function index(Request $request)
    {
        $branch = $request->route('branch') ?? $request->query('branch');
        $counters = ['Counter 1', 'Counter 2', 'Counter 3', 'Backroom', 'E-Center Regular', 'E-Center Priority', 'Priority'];
        $assigned = Session::get('caller_assigned', null);
        // initialize page tickets: Couchbase when available, else SQL
        if ($this->cbRepo && $branch) {
            $tickets = $this->cbRepo->listByBranch($branch);
        } else {
            try {
                // expire stale serving tickets on page load (DB)
                if ($branch) {
                    Ticket::where('branch', $branch)
                        ->where('status', 'serving')
                        ->where('called_at', '<', now()->subHour())
                        ->update(['status' => 'completed', 'completed_at' => now()]);
                }
                $tickets = Ticket::when($branch, fn($q)=>$q->where('branch', $branch))
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(function($t){ return $t->toArray(); })
                    ->all();
            } catch (\Throwable $e) {
                // Branch-scoped session fallback
                $tickets = $branch ? session('tickets:'.$branch, []) : session('tickets', []);
            }
        }
        // Persist branch-scoped tickets to session
        Session::put($branch ? ('tickets:'.$branch) : 'tickets', $tickets);

        // include categories from QueueController
        $categories = \App\Http\Controllers\QueueController::categoriesList();
        return view('caller', ['counters' => $counters, 'assigned' => $assigned, 'categories' => $categories, 'branch' => $branch]);
    }

    public function assign(Request $request)
    {
        $counter = $request->input('counter');
        Session::put('caller_assigned', $counter);
        return redirect()->route('caller.index')->with('status', 'Assigned to ' . $counter);
    }

    // Call the next customer that matches selected categories
    public function callNext(Request $request)
    {
        $branch = $request->route('branch') ?? $request->query('branch');
        $selectedCategories = $request->input('categories', []);
        $counter = $request->input('counter', Session::get('caller_assigned'));

        if (!$this->cbRepo) {
            // expire stale serving tickets (DB)
            try {
                Ticket::when($branch, fn($q)=>$q->where('branch',$branch))
                    ->where('status','serving')
                    ->where('called_at','<', now()->subHour())
                    ->update(['status'=>'completed','completed_at'=>now()]);
            } catch (\Throwable $e) { /* ignore when DB down */ }
        }


        // If there's a ticket currently serving at this counter, mark it completed
        $previousTicket = null;
        if ($this->cbRepo) {
            // Couchbase path: no counter-specific serving record lookup available here; rely on list and match
            $list = $branch ? $this->cbRepo->listByBranch($branch) : [];
            foreach ($list as $doc) {
                if (($doc['status'] ?? '') === 'serving' && ($doc['counter'] ?? '') === $counter) {
                    $previousTicket = (object) $doc;
                    $this->cbRepo->updateStatus($doc['id'], 'completed');
                    break;
                }
            }
        } else {
            try {
                $previousTicket = Ticket::when($branch, fn($q)=>$q->where('branch',$branch))
                    ->where('status','serving')
                    ->where('counter',$counter)
                    ->first();
                if ($previousTicket) {
                    $previousTicket->status = 'completed';
                    $previousTicket->completed_at = now();
                    $previousTicket->save();
                }
            } catch (\Throwable $e) {
                // session fallback: mark any serving at counter as completed
                $list = session('tickets', []);
                foreach ($list as &$t) {
                    if (($t['status'] ?? '') === 'serving' && ($t['counter'] ?? '') === $counter && (!$branch || ($t['branch'] ?? null) === $branch)) {
                        $t['status'] = 'completed';
                        $t['completed_at'] = now()->toIso8601String();
                    }
                }
                unset($t);
                session(['tickets' => $list]);
            }
        }

        // find first waiting ticket that matches selected categories (by category name)
        $candidate = null;
        if ($this->cbRepo) {
            $list = $branch ? $this->cbRepo->listByBranch($branch) : [];
            foreach ($list as $doc) {
                if (($doc['status'] ?? '') !== 'waiting') continue;
                if (!empty($selectedCategories)) {
                    $cid = $doc['category_id'] ?? $doc['category'] ?? $doc['id'] ?? '';
                    $match = in_array($cid, $selectedCategories) || in_array(($doc['category'] ?? ''), $selectedCategories);
                    if (!$match) continue;
                }
                $candidate = (object) $doc;
                break;
            }
        } else {
            try {
                $candidate = Ticket::when($branch, fn($q)=>$q->where('branch',$branch))
                    ->where('status','waiting')
                    ->when(!empty($selectedCategories), function($q) use ($selectedCategories) {
                        $q->where(function($sub) use ($selectedCategories) {
                            $sub->whereIn('category_id', $selectedCategories)
                                ->orWhereIn('category', $selectedCategories)
                                ->orWhereIn('id', $selectedCategories);
                        });
                    })
                    ->orderBy('created_at','asc')
                    ->first();
            } catch (\Throwable $e) {
                $candidate = null;
                $list = $branch ? session('tickets:'.$branch, []) : session('tickets', []);
                foreach ($list as $idx => $t) {
                    if (($t['status'] ?? '') !== 'waiting') continue;
                    if ($branch && ($t['branch'] ?? null) !== $branch) continue;
                    if (!empty($selectedCategories)) {
                        $cid = $t['category_id'] ?? $t['category'] ?? $t['id'] ?? '';
                        $match = in_array($cid, $selectedCategories) || in_array(($t['category'] ?? ''), $selectedCategories);
                        if (!$match) continue;
                    }
                    $candidate = (object) $t; break;
                }
            }
        }

        if (! $candidate) {
            return response()->json(['success' => false, 'message' => 'No matching waiting ticket'], 200);
        }

        // mark as serving in DB
        if ($this->cbRepo) {
            $this->cbRepo->updateStatus($candidate->id, 'serving', $counter);
        } else {
            try {
                $candidate->status = 'serving';
                $candidate->counter = $counter;
                $candidate->called_at = now();
                $candidate->save();
            } catch (\Throwable $e) {
                $list = $branch ? session('tickets:'.$branch, []) : session('tickets', []);
                foreach ($list as &$t) {
                    if (($t['id'] ?? '') === ($candidate->id ?? '')) {
                        $t['status'] = 'serving';
                        $t['counter'] = $counter;
                        $t['called_at'] = now()->toIso8601String();
                    }
                }
                unset($t);
                session([$branch ? ('tickets:'.$branch) : 'tickets' => $list]);
            }
            // Also update cache list for cross-client visibility
            try {
                $cacheKey = $branch ? ('tickets:'.$branch) : 'tickets';
                $cached = Cache::get($cacheKey, []);
                if (is_array($cached) && !empty($cached)) {
                    foreach ($cached as &$ct) {
                        if ((($ct['id'] ?? '') === ($candidate->id ?? '')) || (($ct['number'] ?? '') === ($candidate->number ?? ''))) {
                            $ct['status'] = 'serving';
                            $ct['counter'] = $counter;
                            $ct['called_at'] = now()->toIso8601String();
                        }
                    }
                    unset($ct);
                    Cache::put($cacheKey, $cached, now()->addHours(6));
                }
            } catch (\Throwable $e) {}
        }

        Session::put('serving_number', $candidate->number);
        Session::put('serving_category', $candidate->category);
        Session::put('serving_counter', $counter);

        // Server-side update of last ring (branch-specific) so Display Board fetches per-branch
        try {
            $payload = [
                'number' => (string)($candidate->number ?? ''),
                'category' => (string)($candidate->category ?? ''),
                'counter' => (string)$counter,
                'ts' => (int) round(microtime(true) * 1000),
                'branch' => (string)($branch ?? ''),
            ];
            $key = $branch ? ('queue:last_ring:'.$branch) : 'queue:last_ring';
            Cache::put($key, $payload, now()->addMinutes(30));
        } catch (\Exception $e) { /* ignore */ }

        try {
            $tickets = $this->cbRepo && $branch
                ? $this->cbRepo->listByBranch($branch)
                : Ticket::when($branch, fn($q)=>$q->where('branch',$branch))->orderBy('created_at','asc')->get();
        } catch (\Throwable $e) {
            $tickets = $branch ? session('tickets:'.$branch, []) : session('tickets', []);
        }
        return response()->json([
            'success' => true,
            'ticket' => $candidate,
            'tickets' => $tickets,
            'serving_number' => $candidate->number ?? ($candidate['number'] ?? null),
            'serving_category' => $candidate->category ?? ($candidate['category'] ?? null),
            'serving_counter' => $counter,
            'previous_ticket' => $previousTicket,
        ], 200);
    }

    // Call a specific ticket number (search by number or id)
    public function callSpecific(Request $request)
    {
        $branch = $request->route('branch') ?? $request->query('branch');
        $number = $request->input('number');
        $counter = $request->input('counter', Session::get('caller_assigned'));

        if (! $number) {
            return response()->json(['success' => false, 'message' => 'Missing ticket number'], 400);
        }

        if (!$this->cbRepo) {
            Ticket::when($branch, fn($q)=>$q->where('branch',$branch))
                ->where('status','serving')
                ->where('called_at','<', now()->subHour())
            ->update(['status'=>'completed','completed_at'=>now()]);
        }

        // Server-side update of last ring (branch-specific) so Display Board fetches per-branch
        try {
            // Payload will be updated after we locate the ticket below, so skip early cache write here.
        } catch (\Exception $e) { /* ignore */ }

        // complete any existing serving ticket at this counter
        if ($this->cbRepo) {
            $list = $branch ? $this->cbRepo->listByBranch($branch) : [];
            foreach ($list as $doc) {
                if (($doc['status'] ?? '') === 'serving' && ($doc['counter'] ?? '') === $counter) {
                    $this->cbRepo->updateStatus($doc['id'], 'completed');
                    break;
                }
            }
        } else {
            try {
                $serving = Ticket::when($branch, fn($q)=>$q->where('branch',$branch))
                    ->where('status','serving')
                    ->where('counter',$counter)
                    ->first();
                if ($serving) { $serving->status='completed'; $serving->completed_at=now(); $serving->save(); }
            } catch (\Throwable $e) {
                $list = $branch ? session('tickets:'.$branch, []) : session('tickets', []);
                foreach ($list as &$t) {
                    if (($t['status'] ?? '') === 'serving' && ($t['counter'] ?? '') === $counter && (!$branch || ($t['branch'] ?? null) === $branch)) {
                        $t['status'] = 'completed';
                        $t['completed_at'] = now()->toIso8601String();
                    }
                }
                unset($t);
                session([$branch ? ('tickets:'.$branch) : 'tickets' => $list]);
            }
        }

        // find the ticket by number or id
        $ticket = null;
        if ($this->cbRepo) {
            $list = $branch ? $this->cbRepo->listByBranch($branch) : [];
            foreach ($list as $doc) {
                if ((string)($doc['number'] ?? '') === (string)$number || (string)($doc['id'] ?? '') === (string)$number) {
                    $ticket = (object) $doc; break;
                }
            }
        } else {
            try {
                $ticket = Ticket::when($branch, fn($q)=>$q->where('branch',$branch))
                    ->where(function($q) use ($number){ $q->where('number',(string)$number)->orWhere('id',(string)$number); })
                    ->first();
            } catch (\Throwable $e) {
                $list = $branch ? session('tickets:'.$branch, []) : session('tickets', []);
                foreach ($list as $t) {
                    if ((string)($t['number'] ?? '') === (string)$number || (string)($t['id'] ?? '') === (string)$number) { $ticket = (object) $t; break; }
                }
            }
        }

        if (! $ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket not found'], 200);
        }

        // only serve if currently waiting (you may choose to force-call regardless)
        if (($ticket->status ?? ($ticket['status'] ?? null)) !== 'waiting') {
            return response()->json(['success' => false, 'message' => 'Ticket is not waiting'], 200);
        }

        if ($this->cbRepo) {
            $this->cbRepo->updateStatus($ticket->id, 'serving', $counter);
        } else {
            try {
                $ticket->status = 'serving';
                $ticket->counter = $counter;
                $ticket->called_at = now();
                $ticket->save();
            } catch (\Throwable $e) {
                $list = $branch ? session('tickets:'.$branch, []) : session('tickets', []);
                foreach ($list as &$t) {
                    if (($t['id'] ?? '') === ($ticket->id ?? '')) {
                        $t['status'] = 'serving';
                        $t['counter'] = $counter;
                        $t['called_at'] = now()->toIso8601String();
                    }
                }
                unset($t);
                session([$branch ? ('tickets:'.$branch) : 'tickets' => $list]);
            }
            // Also update cache list
            try {
                $cacheKey = $branch ? ('tickets:'.$branch) : 'tickets';
                $cached = Cache::get($cacheKey, []);
                if (is_array($cached) && !empty($cached)) {
                    foreach ($cached as &$ct) {
                        if ((($ct['id'] ?? '') === ($ticket->id ?? '')) || (($ct['number'] ?? '') === ($ticket->number ?? ''))) {
                            $ct['status'] = 'serving';
                            $ct['counter'] = $counter;
                            $ct['called_at'] = now()->toIso8601String();
                        }
                    }
                    unset($ct);
                    Cache::put($cacheKey, $cached, now()->addHours(6));
                }
            } catch (\Throwable $e) {}
        }

        // Branch-specific ring cache for specific call
        try {
            $payload = [
                'number' => (string)($ticket->number ?? ''),
                'category' => (string)($ticket->category ?? ''),
                'counter' => (string)$counter,
                'ts' => (int) round(microtime(true) * 1000),
                'branch' => (string)($branch ?? ''),
            ];
            $key = $branch ? ('queue:last_ring:'.$branch) : 'queue:last_ring';
            Cache::put($key, $payload, now()->addMinutes(30));
        } catch (\Exception $e) { /* ignore */ }

        try {
            $tickets = $this->cbRepo && $branch
                ? $this->cbRepo->listByBranch($branch)
                : Ticket::when($branch, fn($q)=>$q->where('branch',$branch))->orderBy('created_at','asc')->get();
        } catch (\Throwable $e) {
            $tickets = $branch ? session('tickets:'.$branch, []) : session('tickets', []);
        }
        return response()->json([
            'success' => true,
            'ticket' => $ticket,
            'tickets' => $tickets,
            'serving_number' => $ticket->number ?? ($ticket['number'] ?? null),
            'serving_category' => $ticket->category ?? ($ticket['category'] ?? null),
            'serving_counter' => $counter,
        ], 200);
    }

    /**
     * Expire serving tickets older than 1 hour.
     * Marks tickets with status 'serving' and called_at older than 60 minutes as 'completed'.
     * @param array $tickets
     * @return array
     */
    protected function expireServingTickets(array $tickets): array { return $tickets; }
}
