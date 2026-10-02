<?php

declare(strict_types=1);

final class BookingPricingService
{
    public function calculate(Booking $booking): float
    {
        $total = 0.0;

        foreach ($booking->items as $item) {
            if ($item->quantity <= 0) {
                throw new RuntimeException('Invalid quantity');
            }

            $total += $item->ticket->price * $item->quantity;
        }

        if ($booking->customer->type === 'vip') {
            $total *= 0.90;
        }

        if ($booking->passType === '3days') {
            $total -= 10.0;
        }

        return $total;
    }
}