<?php

declare(strict_types=1);

final class Customer
{
    public readonly int $id;
    public readonly Email $email;
    public readonly ?string $phone;
    public readonly string $type;

    public function __construct(
        int $id,
        Email $email,
        ?string $phone = null,
        string $type = 'standard'
    ) {
        if ($id <= 0) {
            throw new RuntimeException('Invalid customer id');
        }

        $normalizedType = strtolower(trim($type));
        if (!in_array($normalizedType, ['standard', 'vip'], true)) {
            throw new RuntimeException('Invalid customer type');
        }

        $this->id = $id;
        $this->email = $email;
        $this->phone = $phone !== null ? trim($phone) : null;
        $this->type = $normalizedType;
    }

    public function isVip(): bool
    {
        return $this->type === 'vip';
    }
}
