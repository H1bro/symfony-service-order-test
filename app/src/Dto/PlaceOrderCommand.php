<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\User;

final class PlaceOrderCommand
{
    public function __construct(
        private readonly string $service,
        private readonly string $email,
        private readonly User $createdBy,
    ) {
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }
}
