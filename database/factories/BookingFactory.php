<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->client(),
            'event_id' => Event::factory(),
            'ticket_id' => Ticket::factory(),
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }

    /**
     * Create a single ticket booking.
     */
    public function single(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 1,
        ]);
    }

    /**
     * Create a group booking.
     */
    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(3, 10),
        ]);
    }

    /**
     * Create a family booking.
     */
    public function family(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(2, 6),
        ]);
    }
} 