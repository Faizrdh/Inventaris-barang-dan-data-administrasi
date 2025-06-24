<?php

namespace App\Mail;

use App\Models\LeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveApplicationConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $leaveApplication;

    public function __construct(LeaveApplication $leaveApplication)
    {
        $this->leaveApplication = $leaveApplication;
    }

    public function build()
    {
        return $this->subject('Konfirmasi Pengajuan Cuti - ' . $this->leaveApplication->code)
                    ->view('emails.leave-application-confirmation')
                    ->with([
                        'application' => $this->leaveApplication,
                        'dashboardUrl' => route('leave-application.index') // sesuaikan dengan route Anda
                    ]);
    }
}