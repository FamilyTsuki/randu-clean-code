<?php

declare(strict_types=1);

final class BookingPricingService
{
    private const VIP_REDUCTION = [0.95, 0.90, 0.85];
    private const VIP_TIERS = [100.0, 299.0];

    public function calculate(Booking $booking): float
    {
        $days_reduction = 20.0;

        $total = 0.0;

        foreach ($booking->items as $item) {
            if ($item->quantity <= 0) {
                throw new RuntimeException('Invalid quantity');
            }

            $total += $item->ticket->price * $item->quantity;
        }

        $total = $this->applyVipDiscount($booking, $total);

        if ($booking->passType === '3days' && $total >= $days_reduction) {
            $total -= $days_reduction;
        }
        if ($total < 0.0) {
            throw new RuntimeException('Total cannot be negative');
        }
        return $total;
    }
    public function applyVipDiscount(Booking $booking, float $total): float {
        if ($booking->customer->isVip()) {
            if ($total < self::VIP_TIERS[0]) {
                $total *= self::VIP_REDUCTION[0];
            } else if ($total < self::VIP_TIERS[1]) {
                $total *= self::VIP_REDUCTION[1];
            }
            else {
                $total *= self::VIP_REDUCTION[2];
            }
        }
        return $total;
    }
}