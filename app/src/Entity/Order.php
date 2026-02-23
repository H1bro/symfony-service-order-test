<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    public const SERVICES = [
        'Оценка стоимости автомобиля' => 500,
        'Оценка стоимости квартиры' => 1500,
        'Оценка стоимости бизнеса' => 5000,
    ];

    private const SERVICE_NAMES = [
        'Оценка стоимости автомобиля',
        'Оценка стоимости квартиры',
        'Оценка стоимости бизнеса',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Выберите услугу.')]
    #[Assert\Choice(choices: self::SERVICE_NAMES, message: 'Выбрана некорректная услуга.')]
    private string $service = '';

    #[ORM\Column]
    private int $price = 0;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Укажите email.')]
    #[Assert\Email(message: 'Укажите корректный email.')]
    private string $email = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function setService(?string $service): self
    {
        $this->service = $service ?? '';

        return $this;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = mb_strtolower($email ?? '');

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
