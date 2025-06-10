<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\LeaveApplication;

class NewLeaveApplicationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $leaveApplication;

    /**
     * Create a new message instance.
     */
    public function __construct(LeaveApplication $leaveApplication)
    {
        $this->leaveApplication = $leaveApplication;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Pengajuan Cuti Baru - ' . $this->leaveApplication->name)
                    ->view('emails.new-leave-application')
                    ->with([
                        'leaveApplication' => $this->leaveApplication,
                        'approvalUrl' => route('leave-validation.index') // URL untuk halaman approval
                    ]);
    }
}