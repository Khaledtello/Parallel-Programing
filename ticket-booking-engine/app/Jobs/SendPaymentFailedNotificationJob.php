<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendPaymentFailedNotificationJob implements ShouldQueue
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

        Log::warning('Sending payment failed notification', [
            'booking_id' => $booking->id,
        ]);

        sleep(1);

        Log::info('Payment failed notification sent', [
            'booking_id' => $booking->id,
        ]);
    }
}
