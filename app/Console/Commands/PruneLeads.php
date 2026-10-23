<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;

class PruneLeads extends Command
{
    protected $signature = 'starter:prune-leads {--execute : Delete expired leads; without this option only count them}';

    protected $description = 'Apply the configured lead retention period (dry-run by default)';

    public function handle(): int
    {
        $days = config('starter.leads.retention_days');
        if ($days < 1) {
            $this->warn('Retention disabled.');

            return self::SUCCESS;
        }
        $query = Lead::where('created_at', '<', now()->subDays($days));
        $this->info($query->count().' lead(s) older than '.$days.' days.');
        if ($this->option('execute')) {
            $query->delete();
            $this->info('Expired leads deleted.');
        }

        return self::SUCCESS;
    }
}
