<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Order;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrderFlowTest extends WebTestCase
{
    public function testGuestCannotAccessOrderPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/order');

        self::assertResponseRedirects('/login');
    }

    public function testAuthorizedUserSeesOrderForm(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUserByEmail('user1@example.com'));

        $crawler = $client->request('GET', '/order');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('select[name="order[service]"]');
        self::assertSelectorExists('input[name="order[email]"]');
        self::assertSelectorExists('button[type="submit"]');
        self::assertStringContainsString('Подтвердить', $crawler->filter('button[type="submit"]')->text());
    }

    public function testInvalidOrderSubmissionShowsValidationErrors(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUserByEmail('user1@example.com'));

        $client->request('GET', '/order');
        $client->submitForm('Подтвердить', [
            'order[service]' => '',
            'order[email]' => '',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', 'Выберите услугу.');
        self::assertSelectorTextContains('body', 'Укажите email.');
    }

    public function testValidOrderCreatesRecordInStorage(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getUserByEmail('user2@example.com'));

        $email = 'valid-order@example.com';
        $this->cleanupOrdersByEmail($email);

        $client->request('GET', '/order');
        $client->submitForm('Подтвердить', [
            'order[service]' => 'Оценка стоимости квартиры',
            'order[email]' => $email,
        ]);

        self::assertResponseRedirects('/order');
        $client->followRedirect();
        self::assertSelectorTextContains('body', 'Заказ успешно сохранен.');

        $savedOrder = $this->getOrderRepository()->findOneBy(['email' => $email]);
        self::assertNotNull($savedOrder);
        self::assertSame('Оценка стоимости квартиры', $savedOrder->getService());
        self::assertSame(1500, $savedOrder->getPrice());
    }

    private function getUserByEmail(string $email): User
    {
        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => $email]);

        self::assertNotNull($user);

        return $user;
    }

    private function cleanupOrdersByEmail(string $email): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        foreach ($this->getOrderRepository()->findBy(['email' => $email]) as $order) {
            $entityManager->remove($order);
        }

        $entityManager->flush();
    }

    private function getOrderRepository(): OrderRepository
    {
        return static::getContainer()->get(OrderRepository::class);
    }
}
