<?php

namespace App\Console\Commands;

use App\Models\InvestmentOpportunity;
use Illuminate\Console\Command;

class UpdateInvestmentOpportunityStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opportunities:update-status {--force : Force update all opportunities}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update investment opportunity statuses dynamically based on dates and conditions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting investment opportunity status update...');

        $query = InvestmentOpportunity::query();

        if (!$this->option('force')) {
            // Only update opportunities that need status updates
            $query->where(function ($q) {
                $q->where('status', '!=', 'completed')
                  ->where('status', '!=', 'suspended');
            });
        }

        $opportunities = $query->get();
        $updated = 0;

        $this->withProgressBar($opportunities, function ($opportunity) use (&$updated) {
            if ($opportunity->shouldUpdateStatus()) {
                $oldStatus = $opportunity->status;
                $opportunity->updateDynamicStatus();
                $newStatus = $opportunity->status;

                if ($oldStatus !== $newStatus) {
                    $updated++;
                    $this->line("\nUpdated opportunity '{$opportunity->name}' from '{$oldStatus}' to '{$newStatus}'");
                }
            }
        });

        $this->newLine();
        $this->info("Status update completed. Updated {$updated} opportunities out of {$opportunities->count()} total.");

        // Show status distribution
        $this->showStatusDistribution();

        return Command::SUCCESS;
    }

    /**
     * Show current status distribution
     */
    private function showStatusDistribution()
    {
        $this->newLine();
        $this->info('Current Status Distribution:');

        $statuses = InvestmentOpportunity::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get();

        foreach ($statuses as $status) {
            $this->line("  {$status->status}: {$status->count}");
        }
    }
}
