<?php

namespace App\Enums;

enum EventSeatStatus: string
{
    case Available = 'available';

    case Reserved = 'reserved';

    case Booked = 'booked';

    case Blocked = 'blocked';
}