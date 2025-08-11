<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $password;
    
    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function build(): self
    {
        return $this->subject('Pearl\'s Events - Bienvenue ' . $this->user->name . ' !')
            ->markdown('emails.user_registration')
            ->with([
                'user' => $this->user,
                'password' => $this->password
            ]);
    }
} 