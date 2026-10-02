<?php

declare(strict_types=1);

final class BookingService
{
    public function __construct(
        private ?BookingPricingService $pricingService = null,
        private ?PaymentService $paymentService = null
    ) {
        $this->pricingService = $pricingService ?? new BookingPricingService();
        $this->paymentService = $paymentService ?? new PaymentService();
    }

    public function confirm(Booking $booking, string $paymentMethod = 'stripe'): float
    {
        if (count($booking->items) === 0) {
            throw new RuntimeException('Empty booking');
        }

        $total = $this->pricingService->calculate($booking);

        $this->paymentService->pay($total, $paymentMethod);

        $booking->status = 'confirmed';

        echo "SQL INSERT booking={$booking->id} total={$total} status={$booking->status}" . PHP_EOL;

        $emailService = new EmailService();
        $emailService->sendConfirmation(
            $booking->customer->email,
            $booking->id
        );

        return $total;
    }
}