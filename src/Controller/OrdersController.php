<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\OrdersRepository;
use Symfony\Bundle\SecurityBundle\SecurityBundle;

final class OrdersController extends AbstractController
{
    #[Route('/orders', name: 'app_orders')]
    public function index(OrdersRepository $ordersRepository): Response
    {

        $user = $this->getUser();
        
        $orders = $ordersRepository->findBy(['user_id' => $user]);

        return $this->render('orders/index.html.twig', [
            'orders' => $orders
        ]);
    }
}
