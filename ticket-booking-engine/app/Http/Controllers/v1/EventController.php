<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventSeat;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->dataResponse(Event::all());
    }

    public function show(Event $event): JsonResponse
    {
        return $this->dataResponse($event);
    }

    public function popularEvents(): JsonResponse
    {
        $events = Event::query()
            ->select('events.*')
            ->join('event_seat', 'events.id', '=', 'event_seat.event_id')
            ->join('bookings', 'event_seat.id', '=', 'bookings.event_seat_id')
            ->groupBy('events.id')
            ->orderByRaw('COUNT(bookings.id) DESC')
            ->limit(10)
            ->get();

        return $this->dataResponse($events);
    }

    public function availableSeats(Event $event): JsonResponse
    {
        $seats = EventSeat::where('event_id', $event->id)
            ->where('status', 'available')
            ->get();

        return $this->dataResponse($seats);
    }
}
