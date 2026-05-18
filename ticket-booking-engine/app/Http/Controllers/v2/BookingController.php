<?php

namespace App\Http\Controllers\v2;

use App\Enums\BookingStatus;
use App\Enums\EventSeatStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventSeat;
use App\Models\Seat;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    use ApiResponse;

    public function store(StoreBookingRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            /*
            |--------------------------------------------------------------------------
            | Step 0: Added A Lock To Fix The Race Condition Issue
            |--------------------------------------------------------------------------
            */

            $user = User::lockForUpdate()->find($request->user_id);
            $eventSeat = EventSeat::lockForUpdate()
                ->where('event_id', $request->event_id)
                ->firstWhere('seat_id', $request->seat_id);

            Log::info('Booking request received', [
                'user_id' => $user->id,
                'event_seat_id' => $eventSeat->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Step 1: Check Seat Availability
            |--------------------------------------------------------------------------
            */

            if ($eventSeat->status !== EventSeatStatus::Available) {
                Log::warning('Seat is not available');

                return $this->errorResponse(
                    message: 'Seat is not available',
                    code: 409
                );
            }

            Log::info('Seat is available');

            sleep(1);

            /*
            |--------------------------------------------------------------------------
            | Step 2: Create Pending Booking
            |--------------------------------------------------------------------------
            */

            $booking = Booking::create([
                'user_id' => $user->id,
                'event_seat_id' => $eventSeat->id,
                'status' => BookingStatus::Pending,
                'payment_status' => PaymentStatus::Pending,
            ]);

            $eventSeat->update(['status' => EventSeatStatus::Reserved]);

            Log::info('Pending booking created', [
                'booking_id' => $booking->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Step 3: Simulate Payment Processing
            |--------------------------------------------------------------------------
            */

            $booking->update(['status' => BookingStatus::ProcessingPayment]);

            sleep(2);

            /*
            |--------------------------------------------------------------------------
            | Step 4: Check Balance
            |--------------------------------------------------------------------------
            */

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

                return $this->errorResponse(
                    message: 'Insufficient balance'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Step 5: Simulate Random Payment Failure
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

                return $this->errorResponse(message: 'Payment failed');
            }

            /*
            |--------------------------------------------------------------------------
            | Step 6: Deduct Balance
            |--------------------------------------------------------------------------
            */

            $user->decrement(
                'balance',
                $price
            );

            Log::info('Balance deducted', [
                'user_id' => $user->id,
                'amount' => $price,
                'remaining_balance' => $user->fresh()->balance,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Step 7: Confirm Booking
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

            return $this->dataResponse(
                data: ['booking_id' => $booking->id],
                message: 'Booking completed successfully',
                code: 201,
            );
        });
    }
}
