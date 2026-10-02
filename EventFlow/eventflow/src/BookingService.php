<?php

declare(strict_types=1);

final class BookingService
{
    public function __construct(
        private ?BookingPricingService $pricingService = null,
        private ?PaymentService $paymentService = null,
        ?array $listeners = null
    ) {
        $this->pricingService = $pricingService ?? new BookingPricingService();
        $this->paymentService = $paymentService ?? new PaymentService();
        $this->listeners = $listeners ?? [
            new SendConfirmationEmailListener(),
            new AddLoyaltyPointsListener(),
            new TrackAnalyticsListener(),
            new SendConfirmationSmsListener(),
        ];
    }

    public function addListener(BookingConfirmationListener $listener): void
    {
        $this->listeners[] = $listener;
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

        foreach ($this->listeners as $listener) {
            $listener->onBookingConfirmed($booking, $total);
        }

        return $total;
    }
}