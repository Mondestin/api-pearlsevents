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
    public string $welcomeMessage;

    public function __construct(User $user, string $welcomeMessage = '')
    {
        $this->user = $user;
        $this->welcomeMessage = $welcomeMessage ?: 'Bienvenue dans la communauté Pearl\'s Events !';
    }

    public function build(): self
    {
        return $this->subject('Pearl\'s Events - Bienvenue ' . $this->user->name . ' !')
            ->markdown('emails.user_registration')
            ->with([
                'user' => $this->user,
                'welcomeMessage' => $this->welcomeMessage,
            ]);
    }
} 