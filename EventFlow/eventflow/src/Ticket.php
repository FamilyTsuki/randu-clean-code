<?php

declare(strict_types=1);

final class Ticket
{
    public readonly string $code;
    public readonly string $label;
    public readonly float $price;

    public function __construct(
        string $code,
        string $label,
        float $price
    ) {
        $trimmedCode = trim($code);
        if ($trimmedCode === '') {
            throw new RuntimeException('Invalid ticket code');
        }

        $trimmedLabel = trim($label);
        if ($trimmedLabel === '') {
            throw new RuntimeException('Invalid ticket label');
        }

        if ($price <= 0.0) {
            throw new RuntimeException('Invalid ticket price');
        }

        $this->code = $trimmedCode;
        $this->label = $trimmedLabel;
        $this->price = $price;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}
