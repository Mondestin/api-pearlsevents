<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Booking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo admin users
        $adminUsers = [
            [
                'name' => 'John Admin',
                'email' => 'john@pearlevents.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Sarah Manager',
                'email' => 'sarah@pearlevents.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
        ];

        foreach ($adminUsers as $adminData) {
            User::create($adminData);
        }

        // Create demo client users
        $clientUsers = [
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com'],
            ['name' => 'Bob Smith', 'email' => 'bob@example.com'],
            ['name' => 'Carol Davis', 'email' => 'carol@example.com'],
            ['name' => 'David Wilson', 'email' => 'david@example.com'],
            ['name' => 'Emma Brown', 'email' => 'emma@example.com'],
        ];

        foreach ($clientUsers as $clientData) {
            User::create([
                'name' => $clientData['name'],
                'email' => $clientData['email'],
                'password' => Hash::make('password'),
                'role' => 'client',
            ]);
        }

        // Create demo events
        $events = [
            [
                'name' => 'Tech Conference 2024',
                'description' => 'Join us for the biggest tech conference of the year. Learn about the latest trends in AI, machine learning, and web development. Network with industry experts and discover new opportunities.',
                'location' => 'Convention Center',
                'date' => now()->addMonths(2),
                'user_id' => User::where('email', 'john@pearlevents.com')->first()->id,
            ],
            [
                'name' => 'Summer Music Festival',
                'description' => 'A three-day music festival featuring top artists from around the world. Enjoy amazing performances, delicious food, and refreshing drinks in a beautiful outdoor setting.',
                'location' => 'Central Park',
                'date' => now()->addMonths(3),
                'user_id' => User::where('email', 'sarah@pearlevents.com')->first()->id,
            ],
            [
                'name' => 'Business Innovation Summit',
                'description' => 'Connect with industry leaders and learn about innovative business strategies and technologies. Perfect for entrepreneurs and business professionals.',
                'location' => 'Grand Hotel',
                'date' => now()->addMonths(1),
                'user_id' => User::where('email', 'john@pearlevents.com')->first()->id,
            ],
            [
                'name' => 'Art & Design Exhibition',
                'description' => 'Explore contemporary art and design from emerging and established artists. Experience creativity in its purest form.',
                'location' => 'Modern Art Museum',
                'date' => now()->addWeeks(2),
                'user_id' => User::where('email', 'sarah@pearlevents.com')->first()->id,
            ],
            [
                'name' => 'Sports Championship',
                'description' => 'Witness the most exciting sports championship with top athletes competing for glory. A day filled with adrenaline and excitement.',
                'location' => 'Olympic Stadium',
                'date' => now()->addMonths(4),
                'user_id' => User::where('email', 'john@pearlevents.com')->first()->id,
            ],
        ];

        foreach ($events as $eventData) {
            Event::create($eventData);
        }

        // Create tickets for each event
        $events = Event::all();
        foreach ($events as $event) {
            // VIP tickets
            Ticket::create([
                'event_id' => $event->id,
                'type' => 'VIP',
                'price' => rand(150, 400),
                'quantity' => rand(10, 30),
            ]);

            // Premium tickets
            Ticket::create([
                'event_id' => $event->id,
                'type' => 'Premium',
                'price' => rand(80, 200),
                'quantity' => rand(20, 60),
            ]);

            // Standard tickets
            Ticket::create([
                'event_id' => $event->id,
                'type' => 'Standard',
                'price' => rand(30, 100),
                'quantity' => rand(50, 150),
            ]);

            // Early Bird tickets (if event is far enough in the future)
            if ($event->date->isAfter(now()->addWeeks(2))) {
                Ticket::create([
                    'event_id' => $event->id,
                    'type' => 'Early Bird',
                    'price' => rand(20, 60),
                    'quantity' => rand(30, 80),
                ]);
            }

            // Student tickets
            Ticket::create([
                'event_id' => $event->id,
                'type' => 'Student',
                'price' => rand(15, 50),
                'quantity' => rand(20, 60),
            ]);
        }

        // Create demo bookings
        $clientUsers = User::where('role', 'client')->get();
        $tickets = Ticket::all();

        // Alice books tickets for Tech Conference
        $techEvent = Event::where('name', 'Tech Conference 2024')->first();
        $techTickets = $techEvent->tickets;
        
        Booking::create([
            'user_id' => User::where('email', 'alice@example.com')->first()->id,
            'event_id' => $techEvent->id,
            'ticket_id' => $techTickets->where('type', 'VIP')->first()->id,
            'quantity' => 1,
        ]);

        Booking::create([
            'user_id' => User::where('email', 'alice@example.com')->first()->id,
            'event_id' => $techEvent->id,
            'ticket_id' => $techTickets->where('type', 'Standard')->first()->id,
            'quantity' => 2,
        ]);

        // Bob books tickets for Music Festival
        $musicEvent = Event::where('name', 'Summer Music Festival')->first();
        $musicTickets = $musicEvent->tickets;

        Booking::create([
            'user_id' => User::where('email', 'bob@example.com')->first()->id,
            'event_id' => $musicEvent->id,
            'ticket_id' => $musicTickets->where('type', 'Premium')->first()->id,
            'quantity' => 3,
        ]);

        // Carol books tickets for Business Summit
        $businessEvent = Event::where('name', 'Business Innovation Summit')->first();
        $businessTickets = $businessEvent->tickets;

        Booking::create([
            'user_id' => User::where('email', 'carol@example.com')->first()->id,
            'event_id' => $businessEvent->id,
            'ticket_id' => $businessTickets->where('type', 'Standard')->first()->id,
            'quantity' => 1,
        ]);

        // David books tickets for Art Exhibition
        $artEvent = Event::where('name', 'Art & Design Exhibition')->first();
        $artTickets = $artEvent->tickets;

        Booking::create([
            'user_id' => User::where('email', 'david@example.com')->first()->id,
            'event_id' => $artEvent->id,
            'ticket_id' => $artTickets->where('type', 'Student')->first()->id,
            'quantity' => 2,
        ]);

        // Emma books tickets for Sports Championship
        $sportsEvent = Event::where('name', 'Sports Championship')->first();
        $sportsTickets = $sportsEvent->tickets;

        Booking::create([
            'user_id' => User::where('email', 'emma@example.com')->first()->id,
            'event_id' => $sportsEvent->id,
            'ticket_id' => $sportsTickets->where('type', 'VIP')->first()->id,
            'quantity' => 4,
        ]);

        // Create some additional random bookings
        Booking::factory()->count(15)->create();
    }
} 