<?php

namespace App\Models;

use App\Models\Cinema\Settings\Option;
use App\Models\Entity\Cinema;
use App\Models\Movie\MovieVersion;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $fillable = [
        'movieVersionId',
        'movieId',
        'roomId',
        'cinemaId',
        'startTime',
        'endTime',
        'status',
    ];

    protected $casts = [
        'startTime' => 'datetime',
        'endTime' => 'datetime',
    ];

    public function movieVersion()
    {
        return $this->belongsTo(MovieVersion::class, 'movieVersionId');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'roomId');
    }
    
    public function cinema()
    {
        return $this->belongsTo(Cinema::class, 'cinemaId');
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movieId');
    }

    /**
     * Get all bookings for this session
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the number of tickets sold for this session
     */
    public function getTicketsSoldAttribute(): int
    {
        return $this->bookings()->paid()->sum('total_tickets');
    }

    /**
     * Get the number of available seats for this session
     */
    public function getAvailableSeatsAttribute(): int
    {
        $roomCapacity = $this->room->capacity ?? 0;
        return $roomCapacity - $this->tickets_sold;
    }

    /**
     * Check if session is sold out
     */
    public function isSoldOut(): bool
    {
        return $this->available_seats <= 0;
    }
}
