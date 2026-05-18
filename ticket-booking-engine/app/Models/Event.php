<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

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
