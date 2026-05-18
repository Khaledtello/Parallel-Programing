<?php

namespace Database\Seeders;

use App\Enums\EventSeatStatus;
use App\Models\Event;
use App\Models\EventSeat;
use App\Models\Seat;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(500)->create();

        $venues = Venue::factory(5)->create();

        foreach ($venues as $venue) {
            foreach (range('A', 'J') as $row) {
                for ($i = 1; $i <= 100; $i++) {
                    Seat::create([
                        'venue_id'    => $venue->id,
                        'seat_number' => $row . $i
                    ]);
                }
            }

            Event::factory(4)->create(['venue_id' => $venue->id]);
        }

        $events = Event::all();
        foreach ($events as $event) {
            $seats = Seat::where('venue_id', $event->venue_id)->get();
            foreach ($seats as $seat) {
                EventSeat::create([
                    'event_id' => $event->id,
                    'seat_id'  => $seat->id,
                    'status'   => EventSeatStatus::Available,
                ]);
            }
        }

        // $this->call(BookingSeeder::class);
    }
}
