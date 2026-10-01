<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Form\CommentType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

final class CommentController extends AbstractController
{
    #[Route('/comment', name: 'app_comment')]
    public function index(Request $request, CommentRepository $commentRepository, PaginatorInterface $paginator): Response
    {
        $query = $commentRepository->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC');

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5 // nombre de commentaires par page
        );

        return $this->render('comment/index.html.twig', [
            'comments' => $pagination,
        ]);
    }

    #[Route('/comment/new', name: 'app_comment_new')]
public function new(Request $request, EntityManagerInterface $em): Response
{
    // Vérifie si l'utilisateur est connecté
    if (!$this->getUser()) {
        $this->addFlash('error', 'Vous devez être connecté pour poster un avis.');
        return $this->redirectToRoute('app_login');
    }

    $comment = new Comment();
    $form = $this->createForm(CommentType::class, $comment);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $comment->setUser($this->getUser());
        $comment->setCreatedAt(new \DateTime());

        $em->persist($comment);
        $em->flush();

        $this->addFlash('success', 'Votre commentaire a été publié !');
        return $this->redirectToRoute('app_comment');
    }

    return $this->render('comment/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

}
