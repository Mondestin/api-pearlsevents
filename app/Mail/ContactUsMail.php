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
    public string $subjectLine;
    public string $messageBody;

    public function __construct(string $name, string $email, string $subjectLine, string $messageBody)
    {
        $this->name = $name;
        $this->email = $email;
        $this->subjectLine = $subjectLine;
        $this->messageBody = $messageBody;
    }

    public function build(): self
    {
        return $this->subject('[Contact] ' . $this->subjectLine)
            ->replyTo($this->email, $this->name)
            ->markdown('emails.contact_us')
            ->with([
                'name' => $this->name,
                'email' => $this->email,
                'subjectLine' => $this->subjectLine,
                'messageBody' => $this->messageBody,
            ]);
    }
}

