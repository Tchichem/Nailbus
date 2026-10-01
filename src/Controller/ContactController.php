<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\Type\ContactType;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request,
        EmailService $emailService): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'success',
                'Votre message a bien été envoyé'
            );
            
            $contact = $form->getData();
            $emailService->sendEmailNoAttachement($form["email"]->getData(), ['contact' => $contact], 'Message reçu', 'email/contact.html.twig');
               
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form,
        ]);
        
    }
    
    
}

