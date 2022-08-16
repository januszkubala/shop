<?php

namespace App\Controller;

use App\Entity\Price;
use App\Repository\PriceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Cookie;

class CheckoutController extends AbstractController
{

    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(Request $request, EntityManagerInterface $entityManager): Response
    {

        $cart = base64_decode($request->cookies->get('cart'));

        if(empty($cart)) {

            return $this->render('checkout/empty.html.twig', [
            ]);

        }
        else {
        
            return $this->render('checkout/index.html.twig', [
                'stripe' => [
                    'publishableKey' => $this->getParameter('stripe_publishable_key')
                ]
            ]);

        }

    }

}
