<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovalOTPMail extends Mailable
{
    use Queueable, SerializesModels;

	public $otp;
    public function __construct($otp)
    {
        $this->otp 	=	$otp;
    }

    public function build()
    {
        return $this->subject('One-Time Password (OTP) for EoI Publication Approval')
                    ->view('emails.approvalotpmail')
					->with([
						'otp'		=>	$this->otp,
					]);
    }	
}

