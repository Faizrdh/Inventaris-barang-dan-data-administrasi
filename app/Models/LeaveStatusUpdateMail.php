<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\LeaveApplication;

class LeaveStatusUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $leaveApplication;
    public $oldStatus;

    /**
     * Create a new message instance.
     */
    public function __construct(LeaveApplication $leaveApplication, string $oldStatus)
    {
        $this->leaveApplication = $leaveApplication;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $statusText = $this->getStatusText($this->leaveApplication->status);
        
        return $this->subject('Update Status Pengajuan Cuti - ' . $statusText)
                    ->view('emails.leave-status-update')
                    ->with([
                        'leaveApplication' => $this->leaveApplication,
                        'oldStatus' => $this->oldStatus,
                        'statusText' => $statusText,
                        'dashboardUrl' => route('leave-application.index') // URL dashboard pegawai
                    ]);
    }

    private function getStatusText($status): string
    {
        return match($status) {
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'processed' => 'Diproses',
            'pending' => 'Menunggu',
            default => 'Status Tidak Dikenal'
        };
    }
}