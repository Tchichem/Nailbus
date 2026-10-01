<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'legal_mentions')]
    public function mentionsLegales(): Response
    {
        return $this->render('legal/mentions_legales.html.twig');
    }

    #[Route('/cgv', name: 'legal_cgv')]
    public function conditionsGeneralesVente(): Response
    {
        return $this->render('legal/cgv.html.twig');
    }

    #[Route('/conditions-de-reservation', name: 'legal_reservation')]
    public function conditionsReservation(): Response
    {
        return $this->render('legal/conditions_reservation.html.twig');
    }

    #[Route('/confidentialite', name: 'legal_confidentialite')]
    public function politiqueConfidentialite(): Response
    {
        return $this->render('legal/confidentialite.html.twig');
    }
}
