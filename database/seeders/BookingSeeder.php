<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get client users
        $clientUsers = User::where('role', 'client')->get();

        if ($clientUsers->isEmpty()) {
            // Create client users if none exist
            $this->call(UserSeeder::class);
            $clientUsers = User::where('role', 'client')->get();
        }

        // Get events and tickets
        $events = Event::all();
        $tickets = Ticket::all();

        if ($events->isEmpty() || $tickets->isEmpty()) {
            // Create events and tickets if none exist
            $this->call(EventSeeder::class);
            $this->call(TicketSeeder::class);
            $events = Event::all();
            $tickets = Ticket::all();
        }

        // Create sample bookings
        foreach ($clientUsers->take(10) as $user) {
            // Each user makes 1-3 bookings
            $numBookings = rand(1, 3);
            
            for ($i = 0; $i < $numBookings; $i++) {
                $ticket = $tickets->random();
                $quantity = rand(1, 3);

                // Check if enough tickets are available
                if ($ticket->available_tickets >= $quantity) {
                    Booking::create([
                        'user_id' => $user->id,
                        'event_id' => $ticket->event_id,
                        'ticket_id' => $ticket->id,
                        'quantity' => $quantity,
                    ]);
                }
            }
        }

        // Create additional random bookings
        Booking::factory()->count(30)->create();
    }
} 