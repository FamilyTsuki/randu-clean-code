<?php

declare(strict_types=1);

interface BookingConfirmationListener
{
    public function onBookingConfirmed(Booking $booking, float $total): void;
}
