<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; // For sending booking emails
use App\Mail\BookingCreated; // Booking creation email mailable
use App\Mail\AdminBookingNotification; // Admin booking notification mailable
use Illuminate\Validation\ValidationException; // Added for ValidationException
use App\Models\User; // Added for User model
use Illuminate\Support\Str; // Added for Str::random()

class BookingController extends Controller
{
    /**
     * Display a listing of bookings for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        // Check if the bookings table exists
        if (!Schema::hasTable('bookings')) {
            return response()->json([
                'message' => 'Database tables not set up yet. Please run migrations.',
                'data' => []
            ]);
        }

        // If admin, show all bookings, otherwise show user's bookings
        if ($request->user()->isAdmin()) {
            $bookings = Booking::with(['event', 'ticket', 'user'])->get();
        } else {
            $bookings = $request->user()->bookings()
                ->with(['event', 'ticket'])
                ->get();
        }

        return response()->json([
            'data' => $bookings
        ]);
    }

    /**
     * Update the specified booking
     */
    public function update(Request $request): JsonResponse
    {
        try {
            // Find the booking from route parameter
            $booking = Booking::findOrFail($request->booking);

            // Authorization: users can only update their own bookings, admins can update any booking
            if ($booking->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
                return response()->json([
                    'message' => 'Unauthorized to update this booking'
                ], 403);
            }

            // Validate only updatable fields; allow partial updates
            $validated = $request->validate([
                'ticket_id' => 'sometimes|exists:tickets,id',
                'quantity' => 'sometimes|integer|min:1',
            ]);

            // Determine the new values while defaulting to existing ones
            $newTicketId = $validated['ticket_id'] ?? $booking->ticket_id;
            $newQuantity = $validated['quantity'] ?? $booking->quantity;

            // If nothing changes, return current booking
            if ($newTicketId == $booking->ticket_id && (int)$newQuantity === (int)$booking->quantity) {
                return response()->json([
                    'message' => 'No changes applied',
                    'data' => $booking->load(['event', 'ticket', 'user'])
                ]);
            }

            // Load related tickets for availability checks
            $currentTicket = Ticket::findOrFail($booking->ticket_id);
            $newTicket = ($newTicketId == $booking->ticket_id)
                ? $currentTicket
                : Ticket::findOrFail($newTicketId);

            // Availability checks
            if ($newTicketId == $booking->ticket_id) {
                // Same ticket: only need to ensure increases are available
                $delta = (int)$newQuantity - (int)$booking->quantity;
                if ($delta > 0 && $newTicket->available_tickets < $delta) {
                    return response()->json([
                        'message' => 'Not enough tickets available for the requested increase'
                    ], 400);
                }
            } else {
                // Switching ticket type: ensure the new ticket has enough availability for the whole new quantity
                if ($newTicket->available_tickets < (int)$newQuantity) {
                    return response()->json([
                        'message' => 'Not enough tickets available for the selected ticket'
                    ], 400);
                }
            }

            // Perform update within a transaction to keep booking and ticket counts in sync
            DB::beginTransaction();
            try {
                if ($newTicketId == $booking->ticket_id) {
                    // Adjust inventory based on the change in quantity
                    $delta = (int)$newQuantity - (int)$booking->quantity;
                    if ($delta > 0) {
                        // Decrease available stock accordingly (keeping consistency with create logic)
                        $newTicket->decrement('quantity', $delta);
                    } elseif ($delta < 0) {
                        // Increase stock if the user reduces their booking quantity
                        $newTicket->increment('quantity', abs($delta));
                    }

                    // Update booking quantity
                    $booking->update([
                        'quantity' => $newQuantity,
                    ]);
                } else {
                    // Returning previously held quantity to the old ticket
                    $currentTicket->increment('quantity', (int)$booking->quantity);

                    // Deduct the new quantity from the new ticket
                    $newTicket->decrement('quantity', (int)$newQuantity);

                    // Update booking's ticket and quantity
                    $booking->update([
                        'ticket_id' => $newTicketId,
                        'event_id' => $newTicket->event_id, // keep event_id in sync with ticket
                        'quantity' => $newQuantity,
                    ]);
                }

                DB::commit();

                Log::info('Booking updated successfully', [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'old_ticket_id' => $currentTicket->id,
                    'new_ticket_id' => $newTicketId,
                    'old_quantity' => $booking->getOriginal('quantity'),
                    'new_quantity' => $newQuantity,
                    'updated_by_admin' => $request->user()->isAdmin(),
                    'admin_id' => $request->user()->isAdmin() ? $request->user()->id : null,
                    'ip_address' => $request->ip()
                ]);

                return response()->json([
                    'message' => 'Booking updated successfully',
                    'data' => $booking->fresh()->load(['event', 'ticket', 'user'])
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Database error during booking update', [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'ip_address' => $request->ip()
                ]);

                return response()->json([
                    'message' => 'Failed to update booking'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error updating booking', [
                'booking_id' => $request->booking ?? null,
                'user_id' => optional($request->user())->id,
                'user_email' => optional($request->user())->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->only(['ticket_id', 'quantity']),
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'message' => 'Failed to update booking'
            ], 500);
        }
    }

    /**
     * Store a newly created booking
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Only clients can make bookings (unless admin is booking for someone)
            if (!$request->user()->isClient() && !$request->user()->isAdmin()) {
                Log::warning('Unauthorized booking attempt', [
                    'user_id' => $request->user()->id,
                    'user_email' => $request->user()->email,
                    'user_role' => $request->user()->role,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'message' => 'Only clients can make bookings'
                ], 403);
            }

            $request->validate([
                'ticket_id' => 'required|exists:tickets,id',
                'quantity' => 'required|integer|min:1',
                'user_id' => 'sometimes|exists:users,id', // Allow admin to book for specific user
            ]);

            $ticket = Ticket::findOrFail($request->ticket_id);
            $userId = $request->user()->isAdmin() && $request->has('user_id') 
                ? $request->user_id 
                : $request->user()->id;

            // Check if enough tickets are available
            if ($ticket->available_tickets < $request->quantity) {
                Log::warning('Insufficient tickets for booking', [
                    'user_id' => $request->user()->id,
                    'ticket_id' => $ticket->id,
                    'requested_quantity' => $request->quantity,
                    'available_tickets' => $ticket->available_tickets,
                    'ip_address' => $request->ip()
                ]);
                
                return response()->json([
                    'message' => 'Not enough tickets available'
                ], 400);
            }

            // Create booking within a transaction
            DB::beginTransaction();
            try {
                $booking = Booking::create([
                    'user_id' => $userId,
                    'event_id' => $ticket->event_id,
                    'ticket_id' => $ticket->id,
                    'quantity' => $request->quantity,
                ]);

                // Update ticket availability
                $ticket->decrement('quantity', $request->quantity);

                DB::commit();

                Log::info('Booking created successfully', [
                    'booking_id' => $booking->id,
                    'user_id' => $userId,
                    'ticket_id' => $ticket->id,
                    'event_id' => $ticket->event_id,
                    'quantity' => $request->quantity,
                    'booked_by_admin' => $request->user()->isAdmin(),
                    'admin_id' => $request->user()->isAdmin() ? $request->user()->id : null,
                    'ip_address' => $request->ip()
                ]);

                // Send confirmation email to the user after successful booking creation
                // Email errors should not break the booking flow, so wrap in try/catch and log failures
                try {
                    $bookingForEmail = $booking->fresh()->load(['event', 'ticket', 'user']); // Ensure relations are loaded
                   
                   try {
                        Mail::to($bookingForEmail->user->email)
                        ->send(new BookingCreated($bookingForEmail));
                        Log::info('Booking confirmation email sent successfully', [
                            'booking_id' => $booking->id,
                            'user_id' => $userId,
                            'email_to' => $bookingForEmail->user->email
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send booking confirmation email', [
                            'booking_id' => $booking->id,
                            'user_id' => $userId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'email_to' => $bookingForEmail->user->email
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Failed to send booking confirmation email', [
                        'booking_id' => $booking->id,
                        'user_id' => $userId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'email_to' => $bookingForEmail->user->email
                    ]);
                }

                return response()->json([
                    'message' => 'Booking created successfully',
                    'data' => $booking->load(['event', 'ticket', 'user'])
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Database error during booking creation', [
                    'user_id' => $request->user()->id,
                    'ticket_id' => $ticket->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'ip_address' => $request->ip()
                ]);
                
                return response()->json([
                    'message' => 'Failed to create booking'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error creating booking', [
                'user_id' => $request->user()->id,
                'user_email' => $request->user()->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['user_id']),
                'ip_address' => $request->ip()
            ]);
            
            return response()->json([
                'message' => 'Failed to create booking'
            ], 500);
        }
    }

    /**
     * Display the specified booking
     */
    public function show(Request $request): JsonResponse
    {
        $booking = Booking::findOrFail($request->booking);
        return response()->json([
            'data' => $booking->load(['event', 'ticket'])
        ]);
    }

    /**
     * Remove the specified booking
     */
    public function destroy(Request $request): JsonResponse
    {
        $booking = Booking::findOrFail($request->booking);
        // Users can only cancel their own bookings, admins can cancel any booking
        if ($booking->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to cancel this booking'
            ], 403);
        }

        $booking->delete();

        return response()->json([
            'message' => 'Booking cancelled successfully'
        ]);
    }

    /**
     * Get bookings for a specific event (admin only)
     */
    public function eventBookings(Request $request): JsonResponse
    {
        $booking = Booking::find($request->bookingId);
         if (!$booking) {
                return response()->json([
                    'message' => "Booking not found",
                    'id' => $request->bookingId,
                ], 404);
        }

        // Load event and ticket details
        $booking->load(['event', 'ticket']);

        // Return booking details
       return response()->json([
            'data' => $booking
        ], 200);
    }

    /**
     * Get current user's upcoming bookings
     */
    public function upcomingBookings(Request $request): JsonResponse
    {
        $bookings = $request->user()->bookings()
            ->whereHas('event', function ($query) {
                $query->where('date', '>', now());
            })
            ->with(['event', 'ticket'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $bookings
        ]);
    }

    /**
     * Get current user's past bookings
     */
    public function pastBookings(Request $request): JsonResponse
    {
        $bookings = $request->user()->bookings()
            ->whereHas('event', function ($query) {
                $query->where('date', '<', now());
            })
            ->with(['event', 'ticket'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $bookings
        ]);
    }

    /**
     * Get booking statistics for current user
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $statistics = [
            'total_bookings' => $user->bookings()->count(),
            'total_tickets_booked' => $user->bookings()->sum('quantity'),
            'upcoming_bookings' => $user->bookings()
                ->whereHas('event', function ($query) {
                    $query->where('date', '>', now());
                })->count(),
            'past_bookings' => $user->bookings()
                ->whereHas('event', function ($query) {
                    $query->where('date', '<', now());
                })->count(),
            'total_spent' => $user->bookings()
                ->join('tickets', 'bookings.ticket_id', '=', 'tickets.id')
                ->selectRaw('SUM(bookings.quantity * tickets.price) as total')
                ->value('total') ?? 0,
        ];

        return response()->json([
            'data' => $statistics
        ]);
    }


    /**
     * Post a booking online for an event
     * This is a public endpoint to allow users to book an event online 
     */
    public function eventBookkingOnline(Request $request): JsonResponse
    {
        try {
            // Get the request data for online booking
            $eventId = $request->input('event_id');
            $ticketId = $request->input('ticket_id');
            $quantity = $request->input('quantity');
            $userInfo = $request->input('userInfo');

            // Check if the ticket belongs to the specified event
            $ticket = Ticket::where('id', $ticketId)
                ->where('event_id', $eventId)
                ->first();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Invalid ticket for this event'
                ], 400);
            }

            // Check if enough tickets are available
            if ($ticket->available_tickets < $quantity) {
                return response()->json([
                    'message' => 'Not enough tickets available',
                    'available' => $ticket->available_tickets,
                    'requested' => $quantity
                ], 400);
            }

            // Check if the event is still open for booking
            $event = Event::find($eventId);
            if (!$event) {
                return response()->json([
                    'message' => 'Event not found'
                ], 404);
            }

            // Create or find user
            $user = User::firstOrCreate(
                ['email' => $userInfo['email']],
                [
                    'name' => $userInfo['name'],
                    'password' => bcrypt(Str::random(10)), // Generate random password for user
                    'role' => $userInfo['role'] ?? 'client',
                ]
            );

            // Create booking within a transaction
            DB::beginTransaction();
            try {
                $booking = Booking::create([
                    'user_id' => $user->id,
                    'event_id' => $eventId,
                    'ticket_id' => $ticketId,
                    'quantity' => $quantity,
                ]);

                // Update ticket availability
                $ticket->decrement('quantity', $quantity);

                DB::commit();

                // Send confirmation email to the user
                try {
                    $bookingForEmail = $booking->fresh()->load(['event', 'ticket', 'user']);
                    Mail::to($userInfo['email'])
                        ->send(new BookingCreated($bookingForEmail));
                } catch (\Exception $e) {
                    // Email failed but booking was successful
                }

                // Send admin notification email
                try {
                    $adminEmail = env('MAIL_TO_ADMIN', 'admin@pearlsevents.com');
                    Mail::to($adminEmail)->send(new AdminBookingNotification($bookingForEmail));
                    //mail to admin project owner
                    $adminEmail = env('MAIL_TO_PROJECT_OWNER');
                    Mail::to($adminEmail)->send(new AdminBookingNotification($bookingForEmail, 'project_owner'));
                } catch (\Exception $e) {
                    // Admin notification failed but booking was successful
                }

                // Return success response with booking details
                return response()->json([
                    'message' => 'Event booked successfully! Check your email for confirmation.',
                    'data' => [
                        'booking_id' => $booking->id,
                        'booking_reference' => 'BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                        'event' => [
                            'name' => $event->name,
                            'date' => $event->date,
                            'location' => $event->location
                        ],
                        'ticket' => [
                            'type' => $ticket->type,
                            'price' => $ticket->price
                        ],
                        'quantity' => $quantity,
                        'total_price' => $booking->total_price,
                        'user_info' => [
                            'name' => $userInfo['name'],
                            'email' => $userInfo['email'],
                            'phone' => $userInfo['phone'],
                            'role' => $userInfo['role'] ?? 'client'
                        ],
                        'booking_date' => $booking->created_at,
                        'status' => 'confirmed'
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                
                return response()->json([
                    'message' => 'Failed to create booking. Please try again.'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing your booking. Please try again.'
            ], 500);
        }
    }   
} 