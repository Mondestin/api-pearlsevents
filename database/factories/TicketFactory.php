<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ticketTypes = [
            'VIP', 'Premium', 'Standard', 'Early Bird', 'Student',
            'Senior', 'Child', 'Family', 'Group', 'Corporate',
            'General Admission', 'Backstage Pass', 'Meet & Greet'
        ];

        return [
            'event_id' => Event::factory(),
            'type' => fake()->randomElement($ticketTypes),
            'price' => fake()->randomFloat(2, 10, 500),
            'quantity' => fake()->numberBetween(10, 200),
        ];
    }

    /**
     * Create a VIP ticket.
     */
    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'VIP',
            'price' => fake()->randomFloat(2, 100, 500),
            'quantity' => fake()->numberBetween(5, 50),
        ]);
    }

    /**
     * Create a standard ticket.
     */
    public function standard(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'Standard',
            'price' => fake()->randomFloat(2, 20, 100),
            'quantity' => fake()->numberBetween(50, 200),
        ]);
    }

    /**
     * Create a free ticket.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => 0.00,
            'quantity' => fake()->numberBetween(20, 100),
        ]);
    }

    /**
     * Create a premium ticket.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'Premium',
            'price' => fake()->randomFloat(2, 75, 300),
            'quantity' => fake()->numberBetween(10, 75),
        ]);
    }
} 