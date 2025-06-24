<?php
namespace App\Mail;

use App\Models\LeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LeaveStatusUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $leaveApplication;
    public $oldStatus;
    public $statusText;
    public $dashboardUrl;

    public function __construct(LeaveApplication $leaveApplication, $oldStatus = null)
    {
        $this->leaveApplication = $leaveApplication;
        $this->oldStatus = $oldStatus;
        
        $statusTexts = [
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak', 
            'processed' => 'Sedang Diproses'
        ];
        
        $this->statusText = $statusTexts[$this->leaveApplication->status] ?? 'Diperbarui';
        $this->dashboardUrl = route('leave-application.index'); // atau URL dashboard yang sesuai
    }

    public function build()
    {
        $subject = 'Status Pengajuan Cuti ' . $this->statusText . ' - ' . $this->leaveApplication->code;

        return $this->subject($subject)
                    ->view('emails.leave-status-update')
                    ->with([
                        'leaveApplication' => $this->leaveApplication,
                        'oldStatus' => $this->oldStatus,
                        'statusText' => $this->statusText,
                        'dashboardUrl' => $this->dashboardUrl
                    ]);
    }
}