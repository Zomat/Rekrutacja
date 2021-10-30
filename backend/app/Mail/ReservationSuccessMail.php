<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservationInfo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->reservationInfo = $data;

        //format date
        $this->reservationInfo['date'] = date('d-m-Y H:i', strtotime($data['date']));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('hello@mailtrap.io')
		        ->markdown('emails.reservations.success')
                ->with('info', $this->reservationInfo);
    }
}
