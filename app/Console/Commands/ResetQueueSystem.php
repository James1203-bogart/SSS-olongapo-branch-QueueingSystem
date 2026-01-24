<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;

class ResetQueueSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:reset-system {--branch=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily queue reset: complete and purge tickets (optionally per branch) and clear last ring cache';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $branch = $this->option('branch');
        $tz = config('app.timezone', 'UTC');
        $now = now()->timezone($tz);

        // Enforce allowed window: 18:00–19:00 in app timezone
        $start = (clone $now)->setTime(18, 0, 0);
        $end = (clone $now)->setTime(19, 0, 0);
        if ($now->lt($start) || $now->gte($end)) {
            $this->warn("Skipped: outside allowed window 18:00–19:00 ({$tz}). Now: ".$now->toDateTimeString());
            return Command::SUCCESS;
        }

        // Ensure this runs only once per day (per branch)
        $doneKey = 'queue:reset-done:'.$now->toDateString().($branch ? (':'.$branch) : '');
        if (Cache::get($doneKey)) {
            $this->info('Reset already performed for today'.($branch?" (branch {$branch})":'').'.');
            return Command::SUCCESS;
        }

        $query = Ticket::query();
        if ($branch) { $query->where('branch', $branch); }

        // Mark any active tickets as completed before purge (for reporting if needed)
        $updated = (clone $query)
            ->whereIn('status', ['waiting', 'serving'])
            ->update(['status' => 'completed', 'completed_at' => $now]);
        $this->info("Completed tickets updated: {$updated}");

        // Purge all tickets (per product spec to restart numbering ranges)
        $deleted = $query->delete();
        $this->info("Tickets deleted: {$deleted}");

        // Clear last ring cache so boards don\'t show stale items
        try {
            if ($branch) {
                Cache::forget('queue:last_ring:'.$branch);
            } else {
                Cache::forget('queue:last_ring');
            }
        } catch (\Throwable $e) { /* ignore */ }

        $this->info('Queue reset complete'.($branch ? " for branch {$branch}" : ''));

        // Mark as done for today
        try {
            // expire shortly after day end
            $expiresAt = (clone $now)->endOfDay()->addMinutes(5);
            Cache::put($doneKey, 1, $expiresAt);
        } catch (\Throwable $e) { /* ignore */ }
        return Command::SUCCESS;
    }
}

