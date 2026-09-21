<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
class ForgotPassword extends Mailable
{
    public $pwd;

    public function __construct($pwd)
    {
        $this->pwd = $pwd;
    }

    public function build()
    {
        //return $this->view('emails.emailotp')->subject('My Subject');

        return $this->from('no-reply@vaarnikaenterprises.com')
					->subject('PASSWORD RESET SUCCESSFULLY')
					->view('emails.forgot');
    }
}
