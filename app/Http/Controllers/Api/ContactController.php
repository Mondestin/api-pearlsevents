<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactUsMail;

class ContactController extends Controller
{
    /**
     * Send a contact message to the admin mailbox.
     */
    public function sendMailtoAdmin(Request $request): JsonResponse
    {
        // Validate inputs from the public contact form
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        // Resolve admin email from config or env, fallback to MAIL_FROM_ADDRESS
        $adminEmail = 'sydneymondestin@gmail.com';
        // $adminEmail = config('mail.contact_to')
        //     ?? env('MAIL_CONTACT_TO')
        //     ?? config('mail.from.address');

        try {
            Mail::to($adminEmail)->send(new ContactUsMail(
                $validated['name'],
                $validated['email'],
                $validated['subject'],
                $validated['message']
            ));

            Log::info('Contact message sent to admin', [
                'from_email' => $validated['email'],
                'subject' => $validated['subject'],
                'admin_email' => $adminEmail,
            ]);

            return response()->json([
                'message' => 'Votre message a été envoyé. Merci de nous avoir contactés.'
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Failed to send contact message to admin', [
                'from_email' => $validated['email'],
                'subject' => $validated['subject'],
                'admin_email' => $adminEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => "Une erreur s'est produite lors de l'envoi du message. Veuillez réessayer plus tard."
            ], 500);
        }
    }
}

