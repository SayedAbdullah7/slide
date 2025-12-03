<?php

namespace App\Console\Commands;

use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;

class CleanupInvalidFcmTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:cleanup {--days=30 : Number of days to keep inactive tokens}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up invalid and old FCM tokens';

    protected FirebaseNotificationService $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        parent::__construct();
        $this->firebaseService = $firebaseService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting FCM token cleanup...');

        try {
            $days = $this->option('days');
            $cleanedCount = $this->firebaseService->cleanupInvalidTokens();

            $this->info("Successfully cleaned up {$cleanedCount} invalid FCM tokens older than {$days} days.");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error during FCM token cleanup: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
