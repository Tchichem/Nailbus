<?php

namespace App\Controller;

use App\Entity\Products;
use App\Entity\Orders;
use App\Entity\OrderDetails;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use App\Entity\Payment;
use App\Entity\PaymentDetail;
use App\Repository\ProductsRepository;
use App\Service\EmailService;
use App\Service\PdfGeneratorService;



class StripeController extends AbstractController
{
    #[Route('/buy', name: 'app_buy')]
public function buy(
    Request $request,
    StripeClient $stripe,
    ParameterBagInterface $params,
    EntityManagerInterface $em
): Response {
    $user = $this->getUser();
    if (!$user) {
        throw $this->createAccessDeniedException('Vous devez être connecté pour effectuer un achat.');
    }
    $taxRateId = $params->get('stripe.tax_rate_id');

    $sessionSymfony = $request->getSession();
    $cart = $sessionSymfony->get('cart', []);
    $lineItems = [];

    for ($i=0; $i < count($cart['id']); $i++) { 

        $article = $em->getRepository(Products::class)->find($cart['id'][$i]);
        if (!$article) {
            continue;
        }

        // Créer un prix Stripe si besoin
        if (!$article->getStripePriceId()) {
            $product = $stripe->products->create([
                'name' => $article->getName(),
            ]);

            $priceAmount = (float) str_replace(',', '.', $article->getPrice());

            $stripePrice = $stripe->prices->create([
                'unit_amount' => intval($priceAmount * 100),
                'currency' => 'eur',
                'product' => $product->id,
                'tax_behavior' => 'exclusive',
            ]);

            $article->setStripePriceId($stripePrice->id);
            $em->persist($article);
            $em->flush();
        }

        // Ajouter l'article dans les line_items
        $lineItems[] = [
            'price' => $article->getStripePriceId(),
            'quantity' => $cart['quantity'][$i],
            'tax_rates' => [$taxRateId],
        ];
    }

    if (empty($lineItems)) {
        $this->addFlash('warning', 'Votre panier est vide ou invalide.');
        return $this->redirectToRoute('app_cart');
    }
    

    $successUrl = $this->getParameter('app.base_url') . '/payment/success?session_id={CHECKOUT_SESSION_ID}';
    $cancelUrl = $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL);
    
    $session = $stripe->checkout->sessions->create([
        'customer_email' => $user->getEmail(),
        'payment_method_types' => ['card'],
        'line_items' => [$lineItems],
        'mode' => 'payment',
        'success_url' => $successUrl,
        'cancel_url' => $cancelUrl,
        'customer_creation' => 'always'
    ]);

    return $this->redirect($session->url);
}

    #[Route('/payment/success', name: 'payment_success')]
    public function paymentSuccess(
        Security $security,
        EmailService $emailService,
        EntityManagerInterface $em,
        PdfGeneratorService $pdfGeneratorService,
        ProductsRepository $articleRepository,
        Request $request,
    ): Response {

        $sessionSymfony = $request->getSession();
        $user = $security->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        // session stripe lié au paiement actuel
        $sessionId = $request->query->get('session_id');
        if (!$sessionId) {
            throw $this->createNotFoundException("Session Stripe manquante.");
        }

        // ✅ Vérifie si le paiement a déjà été enregistré et validé
        $existingPayment = $em->getRepository(Payment::class)->findOneBy([
            'stripeSessionId' => $sessionId,
            'user' => $user
        ]);
        
        // si le client est déjà tombé dans la page de confirmation
        // alors redirection vers la page liée aux factures
        // pour ne pas enregistrer son paiement une deuxième fois
        if ($existingPayment && $existingPayment->isVerified()) {
            // 🔁 Redirige vers les factures si déjà traité
            $this->addFlash('info', 'Ce paiement a déjà été traité.');
            return $this->redirectToRoute('app_profile_invoices');
        }

        
        // Récupération du panier
        $cart = $sessionSymfony->get('cart', []);
        if (!isset($cart["id"]) || !is_array($cart["id"]) || empty($cart["id"])) {
            $this->addFlash('danger', 'Votre panier est vide ou invalide.');
            return $this->redirectToRoute('app_cart');
        }
        
        // Nouveau paiement
        $payment = new Payment();
        $payment->setUser($user);
        $payment->setPaidAt(new \DateTimeImmutable());
        $payment->setStripeSessionId($sessionId);
        $payment->setType('article');

        $totalAmount = 0;

        for ($i=0; $i < count($cart["id"]); $i++) { 
            $article = $articleRepository->find($cart["id"][$i]);
            if (!$article) continue;

                $quantity = $cart["quantity"][$i];
                $price = floatval($cart["price"][$i]);
                $subtotal = $price * $quantity;
                $totalAmount += $subtotal;

                $paymentDetail = new PaymentDetail();
                $paymentDetail->setProduct($article);
                $paymentDetail->setQuantity($quantity);
                $paymentDetail->setPrice($price);
                $paymentDetail->setTitle($article->getName());
                $paymentDetail->setPayment($payment);
                
                $em->persist($paymentDetail);
                $payment->addPaymentDetail($paymentDetail);
        }

        $payment->setAmount($totalAmount);
        $em->persist($payment);
        $em->flush();

        // Recuperer la valeur des parametres post depuis un formulaire
        // $totalAmount = $request->request->get('total_amount');

        // Recuperer la session
        $session = $request->getSession();

        if (!empty($cart["id"])) {

            $order = new Orders(); // Je creer une nouvelle commande
            $order->setPrice($totalAmount); // Je lui donne le montant total des articles
            $order->setDate(new \DateTime()); // Je donne la date a laquelle il a passé la commande
            $order->setStatus("En cours"); // Je change le status de la commande
            $order->setUserId($user); // Je lie la commande au User

            $fileName = $user->getId() . "_" . $user->getLastName() . "_" . $user->getFirstName() . "_invoice_" . time() . ".pdf";

            $order->setInvoice($fileName);

            $em->persist($order);

            for ($i=0; $i < count($cart["id"]); $i++) { 

                $product = $articleRepository->find($cart["id"][$i]); // Je récupere le produit en bdd pour le lier au detail de commande
                if (!$product) continue;

                $order_detail = new OrderDetails();

                $order_detail->setProduct($product);
                $order_detail->setQuantity($cart["quantity"][$i]);
                $order_detail->setRelatedOrder($order);
                $order_detail->setSubtotal($cart["quantity"][$i] * $cart["price"][$i]);
                
                $em->persist($order_detail);

                // Mettre à jour le stock du produit après l'achat
                $product->setStock($product->getStock() - $cart["quantity"][$i]); // Décrémenter le stock
                $em->persist($product); // Persist du produit modifié
            }

            $em->flush();

            // 3. Génération de la facture PDF
            $pdfContent = $pdfGeneratorService->generatePdf([
                
                    'date' => new \DateTime(),
                    'user' => $user,
                    'order' => $order,
                    'payment' => $payment

            ], $fileName, 'invoice/index.html.twig', 'uploads/invoices/');
            
    
            $emailService->sendEmail($user->getEmail(),
                    $pdfContent, 'facture-' . $order->getId() . 'nailbus.pdf', [
                    
                    'date' => new \DateTime(),
                    'user' => $payment->getUser(),
                    'articles' => $cart,
                    'payment' => $payment,

                    ],
                    "Merci pour votre achat !", 
                "invoice/email.html.twig");

            // je vide le panier
            $session->set('cart', [
                "id" => [],
                "title" => [],
                "description" => [],
                "stock" => [],
                "price" => [],
                "picture" => [],
                "quantity" => [],
            ]);
        }

        $this->addFlash('success', 'La commande a bien été effectuée !');

        
        // 7. Récupération de toutes les factures
        $payments = $em->getRepository(Payment::class)->findBy(['user' => $user], ['paidAt' => 'DESC']);

        return $this->render('invoice/invoice.html.twig', [
            'user' => $payment->getUser(),
            'articles' => $cart,
            'payment' => $payment,
            'payments' => $payments,
            'order' => $order
        ]);

    }


    #[Route('/payment/cancel', name: 'payment_cancel')]
    public function paymentCancel(): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé.');
        return $this->redirectToRoute('app_cart'); // ou autre page pertinente
    }
    
    }