<?php

namespace App\Jobs;

use App\Enums\BookingStatus;
use App\Enums\EventSeatStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\EventSeat;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPaymentJob implements ShouldQueue
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
        $booking = Booking::lockForUpdate()->find($this->booking->id);

        Log::info('Payment processing started', [
            'booking_id' => $booking->id,
        ]);

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => BookingStatus::ProcessingPayment]);

            /*
            |--------------------------------------------------------------------------
            | Lock User Balance
            |--------------------------------------------------------------------------
            */

            $user = User::query()
                ->whereKey($booking->user->id)
                ->lockForUpdate()
                ->first();

            sleep(2);

            /*
            |--------------------------------------------------------------------------
            | Check Balance
            |--------------------------------------------------------------------------
            */

            $eventSeat = EventSeat::lockForUpdate()->find($booking->event_seat_id);
            $price = $eventSeat->event->price;

            if ($user->balance < $price) {

                $booking->update([
                    'status' => BookingStatus::Cancelled,
                    'payment_status' => PaymentStatus::Failed,
                ]);

                $eventSeat->update(['status' => EventSeatStatus::Available]);

                Log::warning('Insufficient balance', [
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                ]);

                SendPaymentFailedNotificationJob::dispatch($booking);

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Simulate Random Payment Failure
            |--------------------------------------------------------------------------
            */

            $paymentSucceeded = rand(1, 100) <= 90;

            if (!$paymentSucceeded) {

                $booking->update([
                    'status' => BookingStatus::Cancelled,
                    'payment_status' => PaymentStatus::Failed,
                ]);

                $eventSeat->update(['status' => EventSeatStatus::Available]);

                Log::error('Payment gateway failure', [
                    'booking_id' => $booking->id,
                ]);

                SendPaymentFailedNotificationJob::dispatch($booking);

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Deduct Balance
            |--------------------------------------------------------------------------
            */

            $user->decrement('balance', $price);

            Log::info('Balance deducted', [
                'user_id' => $user->id,
                'amount' => $price,
                'remaining_balance' => $user->fresh()->balance,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Confirm Booking
            |--------------------------------------------------------------------------
            */

            $booking->update([
                'status' => BookingStatus::Confirmed,
                'payment_status' => PaymentStatus::Paid,
                'booked_at' => now(),
            ]);

            $eventSeat->update(['status' => EventSeatStatus::Booked]);

            Log::info('Booking confirmed', [
                'booking_id' => $booking->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Send Notification
            |--------------------------------------------------------------------------
            */

            SendBookingConfirmedNotificationJob::dispatch($booking);
        });
    }
}
