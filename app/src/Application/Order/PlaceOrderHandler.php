<?php

declare(strict_types=1);

namespace App\Application\Order;

use App\Dto\PlaceOrderCommand;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;

final class PlaceOrderHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function handle(PlaceOrderCommand $command): Order
    {
        $order = new Order();
        $order->setService($command->getService());
        $order->setEmail($command->getEmail());
        $order->setPrice(Order::SERVICES[$command->getService()] ?? 0);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setCreatedBy($command->getCreatedBy());

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }
}
