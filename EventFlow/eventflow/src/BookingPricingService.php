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
            if ($total < 100.0) {
                $total *= 0.95;
            } else if ($total < 299.0) {
                $total *= 0.90;
            }
            else {
                $total *= 0.85;
            }
        }

        if ($booking->passType === '3days') {
            if ($total >= 20) {
                $total -= 20.0;
            }
        }
        if ($total < 0.0) {
            throw new RuntimeException('Total cannot be negative');
        }
        return $total;
    }
}