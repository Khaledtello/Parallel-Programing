<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'event_seat_id',
        'status',
        'payment_status',
        'booked_at',
    ];

    protected $casts = [
        'status' => BookingStatus::class,
        'payment_status' => PaymentStatus::class,
        'booked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function eventSeat()
    {
        return $this->belongsTo(EventSeat::class);
    }
}
