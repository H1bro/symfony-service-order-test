<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Order\PlaceOrderHandler;
use App\Dto\PlaceOrderCommand;
use App\Entity\Order;
use App\Entity\User;
use App\Form\OrderType;
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
    public function new(Request $request, PlaceOrderHandler $placeOrderHandler): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ORDER_CREATE');

        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $placeOrderHandler->handle(
                new PlaceOrderCommand(
                    $order->getService(),
                    $order->getEmail(),
                    $this->getUserEntity(),
                ),
            );

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
