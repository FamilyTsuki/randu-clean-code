<?php

declare(strict_types=1);

final class AddLoyaltyPointsListener implements BookingConfirmationListener
{
    private LoyaltyService $loyaltyService;

    public function __construct(?LoyaltyService $loyaltyService = null)
    {
        $this->loyaltyService = $loyaltyService ?? new LoyaltyService();
    }

    public function onBookingConfirmed(Booking $booking, float $total): void
    {
        $points = (int) floor($total);
        $this->loyaltyService->addPoints($booking->customer->id, $points);
    }
}
