<?php

declare(strict_types=1);

final class PayFastAdapter implements PaymentGateway
{
    private PayFastSdk $sdk;

    public function __construct(?PayFastSdk $sdk = null)
    {
        $this->sdk = $sdk ?? new PayFastSdk();
    }

    public function pay(float $amount): string
    {
        if ($amount <= 0) {
            throw new RuntimeException('Invalid amount');
        }

        $payload = [
            'reference' => bin2hex(random_bytes(4)),
            'amount_cents' => (int) round($amount * 100),
            'currency' => 'EUR',
        ];

        $result = $this->sdk->executePayment($payload);

        if (!$result['success']) {
            throw new RuntimeException('PayFast payment failed');
        }

        return $result['transaction_id'];
    }
}

