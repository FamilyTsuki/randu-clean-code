<?php

declare(strict_types=1);

final class BookingService
{
    private PaymentService $paymentService;

    public function __construct(?PaymentService $paymentService = null)
    {
        $this->paymentService = $paymentService ?? new PaymentService();
    }

    public function confirm(Booking $booking, string $paymentMethod = 'stripe'): float
    {
        if (count($booking->items) === 0) {
            throw new RuntimeException('Empty booking');
        }

        if (!filter_var($booking->customer->email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Invalid email');
        }

        $total = 0.0;

        foreach ($booking->items as $item) {
            if ($item->quantity <= 0) {
                throw new RuntimeException('Invalid quantity');
            }

            $total += $item->ticket->price * $item->quantity;
        }

        // Ancienne règle VIP : remise fixe de 10 %.
        if ($booking->customer->type === 'vip') {
            $total *= 0.90;
        }

        // Ancienne règle Pass 3 jours : remise fixe de 10 euros.
        if ($booking->passType === '3days') {
            $total -= 10.0;
        }

        $this->paymentService->pay($total, $paymentMethod);

        $booking->status = 'confirmed';

        echo "SQL INSERT booking={$booking->id} total={$total} status={$booking->status}" . PHP_EOL;

        $emailService = new EmailService();
        $emailService->sendConfirmation($booking->customer->email, $booking->id);

        return $total;
    }
}
