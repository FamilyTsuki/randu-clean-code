<?php

declare(strict_types=1);

final class SendConfirmationSmsListener implements BookingConfirmationListener
{
    private SmsClient $smsClient;

    public function __construct(?SmsClient $smsClient = null)
    {
        $this->smsClient = $smsClient ?? new SmsClient();
    }

    public function onBookingConfirmed(Booking $booking, float $total): void
    {
        if ($booking->customer->phone === null || trim($booking->customer->phone) === '') {
            return;
        }

        $this->smsClient->send(
            $booking->customer->phone,
            "Booking {$booking->id} confirmed"
        );
    }
}
