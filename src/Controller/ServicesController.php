<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ServicesRepository;
use App\Entity\Services;

final class ServicesController extends AbstractController{
    #[Route('/services', name: 'app_services')]
    public function index(Request $request, ServicesRepository $servicesRepository)
    {
        $query = $request->query->get('q'); // Récupération du terme de recherche
        $services = [];

        if ($query) {
            $services = $servicesRepository->createQueryBuilder('p')
                ->where('p.name LIKE :query OR p.description LIKE :query')
                ->setParameter('query', '%'.$query.'%')
                ->getQuery()
                ->getResult();
        } else {
            $services = $servicesRepository->findAll();
        }

        return $this->render('services/index.html.twig', [
            'services' => $services,
            'query' => $query
        ]);
    }

    #[Route('/services/{id}', name: 'app_services_show', methods: ['GET'])]
    public function show(Services $service): Response
    {
        return $this->render('services/show.html.twig', [
            'service' => $service,
        ]);
    }

}
