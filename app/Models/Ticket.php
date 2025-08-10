<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'type',
        'price',
        'quantity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the event that owns the ticket.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the bookings for this ticket.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the number of tickets sold.
     */
    public function getTicketsSoldAttribute(): int
    {
        return $this->bookings()->sum('quantity');
    }

    /**
     * Get the number of available tickets.
     */
    public function getAvailableTicketsAttribute(): int
    {
        return max(0, $this->quantity - $this->tickets_sold);
    }

    /**
     * Check if tickets are available.
     */
    public function hasAvailableTickets(): bool
    {
        return $this->available_tickets > 0;
    }
} 