<?php

namespace App\Models;

use App\Enums\EventSeatStatus;
use Illuminate\Database\Eloquent\Model;

class EventSeat extends Model
{
    protected $table = 'event_seat';

    protected $fillable = [
        'event_id',
        'seat_id',
        'status',
    ];

    protected $casts = [
        'status' => EventSeatStatus::class,
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
