<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductsRepository;
use App\Repository\ServicesRepository;
use App\Repository\CommentRepository;

final class HomeController extends AbstractController{
    #[Route('/', name: 'app_home')]
    public function index(ProductsRepository $productsRepository, ServicesRepository $servicesRepository, CommentRepository $commentRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $productsRepository->findBy([], ['id' => 'DESC'], 3),
            'comments' => $commentRepository->findBy([], ['id' => 'DESC'], 3),
        ]);
    }
}
