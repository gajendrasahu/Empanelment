<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterviewLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $eoi;
	public $vendor;
	public $participation;

    public function __construct($eoi,$vendor,$participation)
    {
        $this->eoi 			=	$eoi;
		$this->vendor 		=	$vendor;
		$this->participation=	$participation;
    }

    public function build()
    {
        return $this->subject('EoI Interview Schedule and Meeting Link – ['.$this->eoi->eoinumber.']')
                    ->view('emails.interviewlink')
					->with([
						'eoi'			=>	$this->eoi,
						'vendor'		=>	$this->vendor,
						'participation'	=>	$this->participation,
					]);
    }	
}


