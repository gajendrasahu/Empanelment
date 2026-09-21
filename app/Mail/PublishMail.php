<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PublishMail extends Mailable
{
    use Queueable, SerializesModels;

    public $eoi;

    public function __construct($eoi)
    {
        $this->eoi 		=	$eoi;
    }

    public function build()
    {
        return $this->subject('EoI - '.$this->eoi->projecttitle)
                    ->view('emails.publish')
					->with([
						'eoi'	=>	$this->eoi,
					]);
    }	
}

