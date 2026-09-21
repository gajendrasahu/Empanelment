<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorUserPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pass_word;
	public $email;
	public $name;

    public function __construct($pass_word,$email,$name)
    {
        $this->pass_word=	$pass_word;
		$this->email	=	$email;
		$this->name		=	$name;
    }

    public function build()
    {
        return $this->subject('Welcome to Portal')
                    ->view('emails.vendoruserpassword')
					->with([
						'pass_word'	=>	$this->pass_word,
						'email'		=>	$this->email,
						'name'		=>	$this->name,
					]);
    }	
}

