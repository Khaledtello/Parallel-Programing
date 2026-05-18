<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\EventSeatStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\EventSeat;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        $availableEventSeats = EventSeat::where(
            'status',
            EventSeatStatus::Available
        )->get();

        foreach ($availableEventSeats as $eventSeat) {
            $user = $users->random();
            $paymentSucceeded = rand(1, 100) <= 85;

            if ($paymentSucceeded) {
                Booking::create([
                    'user_id' => $user->id,
                    'event_seat_id' => $eventSeat->id,
                    'status' => BookingStatus::Confirmed,
                    'payment_status' => PaymentStatus::Paid,
                    'booked_at' => now()->subDays(rand(0, 30)),
                    'created_at' => now()->subDays(rand(0, 30)),
                    'updated_at' => now(),
                ]);

                $eventSeat->update([
                    'status' => EventSeatStatus::Booked,
                ]);
            } else {
                Booking::create([
                    'user_id' => $user->id,
                    'event_seat_id' => $eventSeat->id,
                    'status' => BookingStatus::Cancelled,
                    'payment_status' => PaymentStatus::Failed,
                    'booked_at' => null,
                    'created_at' => now()->subDays(rand(0, 30)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
