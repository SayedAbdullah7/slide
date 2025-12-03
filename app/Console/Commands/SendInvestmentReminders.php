<?php

namespace App\Console\Commands;

use App\Services\InvestmentOpportunityReminderService;
use Illuminate\Console\Command;

class SendInvestmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send {--cleanup : Clean up old reminders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for investment opportunities that became available';

    protected InvestmentOpportunityReminderService $reminderService;

    public function __construct(InvestmentOpportunityReminderService $reminderService)
    {
        parent::__construct();
        $this->reminderService = $reminderService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to process investment opportunity reminders...');

        try {
            // Send reminders
            $sentCount = $this->reminderService->sendReminders();

            $this->info("Successfully sent {$sentCount} reminders.");

            // Clean up old reminders if requested
            if ($this->option('cleanup')) {
                $cleanedCount = $this->reminderService->cleanupOldReminders();
                $this->info("Cleaned up {$cleanedCount} old reminders.");
            }

            $this->info('Reminder processing completed successfully.');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error processing reminders: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
