<?php

namespace App\Observers;

use App\Models\LeaveApplication;
use Illuminate\Support\Facades\Log;

class LeaveApplicationObserver
{
    /**
     * Handle the LeaveApplication "created" event.
     */
    public function created(LeaveApplication $leaveApplication): void
    {
        try {
            // Log the creation
            Log::info('New leave application created', [
                'leave_id' => $leaveApplication->id,
                'applicant' => $leaveApplication->name,
                'leave_type' => $leaveApplication->leave_type,
                'user_id' => $leaveApplication->user_id
            ]);

            // Create notification for admins (if notification system exists)
            $this->createLeaveNotification($leaveApplication, 'new_leave');
            
        } catch (\Exception $e) {
            Log::error('Error in LeaveApplicationObserver created: ' . $e->getMessage(), [
                'leave_id' => $leaveApplication->id
            ]);
        }
    }

    /**
     * Handle the LeaveApplication "updated" event.
     */
    public function updated(LeaveApplication $leaveApplication): void
    {
        try {
            // Check if status was changed
            if ($leaveApplication->isDirty('status')) {
                $oldStatus = $leaveApplication->getOriginal('status');
                $newStatus = $leaveApplication->status;
                
                Log::info('Leave application status changed', [
                    'leave_id' => $leaveApplication->id,
                    'applicant' => $leaveApplication->name,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'approved_by' => $leaveApplication->approved_by
                ]);

                // Create notification based on new status
                if (in_array($newStatus, ['approved', 'rejected', 'processed'])) {
                    $this->createLeaveNotification($leaveApplication, 'leave_' . $newStatus);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in LeaveApplicationObserver updated: ' . $e->getMessage(), [
                'leave_id' => $leaveApplication->id
            ]);
        }
    }

    /**
     * Handle the LeaveApplication "deleted" event.
     */
    public function deleted(LeaveApplication $leaveApplication): void
    {
        try {
            Log::info('Leave application deleted', [
                'leave_id' => $leaveApplication->id,
                'applicant' => $leaveApplication->name,
                'status' => $leaveApplication->status
            ]);

            // Clean up related notifications if notification system exists
            $this->cleanupLeaveNotifications($leaveApplication);
            
        } catch (\Exception $e) {
            Log::error('Error in LeaveApplicationObserver deleted: ' . $e->getMessage(), [
                'leave_id' => $leaveApplication->id
            ]);
        }
    }

    /**
     * Create leave notification (if notification system exists)
     */
    private function createLeaveNotification($leaveApplication, $type)
    {
        try {
            // Check if Notification model exists
            if (!class_exists('\App\Models\Notification')) {
                return; // Skip if notification system not implemented
            }

            $notificationClass = '\App\Models\Notification';
            
            switch ($type) {
                case 'new_leave':
                    // Notify all admins about new leave application
                    $adminUsers = \App\Models\User::whereHas('role', function($q) {
                        $q->where('name', 'admin');
                    })->orWhere('role_id', 1)->get();

                    foreach ($adminUsers as $admin) {
                        $notificationClass::create([
                            'user_id' => $admin->id,
                            'type' => 'new_leave',
                            'title' => 'Pengajuan Cuti Baru',
                            'message' => "Pengajuan cuti dari {$leaveApplication->name} perlu validasi",
                            'data' => [
                                'leave_id' => $leaveApplication->id,
                                'applicant_name' => $leaveApplication->name,
                                'leave_type' => $leaveApplication->leave_type
                            ],
                            'icon' => 'fas fa-calendar-plus text-info',
                            'url' => '/leave-validation?leave_id=' . $leaveApplication->id,
                            'expires_at' => now()->addDays(30)
                        ]);
                    }
                    break;

                case 'leave_approved':
                case 'leave_rejected':
                case 'leave_processed':
                    // Notify the applicant about status change
                    if ($leaveApplication->user_id) {
                        $statusMessages = [
                            'leave_approved' => "Pengajuan cuti Anda ({$leaveApplication->leave_type}) telah disetujui",
                            'leave_rejected' => "Pengajuan cuti Anda ({$leaveApplication->leave_type}) telah ditolak",
                            'leave_processed' => "Pengajuan cuti Anda ({$leaveApplication->leave_type}) sedang diproses"
                        ];

                        $icons = [
                            'leave_approved' => 'fas fa-check-circle text-success',
                            'leave_rejected' => 'fas fa-times-circle text-danger',
                            'leave_processed' => 'fas fa-clock text-warning'
                        ];

                        $titles = [
                            'leave_approved' => 'Cuti Disetujui',
                            'leave_rejected' => 'Cuti Ditolak',
                            'leave_processed' => 'Cuti Diproses'
                        ];

                        $notificationClass::create([
                            'user_id' => $leaveApplication->user_id,
                            'type' => $type,
                            'title' => $titles[$type],
                            'message' => $statusMessages[$type],
                            'data' => [
                                'leave_id' => $leaveApplication->id,
                                'leave_type' => $leaveApplication->leave_type,
                                'status' => $leaveApplication->status
                            ],
                            'icon' => $icons[$type],
                            'url' => '/leave-application?leave_id=' . $leaveApplication->id,
                            'expires_at' => now()->addDays(7)
                        ]);
                    }
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Error creating leave notification: ' . $e->getMessage());
        }
    }

    /**
     * Clean up leave notifications
     */
    private function cleanupLeaveNotifications($leaveApplication)
    {
        try {
            // Check if Notification model exists
            if (!class_exists('\App\Models\Notification')) {
                return; // Skip if notification system not implemented
            }

            $notificationClass = '\App\Models\Notification';
            
            // Remove related notifications when leave application is deleted
            $notificationClass::where('type', 'LIKE', '%leave%')
                ->where('data->leave_id', $leaveApplication->id)
                ->delete();
                
        } catch (\Exception $e) {
            Log::error('Error cleaning up leave notifications: ' . $e->getMessage());
        }
    }
}