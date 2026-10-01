<?php namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/appointments')]
class AppointmentController extends AbstractController
{
    #[Route('/', name: 'appointment_list', methods: ['GET', 'POST'])]
    public function index(AppointmentRepository $appointmentRepository): Response
    {
        $appointments = $appointmentRepository->findAll();
        $contact = new Appointment();
        $form = $this->createForm(AppointmentType::class, $contact);

        $listEvents = [];

        foreach ($appointments as $appointment) {
            $listEvents[] = [
                "title" => $appointment->getService(),
                "start" => $appointment->getStartTime()->format("Y-m-d\TH:i:s"), // Format ISO avec heure
                "end" => (clone $appointment->getStartTime())->modify("+{$appointment->getDuration()} minutes")->format("Y-m-d\TH:i:s"),
            ];
        }

        $data = json_encode($listEvents);
        
        return $this->render('appointment/index.html.twig', [
            'appointments' => $data,
            'form' => $form,
        ]);

    }

    #[Route('/new', name: 'appointment_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $appointment = new Appointment();
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($appointment);
            $em->flush();
    
            // Ajouter un message flash
            $this->addFlash('success', 'Le rendez-vous a été ajouté avec succès !');
    
            return $this->json(['success' => true]);
        }
    
        return $this->json(['success' => false, 'errors' => (string) $form->getErrors(true, false)], 400);
    }
    

}
