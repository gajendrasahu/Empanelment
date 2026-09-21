<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeptEoiExtensionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $eoi;

    public function __construct($eoi)
    {
        $this->eoi 			=	$eoi;
    }

    public function build()
    {
        return $this->subject('Extension of EOI Submission Deadline – ['.$this->eoi->eoinumber.']')
                    ->view('emails.depteoiextension')
					->with([
						'eoi'			=>	$this->eoi,
					]);
    }	
}


