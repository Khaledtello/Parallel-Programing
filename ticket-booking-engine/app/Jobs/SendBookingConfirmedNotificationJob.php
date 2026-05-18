<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendBookingConfirmedNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Booking $booking)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $booking = $this->booking;

        Log::info('Sending confirmation notification', [
            'booking_id' => $booking->id,
        ]);

        sleep(1);

        Log::info('Notification sent successfully', [
            'booking_id' => $booking->id,
        ]);
    }
}
