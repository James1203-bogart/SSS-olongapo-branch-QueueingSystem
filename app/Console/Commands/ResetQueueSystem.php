<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetQueueSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:reset-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets the queueing system database and counters to initial state.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Truncate tickets, jobs, and any other relevant tables
        DB::table('tickets')->truncate();
        DB::table('jobs')->truncate();
        // Reset any counters or stats tables if needed
        // Example: DB::table('category_counters')->update(['counter' => 0]);
        $this->info('Queueing system has been reset.');
    }
}
