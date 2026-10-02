<?php

declare(strict_types=1);

final class PaymentService
{
    private array $gateways = [];
    
    public function __construct(array $gateways = [])
    {
        $this->gateways = $gateways ?: [
            'stripe' => new StripeAdapter(),
        ];
    }

    public function registerGateway(string $name, PaymentGateway $gateway): void
    {
        $this->gateways[$name] = $gateway;
    }

    public function pay(float $amount, string $paymentMethod = 'stripe'): string
    {
        if ($paymentMethod === 'payfast' && !isset($this->gateways['payfast'])) {
            throw new RuntimeException('PayFast not implemented');
        }

        if (!isset($this->gateways[$paymentMethod])) {
            throw new RuntimeException('Unknown payment method');
        }

        $transactionId = $this->gateways[$paymentMethod]->pay($amount);
        echo "PAYMENT {$transactionId}" . PHP_EOL;

        return $transactionId;
    }
}

