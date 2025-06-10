<?php
namespace App\Mail;

use App\Models\LeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewLeaveApplicationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $leaveApplication;
    public $approvalUrl;

    public function __construct(LeaveApplication $leaveApplication)
    {
        $this->leaveApplication = $leaveApplication;
        $this->approvalUrl = route('leave-validation.index'); // atau URL yang sesuai
    }

    public function build()
    {
        return $this->subject('Pengajuan Cuti Baru - ' . $this->leaveApplication->code)
                    ->view('emails.new-leave-application')
                    ->with([
                        'leaveApplication' => $this->leaveApplication,
                        'approvalUrl' => $this->approvalUrl
                    ]);
    }
}