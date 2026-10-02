<?php

declare(strict_types=1);

final class PaymentService
{
    public function pay(float $amount, string $paymentMethod = 'stripe'): string
    {
        if ($paymentMethod === 'stripe') {
            $stripe = new StripeClient();
            $transactionId = $stripe->charge($amount);
            echo "PAYMENT {$transactionId}" . PHP_EOL;
            return $transactionId;
        }

        if ($paymentMethod === 'payfast') {
            throw new RuntimeException('PayFast not implemented');
        }

        throw new RuntimeException('Unknown payment method');
    }
}
