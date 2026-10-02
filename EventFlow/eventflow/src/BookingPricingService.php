<?php

declare(strict_types=1);

final class BookingPricingService
{
    public function calculate(Booking $booking): float
    {
        $days_reduction = 20.0;
        $vip_reduction = ['0.95', '0.90', '0.85'];
        $vip_tiers = [100.0, 299.0];

        $total = 0.0;

        foreach ($booking->items as $item) {
            if ($item->quantity <= 0) {
                throw new RuntimeException('Invalid quantity');
            }

            $total += $item->ticket->price * $item->quantity;
        }

        if ($booking->customer->type === 'vip') {
            if ($total < $vip_tiers[0]) {
                $total *= $vip_reduction[0];
            } else if ($total < $vip_tiers[1]) {
                $total *= $vip_reduction[1];
            }
            else {
                $total *= $vip_reduction[2];
            }
        }

        if ($booking->passType === '3days' && $total >= $days_reduction) {
            $total -= $days_reduction;
        }
        if ($total < 0.0) {
            throw new RuntimeException('Total cannot be negative');
        }
        return $total;
    }
}