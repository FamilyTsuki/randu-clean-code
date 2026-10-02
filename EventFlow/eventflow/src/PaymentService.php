<?php

declare(strict_types=1);

final class PaymentService
{
    private array $gateways = [];
    
    public function __construct(array $gateways = [])
    {
        $this->gateways = $gateways ?: [
            'stripe' => new PaymentSupervisionDecorator(new StripeAdapter(), 'Stripe'),
            'payfast' => new PaymentSupervisionDecorator(new PayFastAdapter(), 'PayFast'),
        ];
    }

    public function registerGateway(string $name, PaymentGateway $gateway): void
    {
        $this->gateways[$name] = $gateway;
    }

    public function pay(float $amount, string $paymentMethod = 'stripe'): string
    {


        if (!isset($this->gateways[$paymentMethod])) {
            throw new RuntimeException('Unknown payment method');
        }

        $transactionId = $this->gateways[$paymentMethod]->pay($amount);
        echo "PAYMENT {$transactionId}" . PHP_EOL;

        return $transactionId;
    }
}

