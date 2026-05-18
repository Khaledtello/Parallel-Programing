<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailySalesReport extends Model
{
    protected $fillable = [
        'report_date',
        'total_bookings',
        'total_revenue',
        'successful_payments',
        'failed_payments',
    ];

    protected $casts = [
        'booked_at' => 'datetime',
    ];
}
