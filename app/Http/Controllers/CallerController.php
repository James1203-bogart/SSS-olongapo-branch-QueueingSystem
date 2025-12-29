<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Ticket;

class CallerController extends Controller
{
    public function index(Request $request)
    {
        $branch = $request->route('branch') ?? $request->query('branch');
        $counters = ['Counter 1', 'Counter 2', 'Counter 3', 'Backroom', 'E-Center Regular', 'E-Center Priority', 'Priority', 'Medical']; // Medical is now always included
        $assigned = null;
        // expire stale serving tickets on page load (DB)
        if ($branch) {
            Ticket::where('branch', $branch)
                ->where('status', 'serving')
                ->where('called_at', '<', now()->subHour())
                ->update(['status' => 'completed', 'completed_at' => now()]);
                // Reset all tickets at/after 9pm
                $now = now();
                if ($now->hour >= 21) {
                    // Mark all tickets as completed
                    Ticket::where('branch', $branch)
                        ->whereIn('status', ['waiting', 'serving'])
                        ->update(['status' => 'completed', 'completed_at' => $now]);
                    // Delete all tickets for this branch so new tickets start at initial range
                    Ticket::where('branch', $branch)->delete();
                }
        }
        $tickets = Ticket::when($branch, fn($q)=>$q->where('branch', $branch))
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($t){ return $t->toArray(); })
            ->all();

        // include categories from QueueController
        $categories = \App\Http\Controllers\QueueController::categoriesList();
        return view('caller', ['counters' => $counters, 'assigned' => $assigned, 'categories' => $categories, 'branch' => $branch, 'tickets' => $tickets]);
    }

    public function assign(Request $request)
    {
        $counter = $request->input('counter');
        // Assignment logic removed (no session)
        return redirect()->route('caller.index')->with('status', 'Assigned to ' . $counter);
    }

    // Call the next customer that matches selected categories
    public function callNext(Request $request)
    {
        $branch = $request->route('branch') ?? $request->query('branch');
        $selectedCategories = $request->input('categories', []);
        $counter = $request->input('counter');
        // expire stale serving tickets (DB)
        Ticket::when($branch, fn($q)=>$q->where('branch',$branch))
            ->where('status','serving')
            ->where('called_at','<', now()->subHour())
            ->update(['status'=>'completed','completed_at'=>now()]);
        // If there's a ticket currently serving at this counter, mark it completed
        $previousTicket = Ticket::when($branch, fn($q)=>$q->where('branch',$branch))
            ->where('status','serving')
            ->where('counter',$counter)
            ->first();
        if ($previousTicket) {
            $previousTicket->status = 'completed';
            $previousTicket->completed_at = now();
            $previousTicket->save();
        }

        // find first waiting ticket that matches selected categories (by category name)
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

        if (! $candidate) {
            return response()->json(['success' => false, 'message' => 'No matching waiting ticket'], 200);
        }
        // mark as serving in DB
        $candidate->status = 'serving';
        $candidate->counter = $counter;
        $candidate->called_at = now();
        $candidate->save();
        $tickets = Ticket::when($branch, fn($q)=>$q->where('branch',$branch))->orderBy('created_at','asc')->get();
        return response()->json([
            'success' => true,
            'ticket' => $candidate,
            'tickets' => $tickets,
            'serving_number' => $candidate->number,
            'serving_category' => $candidate->category,
            'serving_counter' => $counter,
            'previous_ticket' => $previousTicket,
        ], 200);
    }

    // Call a specific ticket number (search by number or id)
    public function callSpecific(Request $request)
    {
        $branch = $request->route('branch') ?? $request->query('branch');
        $number = $request->input('number');
        $counter = $request->input('counter');
        if (! $number) {
            return response()->json(['success' => false, 'message' => 'Missing ticket number'], 400);
        }
        Ticket::when($branch, fn($q)=>$q->where('branch',$branch))
            ->where('status','serving')
            ->where('called_at','<', now()->subHour())
            ->update(['status'=>'completed','completed_at'=>now()]);
        // complete any existing serving ticket at this counter
        $serving = Ticket::when($branch, fn($q)=>$q->where('branch',$branch))
            ->where('status','serving')
            ->where('counter',$counter)
            ->first();
        if ($serving) {
            $serving->status='completed';
            $serving->completed_at=now();
            $serving->save();
        }

        // find the ticket by number or id
        $ticket = Ticket::when($branch, fn($q)=>$q->where('branch',$branch))
            ->where(function($q) use ($number){ $q->where('number',(string)$number)->orWhere('id',(string)$number); })
            ->first();

        if (! $ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket not found'], 200);
        }
        // only serve if currently waiting (you may choose to force-call regardless)
        if ($ticket->status !== 'waiting') {
            return response()->json(['success' => false, 'message' => 'Ticket is not waiting'], 200);
        }
        $ticket->status = 'serving';
        $ticket->counter = $counter;
        $ticket->called_at = now();
        $ticket->save();
        $tickets = Ticket::when($branch, fn($q)=>$q->where('branch',$branch))->orderBy('created_at','asc')->get();
        return response()->json([
            'success' => true,
            'ticket' => $ticket,
            'tickets' => $tickets,
            'serving_number' => $ticket->number,
            'serving_category' => $ticket->category,
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
