<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactUsMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $email;
    public string $phone;
    public string $eventType;
    public ?string $eventDate;
    public ?string $budget;
    public string $message;

    public function __construct(
        string $name, 
        string $email, 
        string $phone, 
        string $eventType, 
        ?string $eventDate = null, 
        ?string $budget = null, 
        string $message
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->eventType = $eventType;
        $this->eventDate = $eventDate;
        $this->budget = $budget;
        $this->message = $message;
    }

    public function build(): self
    {
        return $this->subject('Nouveau message de contact - ' . $this->eventType)
            ->replyTo($this->email, $this->name)
            ->markdown('emails.contact_us')
            ->with([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'eventType' => $this->eventType,
                'eventDate' => $this->eventDate,
                'budget' => $this->budget,
                'message' => $this->message,
            ]);
    }
}

