<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class AdminBookingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public string $adminName;

    public function __construct(Booking $booking, string $adminName = 'Admin')
    {
        $this->booking = $booking;
        $this->adminName = $adminName;
    }

    public function build(): self
    {
        return $this->subject('Pearl\'s Event New Online Booking - ' . $this->booking->event->name)
            ->markdown('emails.admin_booking_notification')
            ->with([
                'booking' => $this->booking,
                'adminName' => $this->adminName,
            ]);
    }
} 