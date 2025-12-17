<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class CallerController extends Controller
{
    public function index()
    {
        $counters = ['Counter 1', 'Counter 2', 'Counter 3', 'Backroom', 'E-Center Regular', 'E-Center Priority', 'Priority'];
        $assigned = Session::get('caller_assigned', null);
        // expire stale serving tickets on page load
        $tickets = Session::get('tickets', []);
        $tickets = $this->expireServingTickets($tickets);
        Session::put('tickets', $tickets);

        // include categories from QueueController
        $categories = \App\Http\Controllers\QueueController::categoriesList();
        return view('caller', ['counters' => $counters, 'assigned' => $assigned, 'categories' => $categories]);
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
        $selectedCategories = $request->input('categories', []);
        $counter = $request->input('counter', Session::get('caller_assigned'));

        // expire stale serving tickets (e.g., serving > 1 hour)
        $tickets = Session::get('tickets', []);
        $tickets = $this->expireServingTickets($tickets);
        Session::put('tickets', $tickets);


        // If there's a ticket currently serving at this counter, mark it completed
        $previousIndex = null;
        foreach ($tickets as $i => $t) {
            if ((($t['status'] ?? '') === 'serving') && isset($t['counter']) && $t['counter'] == $counter) {
                $previousIndex = $i;
                break;
            }
        }
        if ($previousIndex !== null) {
            $tickets[$previousIndex]['status'] = 'completed';
            $tickets[$previousIndex]['completed_at'] = now()->toDateTimeString();
        }

        // find first waiting ticket that matches selected categories (by category name)
        $index = null;
        foreach ($tickets as $i => $t) {
            if (($t['status'] ?? 'waiting') === 'waiting') {
                $matches = false;
                if (empty($selectedCategories)) {
                    $matches = true;
                } else {
                    // check by category id or category name
                    if (isset($t['category_id']) && in_array($t['category_id'], $selectedCategories)) {
                        $matches = true;
                    } elseif (in_array($t['category'], $selectedCategories)) {
                        $matches = true;
                    } elseif (in_array($t['id'], $selectedCategories)) {
                        $matches = true;
                    }
                }

                if ($matches) {
                    $index = $i;
                    break;
                }
            }
        }

        if ($index === null) {
            return response()->json(['success' => false, 'message' => 'No matching waiting ticket'], 200);
        }

        // mark as serving
        $tickets[$index]['status'] = 'serving';
        $tickets[$index]['counter'] = $counter;
        $tickets[$index]['called_at'] = now()->toDateTimeString();
        // Do NOT auto-complete any other waiting ticket.

        // persist session
        Session::put('tickets', $tickets);
        Session::put('serving_number', $tickets[$index]['number']);
        Session::put('serving_category', $tickets[$index]['category']);
        Session::put('serving_counter', $counter);

        return response()->json([
            'success' => true,
            'ticket' => $tickets[$index],
            'tickets' => $tickets,
            'serving_number' => $tickets[$index]['number'],
            'serving_category' => $tickets[$index]['category'],
            'serving_counter' => $counter,
            'previous_ticket' => $previousIndex !== null ? $tickets[$previousIndex] : null,
        ], 200);
    }

    // Call a specific ticket number (search by number or id)
    public function callSpecific(Request $request)
    {
        $number = $request->input('number');
        $counter = $request->input('counter', Session::get('caller_assigned'));

        if (! $number) {
            return response()->json(['success' => false, 'message' => 'Missing ticket number'], 400);
        }

        // expire stale serving tickets before assigning specific
        $tickets = Session::get('tickets', []);
        $tickets = $this->expireServingTickets($tickets);
        Session::put('tickets', $tickets);

        // complete any existing serving ticket at this counter
        foreach ($tickets as $i => $t) {
            if ((($t['status'] ?? '') === 'serving') && isset($t['counter']) && $t['counter'] == $counter) {
                $tickets[$i]['status'] = 'completed';
                $tickets[$i]['completed_at'] = now()->toDateTimeString();
                break;
            }
        }

        // find the ticket by number or id
        $index = null;
        foreach ($tickets as $i => $t) {
            if ((($t['number'] ?? '') === (string)$number) || (($t['id'] ?? '') === (string)$number)) {
                $index = $i; break;
            }
        }

        if ($index === null) {
            return response()->json(['success' => false, 'message' => 'Ticket not found'], 200);
        }

        // only serve if currently waiting (you may choose to force-call regardless)
        if (($tickets[$index]['status'] ?? '') !== 'waiting') {
            return response()->json(['success' => false, 'message' => 'Ticket is not waiting'], 200);
        }

        $tickets[$index]['status'] = 'serving';
        $tickets[$index]['counter'] = $counter;
        $tickets[$index]['called_at'] = now()->toDateTimeString();

        Session::put('tickets', $tickets);

        return response()->json([
            'success' => true,
            'ticket' => $tickets[$index],
            'tickets' => $tickets,
            'serving_number' => $tickets[$index]['number'],
            'serving_category' => $tickets[$index]['category'],
            'serving_counter' => $counter,
        ], 200);
    }

    /**
     * Expire serving tickets older than 1 hour.
     * Marks tickets with status 'serving' and called_at older than 60 minutes as 'completed'.
     * @param array $tickets
     * @return array
     */
    protected function expireServingTickets(array $tickets): array
    {
        $now = Carbon::now();
        $changed = false;
        foreach ($tickets as $i => $t) {
            if (($t['status'] ?? '') === 'serving') {
                $calledAt = null;
                if (!empty($t['called_at'])) {
                    try { $calledAt = Carbon::parse($t['called_at']); } catch (\Exception $e) { $calledAt = null; }
                }
                // fallback to timestamp if called_at missing
                if ($calledAt === null && !empty($t['timestamp'])) {
                    try { $calledAt = Carbon::parse($t['timestamp']); } catch (\Exception $e) { $calledAt = null; }
                }

                if ($calledAt !== null) {
                    $minutes = $calledAt->diffInMinutes($now);
                    if ($minutes >= 60) {
                        $tickets[$i]['status'] = 'completed';
                        $tickets[$i]['completed_at'] = $now->toDateTimeString();
                        $changed = true;
                    }
                }
            }
        }
        return $tickets;
    }
}
