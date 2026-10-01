<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Repository\OrderDetailsRepository;
use App\Repository\ProductsRepository;
use App\Service\EmailService;
use App\Service\PdfGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CartService;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $cartSession = $session->get('cart');
        $total_amount = 0;

        if (!is_null($cartSession) && count($cartSession["id"]) > 0) {

            for ($i = 0; $i < count($cartSession["id"]); $i++) {
                $total_amount += $cartSession["price"][$i] * $cartSession["quantity"][$i];
            }

        }

        return $this->render('cart/index.html.twig', [
            'cart_items' => $cartSession,
            'total_amount' => $total_amount
        ]);
    }

    #[Route('/cart/empty', name: 'app_empty_cart', methods: ['GET'])]
    public function emptyCart(Request $request): Response
    {

        // je récupère la session 
        $session = $request->getSession();
        $session->set('cart', [
            "id" => [],
            "name" => [],
            "description" => [],
            "stock" => [],
            "price" => [],
            "image" => [],
            "quantity" => [],
        ]); // je vide le panier
        return $this->redirectToRoute('app_cart');

    }

    #[Route('/cart/{idProduct}', name: 'app_add_cart', methods: ['POST'])]
    public function addProductToCart(int $idProduct, Request $request, ProductsRepository $productsRepository): Response
    {

        // créer la session
        $session = $request->getSession();

        // si elle existe pas je la créé
        if (!$session->get('cart')) {
            $session->set('cart', [
                "id" => [],
                "title" => [],
                "description" => [],
                "stock" => [],
                "price" => [],
                "image" => [],
                "quantity" => [],
            ]);
        }
        // je la récupère
        $cartSession = $session->get('cart');

        // je récupère les infos du produit en bdd que je souhaite ajouter à mon panier
        $product = $productsRepository->find($idProduct);

        // j'alimente ma sessions panier avec les infos du produit

        $cartSession["id"][] = $product->getId();
        $cartSession["name"][] = $product->getName();
        $cartSession["description"][] = $product->getDescription();
        $cartSession["stock"][] = $product->getStock();
        $cartSession["price"][] = $product->getPrice();
        $cartSession["image"][] = $product->getImage();
        $quantity = $request->request->getInt('quantity', 1); // si aucune quantité n'est postée, défaut à 1
        $cartSession["quantity"][] = $quantity;


        // mettre à jour la session
        $session->set('cart', $cartSession);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/delete/{idProduct}', name: 'app_delete_cart')]
    public function deleteProductFromCart(int $idProduct, Request $request, ProductsRepository $productsRepository): Response
    {

        // récupérer la session
        $session = $request->getSession();
        $cartSession = $session->get('cart', []);

        // Vérifier si l'article existe dans le panier
        if (($key = array_search($idProduct, $cartSession['id'])) !== false) {
            // Supprimer toutes les informations liées à cet article
            unset($cartSession['id'][$key]);
            unset($cartSession['name'][$key]);
            unset($cartSession['description'][$key]);
            unset($cartSession['stock'][$key]);
            unset($cartSession['price'][$key]);
            unset($cartSession['image'][$key]);
            unset($cartSession['quantity'][$key]);

            // Réindexer les tableaux pour éviter les trous dans les clés
            $cartSession['id'] = array_values($cartSession['id']);
            $cartSession['name'] = array_values($cartSession['name']);
            $cartSession['description'] = array_values($cartSession['description']);
            $cartSession['stock'] = array_values($cartSession['stock']);
            $cartSession['price'] = array_values($cartSession['price']);
            $cartSession['image'] = array_values($cartSession['image']);
            $cartSession['quantity'] = array_values($cartSession['quantity']);
        }

        // Mettre à jour la session avec le nouveau panier
        $session->set('cart', $cartSession);

        // Rediriger vers la page du panier
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/modify/quantity', name: 'app_modify_cart', methods: ['POST'])]
    public function modifyQuantityCart(Request $request, ProductsRepository $productsRepository): Response
    {

        // récupérer la valeur des paramètres post depuis un formulaire
        $idProduct = $request->request->get('idProduct');
        $quantity = $request->request->get('quantity');

        // récupérer la session
        $session = $request->getSession();
        $cartSession = $session->get('cart', []);

        if (($key = array_search($idProduct, $cartSession['id'])) !== false) {

            // mettre à jour la quantité
            $cartSession['quantity'][$key] = $quantity;

            // mettre à jour la session
            $session->set('cart', $cartSession);

            $this->addFlash('success', 'La quantité a bien été modifié !');

        }

        // Rediriger vers la page du panier
        return $this->redirectToRoute('app_cart');

    }

    #[Route('/cart/count', name: 'cart_count')]
    public function cartCount(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $cartSession = $session->get('cart', []);

        $count = 0;
        if (!empty($cartSession['quantity'])) {
            foreach ($cartSession['quantity'] as $qty) {
                $count += $qty;
            }
        }

        return new JsonResponse(['count' => $count]);
}


    

}
