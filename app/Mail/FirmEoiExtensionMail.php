<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FirmEoiExtensionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $eoi;
	public $departmentName;

    public function __construct($eoi,$departmentName)
    {
        $this->eoi 				=	$eoi;
		$this->departmentName 	=	$departmentName;
    }

    public function build()
    {
        return $this->subject('EOI Submission Deadline Extended – ['.$this->eoi->eoinumber.']')
                    ->view('emails.firmeoiextension')
					->with([
						'eoi'			=>	$this->eoi,
						'departmentName'=>	$this->departmentName,
					]);
    }	
}


