<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WorkOrderExpiryEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $remainingDays;
    
    public function __construct($order, $remainingDays)
    {
        $this->order = $order;
        $this->remainingDays = $remainingDays;
    }

    public function build()
    {
		return	$this->subject('Reminder Regarding Work Order Expiry – '.$this->order->ordernumber)
				->view('emails.workorderexpiryemail')
				->with([
					'data'			=>	$this->order,
					'remainingDays'	=> 	$this->remainingDays,
				]);
    }
}