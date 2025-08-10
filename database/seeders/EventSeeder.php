<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin users
        $adminUsers = User::where('role', 'admin')->get();

        if ($adminUsers->isEmpty()) {
            // Create admin users if none exist
            $adminUsers = User::factory()->admin()->count(3)->create();
        }

        // Create sample events
        $events = [
            [
                'name' => 'Tech Conference 2024',
                'description' => 'Join us for the biggest tech conference of the year. Learn about the latest trends in AI, machine learning, and web development.',
                'location' => 'Convention Center',
                'date' => now()->addMonths(2),
                'user_id' => $adminUsers->first()->id,
                'pictures' => [
                    'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Summer Music Festival',
                'description' => 'A three-day music festival featuring top artists from around the world. Food, drinks, and amazing performances.',
                'location' => 'Central Park',
                'date' => now()->addMonths(3),
                'user_id' => $adminUsers->first()->id,
                'pictures' => [
                    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Business Innovation Summit',
                'description' => 'Connect with industry leaders and learn about innovative business strategies and technologies.',
                'location' => 'Grand Hotel',
                'date' => now()->addMonths(1),
                'user_id' => $adminUsers->first()->id,
                'pictures' => [
                    'https://images.unsplash.com/photo-1515187029135-18ee286d815b?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Art & Design Exhibition',
                'description' => 'Explore contemporary art and design from emerging and established artists.',
                'location' => 'Modern Art Museum',
                'date' => now()->addWeeks(2),
                'user_id' => $adminUsers->first()->id,
                'pictures' => [
                    'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'Sports Championship',
                'description' => 'Witness the most exciting sports championship with top athletes competing for glory.',
                'location' => 'Olympic Stadium',
                'date' => now()->addMonths(4),
                'user_id' => $adminUsers->first()->id,
                'pictures' => [
                    'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1515187029135-18ee286d815b?w=800&h=600&fit=crop',
                ],
            ],
        ];

        foreach ($events as $eventData) {
            Event::create($eventData);
        }

        // Create additional random events
        Event::factory()->count(15)->create();
    }
} 