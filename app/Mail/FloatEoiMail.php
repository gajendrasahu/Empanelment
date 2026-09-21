<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FloatEoiMail extends Mailable
{
    use Queueable, SerializesModels;

    public $eoi;
	public $details;

    public function __construct($eoi,$details)
    {
        $this->eoi 		=	$eoi;
		$this->details 	=	$details;
    }

    public function build()
    {
        return $this->subject('EoI Published')
                    ->view('emails.floateoitemplate')
					->with([
						'data'			=>	$this->eoi,
						'detail'		=>	$this->details,
					]);
    }	
}

