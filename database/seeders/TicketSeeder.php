<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all events
        $events = Event::all();

        if ($events->isEmpty()) {
            // Create events if none exist
            $this->call(EventSeeder::class);
            $events = Event::all();
        }

        foreach ($events as $event) {
            // Create VIP tickets
            Ticket::create([
                'event_id' => $event->id,
                'type' => 'VIP',
                'price' => rand(100, 500),
                'quantity' => rand(10, 50),
            ]);

            // Create Premium tickets
            Ticket::create([
                'event_id' => $event->id,
                'type' => 'Premium',
                'price' => rand(75, 200),
                'quantity' => rand(20, 100),
            ]);

            // Create Standard tickets
            Ticket::create([
                'event_id' => $event->id,
                'type' => 'Standard',
                'price' => rand(25, 100),
                'quantity' => rand(50, 200),
            ]);

            // Create Early Bird tickets (if event is far enough in the future)
            if ($event->date->isAfter(now()->addWeeks(2))) {
                Ticket::create([
                    'event_id' => $event->id,
                    'type' => 'Early Bird',
                    'price' => rand(15, 75),
                    'quantity' => rand(30, 100),
                ]);
            }

            // Create Student tickets
            Ticket::create([
                'event_id' => $event->id,
                'type' => 'Student',
                'price' => rand(10, 50),
                'quantity' => rand(20, 80),
            ]);
        }

        // Create additional random tickets for some events
        $randomEvents = $events->random(min(5, $events->count()));
        foreach ($randomEvents as $event) {
            Ticket::factory()->count(rand(1, 3))->create([
                'event_id' => $event->id,
            ]);
        }
    }
} 