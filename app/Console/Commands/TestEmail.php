<?php

// Buat file: app/Console/Commands/TestEmail.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewLeaveApplicationMail;
use App\Mail\LeaveStatusUpdateMail;
use App\Models\LeaveApplication;

class TestEmail extends Command
{
    protected $signature = 'email:test {type} {email}';
    protected $description = 'Test email functionality';

    public function handle()
    {
        $type = $this->argument('type');
        $email = $this->argument('email');

        try {
            // Ambil data leave application untuk test
            $leaveApplication = LeaveApplication::first();
            
            if (!$leaveApplication) {
                $this->error('No leave application found. Please create one first.');
                return 1;
            }

            if ($type === 'new') {
                // Test email pengajuan baru
                Mail::to($email)->send(new NewLeaveApplicationMail($leaveApplication));
                $this->info('New leave application email sent successfully to: ' . $email);
            } elseif ($type === 'status') {
                // Test email update status
                Mail::to($email)->send(new LeaveStatusUpdateMail($leaveApplication, 'pending'));
                $this->info('Status update email sent successfully to: ' . $email);
            } else {
                $this->error('Invalid type. Use "new" or "status"');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}