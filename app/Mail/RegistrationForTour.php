<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationForTour extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new message instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return RegistrationForTour
     */
    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'))
            ->view('mail.registration', [
                "data" => $this->data
            ]);
    }
}
