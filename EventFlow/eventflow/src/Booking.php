<?php

declare(strict_types=1);

final class Booking
{
    /** @var BookingItem[] */
    private array $items = [];
    private string $status = 'pending';

    public function __construct(
        public readonly int $id,
        public readonly Customer $customer,
        public readonly string $passType = 'day'
    ) {
        if ($id <= 0) {
            throw new RuntimeException('Invalid booking id');
        }

        if (!in_array($passType, ['day', '3days'], true)) {
            throw new RuntimeException('Invalid pass type');
        }
    }

    public function addItem(BookingItem $item): void
    {
        $this->items[] = $item;
    }

    public function markAsConfirmed(): void
    {
        $this->status = 'confirmed';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return count($this->items) === 0;
    }

    public function __get(string $name): mixed
    {
        return match ($name) {
            'status' => $this->status,
            'items' => $this->items,
            default => throw new RuntimeException("Undefined property: {$name}"),
        };
    }
}
