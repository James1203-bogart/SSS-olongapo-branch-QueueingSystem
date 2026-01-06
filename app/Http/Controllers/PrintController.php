<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EscposPrinterService;

class PrintController extends Controller
{
    public function printEscpos(Request $request)
    {
        $ticket = [
            'number' => $request->input('number'),
            'category' => $request->input('category'),
            'transaction' => $request->input('transaction'),
            'priority' => $request->input('priority'),
            'generated_at' => $request->input('generated_at') ?? now()->format('Y-m-d H:i:s'),
        ];

        $svc = app(EscposPrinterService::class);
        $result = $svc->printTicket($ticket);

        return response()->json($result);
    }
}
