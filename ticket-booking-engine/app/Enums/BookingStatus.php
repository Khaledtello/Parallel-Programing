<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';

    case ProcessingPayment = 'processing_payment';

    case Confirmed = 'confirmed';

    case Cancelled = 'cancelled';
}