<?php

declare(strict_types=1);

final class BookingItem
{
    public function __construct(
        public readonly Ticket $ticket,
        public readonly int $quantity
    ) {
        if ($quantity <= 0) {
            throw new RuntimeException('Invalid quantity');
        }
    }
}
