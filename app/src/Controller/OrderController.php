<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Form\OrderType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(): RedirectResponse
    {
        return $this->redirectToRoute('app_order_new');
    }

    #[Route('/order', name: 'app_order_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service = $order->getService();
            $order->setPrice(Order::SERVICES[$service] ?? 0);
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setCreatedBy($this->getUserEntity());

            $entityManager->persist($order);
            $entityManager->flush();

            $this->addFlash('success', 'Заказ успешно сохранен.');

            return $this->redirectToRoute('app_order_new');
        }

        return $this->render('order/new.html.twig', [
            'orderForm' => $form,
            'services' => Order::SERVICES,
        ]);
    }

    private function getUserEntity(): User
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('User is not authenticated.');
        }

        return $user;
    }
}
