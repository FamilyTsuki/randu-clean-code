<?php

declare(strict_types=1);

final class TrackAnalyticsListener implements BookingConfirmationListener
{
    private AnalyticsClient $analyticsClient;

    public function __construct(?AnalyticsClient $analyticsClient = null)
    {
        $this->analyticsClient = $analyticsClient ?? new AnalyticsClient();
    }

    public function onBookingConfirmed(Booking $booking, float $total): void
    {
        $this->analyticsClient->track('booking_confirmed', [
            'booking_id' => $booking->id,
            'customer_id' => $booking->customer->id,
            'total' => $total,
        ]);
    }
}
