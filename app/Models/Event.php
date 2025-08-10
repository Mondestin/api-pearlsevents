<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'location',
        'date',
        'pictures',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'datetime',
        'pictures' => 'array',
    ];

    /**
     * Get the user that created the event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tickets for the event.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get the bookings for the event.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Scope a query to only include upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>', now());
    }

    /**
     * Scope a query to only include past events.
     */
    public function scopePast($query)
    {
        return $query->where('date', '<', now());
    }

    /**
     * Get the total number of tickets sold for this event.
     */
    public function getTotalTicketsSoldAttribute(): int
    {
        return $this->bookings()->sum('quantity');
    }

    /**
     * Get the total revenue for this event.
     */
    public function getTotalRevenueAttribute(): float
    {
        return $this->bookings()
            ->join('tickets', 'bookings.ticket_id', '=', 'tickets.id')
            ->selectRaw('SUM(bookings.quantity * tickets.price) as total')
            ->value('total') ?? 0;
    }

    /**
     * Add a picture to the event.
     */
    public function addPicture(string $pictureUrl): void
    {
        $pictures = $this->pictures ?? [];
        $pictures[] = $pictureUrl;
        $this->update(['pictures' => $pictures]);
    }

    /**
     * Remove a picture from the event.
     */
    public function removePicture(string $pictureUrl): void
    {
        $pictures = $this->pictures ?? [];
        $pictures = array_filter($pictures, fn($url) => $url !== $pictureUrl);
        $this->update(['pictures' => array_values($pictures)]);
    }

    /**
     * Get the main picture (first picture) for the event.
     */
    public function getMainPictureAttribute(): ?string
    {
        $pictures = $this->pictures ?? [];
        return $pictures[0] ?? null;
    }

    /**
     * Check if the event has pictures.
     */
    public function hasPictures(): bool
    {
        return !empty($this->pictures);
    }

    /**
     * Get the number of pictures for this event.
     */
    public function getPicturesCountAttribute(): int
    {
        return count($this->pictures ?? []);
    }
} 