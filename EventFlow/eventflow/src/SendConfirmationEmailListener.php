<?php

declare(strict_types=1);

final class SendConfirmationEmailListener implements BookingConfirmationListener
{
    private EmailService $emailService;

    public function __construct(?EmailService $emailService = null)
    {
        $this->emailService = $emailService ?? new EmailService();
    }

    public function onBookingConfirmed(Booking $booking, float $total): void
    {
        $this->emailService->sendConfirmation(
            $booking->customer->email,
            $booking->id
        );
    }
}
