<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
class OTPEmail extends Mailable
{
    public function build()
    {
        //return $this->view('emails.emailotp')
            ->subject('My Subject');

        return $this->from('noreply.sanghathit@gmail.com')
                ->subject('OTP VERIFICATION CODE')
                ->view('emails.emailotp');
    }
}
