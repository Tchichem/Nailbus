<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\OrdersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\ChangePasswordFormType;

final class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(OrdersRepository $ordersRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $orders = $ordersRepository->findBy(['user_id' => $user]);

        $form = $this->createForm(ChangePasswordFormType::class);
        return $this->render('account/index.html.twig', [
            'orders' => $orders,
            'changePasswordForm' => $form->createView(),
        ]);
    }

    #[Route('/changer-mot-de-passe', name: 'account_change_password', methods: ['POST'])]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();


            if (!$user) {
                throw $this->createAccessDeniedException('Utilisateur non connecté.');
            }

            $currentPassword = $form->get('current_password')->getData();
            $newPassword = $form->get('new_password')->getData();
            $confirmPassword = $form->get('confirm_password')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('danger', 'Mot de passe actuel incorrect.');
                return $this->redirectToRoute('app_account');
            }

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('danger', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('app_account');
            }

            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $em->flush();

            $this->addFlash('success', 'Mot de passe mis à jour avec succès.');
        }

        return $this->redirectToRoute('app_account');
    }

     
    #[Route('/account/delete', name: 'account_delete', methods: ['POST'])]
    public function deleteAccount(EntityManagerInterface $em, Security $security, Request $request): Response
    {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('delete_account', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Supprimer les commentaires liés
        foreach ($user->getComments() as $comment) {
            $em->remove($comment);
        }

        // Supprimer les commandes liées
        foreach ($user->getOrders() as $order) {
            $em->remove($order);
        }

        // Supprimer l'utilisateur
        $em->remove($user);
        $em->flush();

        // Déconnexion
        $security->logout(false);

        return $this->redirectToRoute('app_logout');
    }

}
