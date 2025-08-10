<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventTypes = [
            'Tech Conference', 'Music Festival', 'Business Summit', 'Art Exhibition',
            'Sports Tournament', 'Food Festival', 'Workshop', 'Seminar',
            'Concert', 'Theater Performance', 'Comedy Show', 'Dance Performance',
            'Gaming Tournament', 'Fashion Show', 'Book Launch', 'Film Screening'
        ];

        $locations = [
            'Convention Center', 'Stadium', 'Auditorium', 'Conference Hall',
            'Exhibition Center', 'Theater', 'Park', 'Museum',
            'Hotel Ballroom', 'University Campus', 'Shopping Mall', 'Community Center'
        ];

        $samplePictures = [
            'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1515187029135-18ee286d815b?w=800&h=600&fit=crop',
        ];

        return [
            'user_id' => User::factory()->admin(),
            'name' => fake()->randomElement($eventTypes),
            'description' => fake()->paragraph(3),
            'location' => fake()->randomElement($locations),
            'date' => fake()->dateTimeBetween('now', '+6 months'),
            'pictures' => fake()->optional(0.7)->randomElements($samplePictures, fake()->numberBetween(1, 3)),
        ];
    }

    /**
     * Create an upcoming event.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween('now', '+3 months'),
        ]);
    }

    /**
     * Create a past event.
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Create an event happening soon.
     */
    public function soon(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween('now', '+1 week'),
        ]);
    }

    /**
     * Create an event with pictures.
     */
    public function withPictures(): static
    {
        $samplePictures = [
            'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1515187029135-18ee286d815b?w=800&h=600&fit=crop',
        ];

        return $this->state(fn (array $attributes) => [
            'pictures' => fake()->randomElements($samplePictures, fake()->numberBetween(1, 3)),
        ]);
    }
} 