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

        self::assertResponseStatusCodeSame(302);
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

    public function testAuthorizedUserWithoutCreateRoleGetsForbidden(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getOrCreateLimitedUser());

        $client->request('GET', '/order');

        self::assertResponseStatusCodeSame(403);
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

        if (!in_array('ROLE_ORDER_CREATE', $user->getRoles(), true)) {
            $roles = $user->getRoles();
            $roles[] = 'ROLE_ORDER_CREATE';
            $user->setRoles(array_values(array_unique($roles)));

            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            $entityManager->flush();
        }

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

    private function getOrCreateLimitedUser(): User
    {
        $repository = static::getContainer()->get(UserRepository::class);
        $user = $repository->findOneBy(['email' => 'limited_user@example.com']);

        if ($user instanceof User) {
            return $user;
        }

        $user = (new User())
            ->setEmail('limited_user@example.com')
            ->setRoles(['ROLE_VIEWER'])
            ->setPassword('$2y$10$5SggCzFMHkbwVxFrSPzZKeLqRvJe1NGaH1CBSW50LlPiwXnvRTDu.');

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}
