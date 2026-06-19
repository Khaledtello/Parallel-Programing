<?php

namespace App\Http\Controllers\v3;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSeat;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->dataResponse(Event::all());
    }

    public function show(int $id): JsonResponse
    {
        $event = Cache::remember(
            "event_{$id}",
            now()->addMinutes(10),
            function () use ($id) {
                return Event::find($id);
            }
        );

        return $this->dataResponse($event);
    }

    public function popularEvents(): JsonResponse
    {
        $events = Cache::remember(
            'popular_events',
            now()->addMinutes(10),
            function () {
                return Event::query()
                    ->select('events.*')
                    ->join('event_seat', 'events.id', '=', 'event_seat.event_id')
                    ->join('bookings', 'event_seat.id', '=', 'bookings.event_seat_id')
                    ->groupBy('events.id')
                    ->orderByRaw('COUNT(bookings.id) DESC')
                    ->limit(10)
                    ->get();
            }
        );

        return $this->dataResponse($events);
    }

    public function availableSeats(int $id): JsonResponse
    {
        $seats = Cache::remember(
            "available_seats_{$id}",
            now()->addMinutes(5),
            function () use ($id) {
                return EventSeat::where('event_id', $id)
                    ->where('status', 'available')
                    ->get();
            }
        );

        return $this->dataResponse($seats);
    }
}
