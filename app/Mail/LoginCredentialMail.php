<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginCredentialMail extends Mailable
{
    use Queueable, SerializesModels;

	public $name;
    public $email;
	public $password;
	public $weblink;

    public function __construct($name,$email,$password,$weblink)
    {
        $this->name 	=	$name;
		$this->email 	=	$email;
		$this->password	=	$password;
		$this->weblink	=	$weblink;
    }

    public function build()
    {
        return $this->subject('Your Account Has Been Created – Login Details')
                    ->view('emails.credential')
					->with([
						'name'		=>	$this->name,
						'email'		=>	$this->email,
						'password'	=>	$this->password,
						'weblink'	=>	$this->weblink,
					]);
    }	
}

