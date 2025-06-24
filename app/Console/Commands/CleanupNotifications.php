<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use Carbon\Carbon;

class CleanupNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup 
                            {--days=30 : Number of days to keep read notifications}
                            {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old and expired notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting notification cleanup...');

        $days = $this->option('days');
        $force = $this->option('force');

        // Show what will be cleaned
        $expiredCount = Notification::where('expires_at', '<', now())->count();
        $oldReadCount = Notification::where('is_read', true)
            ->where('read_at', '<', now()->subDays($days))
            ->count();

        $this->info("Expired notifications to delete: {$expiredCount}");
        $this->info("Old read notifications to delete (older than {$days} days): {$oldReadCount}");
        $this->info("Total notifications to delete: " . ($expiredCount + $oldReadCount));

        if (!$force && !$this->confirm('Do you want to proceed with the cleanup?')) {
            $this->info('Cleanup cancelled.');
            return;
        }

        // Perform cleanup
        try {
            // Delete expired notifications
            $deletedExpired = Notification::where('expires_at', '<', now())->delete();
            
            // Delete old read notifications
            $deletedOldRead = Notification::where('is_read', true)
                ->where('read_at', '<', now()->subDays($days))
                ->delete();

            $total = $deletedExpired + $deletedOldRead;

            $this->info("Successfully deleted {$deletedExpired} expired notifications");
            $this->info("Successfully deleted {$deletedOldRead} old read notifications");
            $this->info("Total deleted: {$total} notifications");

            // Show remaining count
            $remaining = Notification::count();
            $this->info("Remaining notifications: {$remaining}");

        } catch (\Exception $e) {
            $this->error('Error during cleanup: ' . $e->getMessage());
            return 1;
        }

        $this->info('Notification cleanup completed successfully!');
        return 0;
    }
}