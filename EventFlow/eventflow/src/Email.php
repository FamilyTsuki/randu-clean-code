<?php

declare(strict_types=1);

final class Email implements Stringable
{
    private string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if (!filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Invalid email');
        }

        $this->value = $trimmed;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
