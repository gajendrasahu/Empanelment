<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PublishPreBidMail extends Mailable
{
    use Queueable, SerializesModels;

    public $eoi;
	public $usrType;

    public function __construct($eoi,$usrType)
    {
        $this->eoi 		=	$eoi;
		$this->usrType 	=	$usrType;
    }

    public function build()
    {
        return $this->subject('Intimation of Publication of Pre-Bid Queries to Vendors')
                    ->view('emails.publishprebid')
					->with([
						'eoi'		=>	$this->eoi,
						'usrType'	=>	$this->usrType,
					]);
    }	
}


