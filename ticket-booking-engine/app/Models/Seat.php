<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function eventSeats()
    {
        return $this->hasMany(EventSeat::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
