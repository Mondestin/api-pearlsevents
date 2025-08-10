<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The booking instance.
     *
     * @var Booking
     */
    public Booking $booking;
    public string $frontendUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        // Keep the booking with necessary relations for the email
        $this->booking = $booking;
        $this->frontendUrl = env('FRONTEND_URL','https://admin-pearlsevents.vercel.app');
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        // Set a clear French subject and use a dedicated view for the email content
        // Build a friendly booking URL that the QR code points to (opens booking online when scanned)
        // Prefer a configurable frontend URL, fall back to app.url
        $baseUrl = rtrim((string) $this->frontendUrl, '/');
        $bookingUrl = $baseUrl . '/booking/' . $this->booking->id; // Adjust path to your frontend route

        // Build a lightweight QR code using a public QR service URL to avoid heavy dependencies
        // NOTE: If you prefer generating QR codes locally, we can swap this to a library like "simplesoftwareio/simple-qrcode"
        // and embed the image as an attachment. For now, a URL-based QR is sufficient for confirmation emails.
        // Public QR code generation URL (PNG 240x240). The data is URL-encoded (points directly to the booking URL).
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' . urlencode($bookingUrl);

        return $this->subject('Pearl Events - Confirmation de réservation')
            // Use Markdown template for consistent header/footer and button styling
            ->markdown('emails.booking_created_markdown')
            ->with([
                'booking' => $this->booking,
                'user' => $this->booking->user,
                'event' => $this->booking->event,
                'ticket' => $this->booking->ticket,
                // Provide QR code URL to the view
                'qrUrl' => $qrUrl,
                // Provide the booking URL for CTA buttons and deep links
                'bookingUrl' => $bookingUrl,
            ]);
    }
}

