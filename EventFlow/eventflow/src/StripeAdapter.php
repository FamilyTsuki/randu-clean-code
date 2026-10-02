<?php

declare(strict_types=1);

final class StripeAdapter implements PaymentGateway
{
    private StripeClient $client;

    public function __construct(?StripeClient $client = null)
    {
        $this->client = $client ?? new StripeClient();
    }

    public function pay(float $amount): string
    {
        return $this->client->charge($amount);
    }
}

