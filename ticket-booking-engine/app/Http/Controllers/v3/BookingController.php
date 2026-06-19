<?php

namespace App\Http\Controllers\v3;

use App\Enums\BookingStatus;
use App\Enums\EventSeatStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Jobs\ProcessPaymentJob;
use App\Models\Booking;
use App\Models\EventSeat;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

            $user = User::find($request->user_id);
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

            Cache::forget("available_seats_{$eventSeat->event_id}");

            Log::info('Pending booking created', [
                'booking_id' => $booking->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Step 3: Dispatch Async Payment Job
            |--------------------------------------------------------------------------
            */

            ProcessPaymentJob::dispatch($booking);

            Log::info('Payment job dispatched', [
                'booking_id' => $booking->id,
            ]);

            return $this->dataResponse(
                data: ['booking_id' => $booking->id],
                message: 'Booking request accepted',
                status: 'pending',
                code: 202,
            );
        });
    }
}
