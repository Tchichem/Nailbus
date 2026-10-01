<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductsRepository;
use App\Entity\Products;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Component\Pager\PaginatorInterface;


final class CatalogController extends AbstractController{
    #[Route('/catalog', name: 'app_catalog')]
    public function index(Request $request, ProductsRepository $productRepository, PaginatorInterface $paginator)
    {
        $query = $request->query->get('q');
        $qb = $productRepository->createQueryBuilder('p');

        if ($query) {
            $qb->where('p.name LIKE :query OR p.description LIKE :query')
            ->setParameter('query', '%'.$query.'%');
        }

        $pagination = $paginator->paginate(
            $qb, 
            $request->query->getInt('page', 1), // Numéro de page passé en paramètre "page", par défaut 1
            5 // Nombre d'éléments par page
        );

        return $this->render('catalog/index.html.twig', [
            'pagination' => $pagination,
            'query' => $query,
        ]);
    }

    #[Route('/catalog/filter-price', name: 'app_catalog_filter_price', methods: ['GET'])]
    public function filterByPrice(Request $request, ProductsRepository $productRepository, PaginatorInterface $paginator): JsonResponse
    {
        $maxPrice = $request->query->get('max_price');
        $page = $request->query->getInt('page', 1);

        $qb = $productRepository->createQueryBuilder('p');

        if ($maxPrice !== null && is_numeric($maxPrice)) {
            $qb->andWhere('p.price <= :maxPrice')
            ->setParameter('maxPrice', $maxPrice);
        }

        $pagination = $paginator->paginate(
            $qb,
            $page,
            10
        );

        $html = $this->renderView('catalog/_product_list.html.twig', [
            'pagination' => $pagination,
        ]);

        return new JsonResponse(['html' => $html]);
    }


    #[Route('/catalog/search', name: 'app_catalog_search', methods: ['GET'])]
    public function searchByQuery(Request $request, ProductsRepository $productRepository): JsonResponse
    {
        $query = $request->query->get('q');

        if ($query) {
            $products = $productRepository->createQueryBuilder('p')
                ->where('p.name LIKE :query OR p.description LIKE :query')
                ->setParameter('query', '%'.$query.'%')
                ->getQuery()
                ->getResult();
        } else {
            $products = $productRepository->findAll();
        }

        $html = $this->renderView('catalog/_product_list.html.twig', [
            'products' => $products,
        ]);

        return new JsonResponse(['html' => $html]);
    }


    #[Route('/catalog/{id}', name: 'app_catalog_show', methods: ['GET'])]
    public function show(Products $product): Response
    {
        return $this->render('catalog/show.html.twig', [
            'product' => $product,
        ]);
    }

}
