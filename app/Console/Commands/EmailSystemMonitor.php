<?php

// File: app/Console/Commands/EmailSystemMonitor.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailSystemMonitor extends Command
{
    protected $signature = 'email:monitor {--fix : Fix failed jobs automatically}';
    protected $description = 'Monitor email system health and performance';

    public function handle()
    {
        $this->info('🔍 Monitoring Email System...');
        $this->line('');

        // 1. Check Queue Status
        $this->checkQueueStatus();
        
        // 2. Check Failed Jobs
        $this->checkFailedJobs();
        
        // 3. Check Email Configuration
        $this->checkEmailConfig();
        
        // 4. Test Email Connectivity
        $this->testEmailConnectivity();

        // 5. Fix issues if --fix option is used
        if ($this->option('fix')) {
            $this->fixIssues();
        }

        $this->line('');
        $this->info('✅ Email System Monitoring Complete!');
    }

    private function checkQueueStatus()
    {
        $this->info('📊 Queue Status:');
        
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        
        $this->line("  Pending Jobs: {$pendingJobs}");
        $this->line("  Failed Jobs: {$failedJobs}");
        
        if ($pendingJobs > 100) {
            $this->warn("  ⚠️  High number of pending jobs! Consider adding more workers.");
        }
        
        if ($failedJobs > 0) {
            $this->error("  ❌ There are {$failedJobs} failed jobs!");
        } else {
            $this->line("  ✅ No failed jobs");
        }
        
        $this->line('');
    }

    private function checkFailedJobs()
    {
        $this->info('🔍 Failed Jobs Analysis:');
        
        $failedJobs = DB::table('failed_jobs')
            ->select('payload', 'exception', 'failed_at')
            ->orderBy('failed_at', 'desc')
            ->limit(5)
            ->get();
            
        if ($failedJobs->isEmpty()) {
            $this->line("  ✅ No failed jobs to analyze");
        } else {
            foreach ($failedJobs as $job) {
                $payload = json_decode($job->payload, true);
                $jobName = $payload['displayName'] ?? 'Unknown Job';
                $this->line("  ❌ {$jobName} - Failed at: {$job->failed_at}");
                
                // Show first line of exception
                $exceptionLines = explode("\n", $job->exception);
                $this->line("     Error: " . ($exceptionLines[0] ?? 'Unknown error'));
            }
        }
        
        $this->line('');
    }

    private function checkEmailConfig()
    {
        $this->info('⚙️  Email Configuration:');
        
        $configs = [
            'MAIL_MAILER' => config('mail.default'),
            'MAIL_HOST' => config('mail.mailers.smtp.host'),
            'MAIL_PORT' => config('mail.mailers.smtp.port'),
            'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
            'MAIL_FROM_ADDRESS' => config('mail.from.address'),
            'SUPERVISOR_EMAIL' => env('SUPERVISOR_EMAIL'),
        ];
        
        foreach ($configs as $key => $value) {
            if (empty($value)) {
                $this->error("  ❌ {$key} is not configured");
            } else {
                // Hide sensitive data
                if (in_array($key, ['MAIL_USERNAME', 'MAIL_FROM_ADDRESS', 'SUPERVISOR_EMAIL'])) {
                    $value = substr($value, 0, 3) . '***' . substr($value, -5);
                }
                $this->line("  ✅ {$key}: {$value}");
            }
        }
        
        $this->line('');
    }

    private function testEmailConnectivity()
    {
        $this->info('📧 Testing Email Connectivity:');
        
        try {
            // Test SMTP connection without sending email
            $transport = Mail::getSwiftMailer()->getTransport();
            
            if (method_exists($transport, 'start')) {
                $transport->start();
                $this->line("  ✅ SMTP connection successful");
            } else {
                $this->line("  ℹ️  Cannot test SMTP connection (transport type: " . get_class($transport) . ")");
            }
            
        } catch (\Exception $e) {
            $this->error("  ❌ SMTP connection failed: " . $e->getMessage());
        }
        
        $this->line('');
    }

    private function fixIssues()
    {
        $this->info('🔧 Attempting to fix issues...');
        
        // 1. Retry failed jobs
        $failedJobs = DB::table('failed_jobs')->count();
        if ($failedJobs > 0) {
            $this->line("  🔄 Retrying {$failedJobs} failed jobs...");
            $this->call('queue:retry', ['id' => 'all']);
        }
        
        // 2. Clear old failed jobs (older than 7 days)
        $deleted = DB::table('failed_jobs')
            ->where('failed_at', '<', now()->subDays(7))
            ->delete();
            
        if ($deleted > 0) {
            $this->line("  🗑️  Cleaned up {$deleted} old failed jobs");
        }
        
        // 3. Restart queue workers
        $this->line("  🔄 Restarting queue workers...");
        $this->call('queue:restart');
        
        $this->line('');
    }
}

// File: app/Console/Commands/EmailStats.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\LeaveApplication;

class EmailStats extends Command
{
    protected $signature = 'email:stats {--days=7 : Number of days to analyze}';
    protected $description = 'Show email statistics';

    public function handle()
    {
        $days = $this->option('days');
        $this->info("📈 Email Statistics (Last {$days} days)");
        $this->line('');

        // Applications created (triggers new application emails)
        $newApplications = LeaveApplication::where('created_at', '>=', now()->subDays($days))->count();
        $this->line("📋 New Applications: {$newApplications}");

        // Status changes (triggers status update emails)
        $statusChanges = LeaveApplication::where('approved_at', '>=', now()->subDays($days))
            ->whereNotNull('approved_at')
            ->count();
        $this->line("🔄 Status Changes: {$statusChanges}");

        // Queue job statistics
        $totalJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        
        $this->line("📊 Queue Statistics:");
        $this->line("  Pending Jobs: {$totalJobs}");
        $this->line("  Failed Jobs: {$failedJobs}");
        
        if ($totalJobs + $failedJobs > 0) {
            $successRate = round((($totalJobs + $failedJobs - $failedJobs) / ($totalJobs + $failedJobs)) * 100, 2);
            $this->line("  Success Rate: {$successRate}%");
        }

        $this->line('');
        $this->info('✅ Statistics Complete!');
    }
}