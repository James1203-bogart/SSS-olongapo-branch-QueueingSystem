<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class QueueController extends Controller
{
    public static function categoriesList()
    {
        return [
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
    }

    // Return next number per category based on session tickets and category ranges
    public static function categoryCounters()
    {
        $all = self::categoriesList();
        $tickets = Session::get('tickets', []);
        $counters = [];

        foreach ($all as $c) {
            $id = $c['id'];
            $start = $c['rangeStart'] ?? 1;
            $end = $c['rangeEnd'] ?? PHP_INT_MAX;

            // find max number already used for this category
            $max = null;
            foreach ($tickets as $t) {
                // match ticket to category by category_id when available,
                // otherwise try matching by category name or id for backwards compatibility
                $matchesCategory = false;
                if (isset($t['category_id']) && $t['category_id'] === $id) {
                    $matchesCategory = true;
                } elseif (!isset($t['category_id']) && isset($t['category']) && $t['category'] === $c['name']) {
                    $matchesCategory = true;
                } elseif (!isset($t['category_id']) && isset($t['category']) && $t['category'] === $id) {
                    $matchesCategory = true;
                }

                if (! $matchesCategory) {
                    continue;
                }
                
                // numeric ticket number possibility
                $num = null;
                if (is_numeric($t['number'])) {
                    $num = intval($t['number']);
                } else {
                    // try to extract trailing digits
                    if (preg_match('/(\d+)$/', $t['number'], $m)) {
                        $num = intval($m[1]);
                    }
                }
                if ($num !== null) {
                    if ($max === null || $num > $max) $max = $num;
                }
            }

            if ($max === null) {
                $next = $start;
            } else {
                $next = $max + 1;
            }

            if ($next > $end) {
                // if exceeded, set to end (could also wrap or error)
                $next = $end;
            }

            $counters[$id] = $next;
        }

        return $counters;
    }
    // Show view for thermal printer option
    public function printer()
    {
        return view('with_printer', ['categories' => self::categoriesList(), 'categoryCounters' => self::categoryCounters()]);
    }

    // Show view for screen-only option
    public function screen()
    {
        return view('without_printer', ['categories' => self::categoriesList(), 'categoryCounters' => self::categoryCounters()]);
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
            $counters = self::categoryCounters();
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

        $ticket = [
            'id' => uniqid('t'),
            'number' => $ticketNumber,
            'priority' => $priority,
            'category' => $categoryName,
            'category_id' => $categoryId,
            'mode' => $mode,
            'timestamp' => now()->toDateTimeString(),
            'status' => 'waiting',
        ];

        // store last ticket and append to tickets list in session
        Session::put('last_ticket', $ticket);
        $tickets = Session::get('tickets', []);
        $tickets[] = $ticket;
        Session::put('tickets', $tickets);

        // If the request expects JSON (AJAX from the no-thermal UI), return JSON
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ticket' => $ticket,
                'tickets' => $tickets,
                'categoryCounters' => self::categoryCounters(),
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
}
