<?php

namespace App\Controller;

use App\Entity\Sale;
use App\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe;
use Stripe\StripeClient;
use Ramsey\Uuid\Uuid;

class StripeController extends AbstractController
{
    #[Route('/stripe/create-charge', name: 'app_stripe_create_charge')]
    public function createCharge(Request $request, EntityManagerInterface $entityManager): Response
    {

        $stripe = new StripeClient($this->getParameter('stripe_secret_key'));

        $customer = $stripe->customers->create([
            'name' => join(' ', [$request->query->get('firstName'), $request->query->get('lastName')]),
            'email' => $request->query->get('email'),
            'phone' => $request->query->get('phone'),
            'address' => [
                'city' => $request->query->get('city'),
                'country' => $request->query->get('country'),
                'line1' => $request->query->get('street'),
                'postal_code' => $request->query->get('postalCode'),
                'state' => $request->query->get('region')
            ]
        ]);

        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $request->query->get('amount') * 100,
            'currency' => 'eur',
            'customer' => $customer->id,
            'payment_method_types' => ['card'],
            'payment_method_data' => [
                'type' => 'card',
                'card' => [
                    'token' => $request->query->get('stripeToken')
                ]
            ],
            'metadata' => ['ref' => $request->get('ref')]
        ]);

        $paymentConfirmation = $stripe->paymentIntents->confirm(
            $paymentIntent->id
        );

        $paymentMethod = $paymentConfirmation->charges->data[0]->payment_method_details->card;
        
        $sale = $entityManager->getRepository(Sale::class)->findOneBy(['ref' => $request->get('ref')]);
        
        $expiryDate = \DateTime::createFromFormat('m-Y', ($paymentMethod->exp_month . '-' . $paymentMethod->exp_year));
        $expiryDate->modify('last day of this month')->setTime(23,59,59);

        $guid = $uuid = Uuid::uuid4();

        $payment = new Payment();
        $payment->setUser($sale->getUser());
        $payment->setAllocation($sale);
        $payment->setAmount(number_format($paymentConfirmation->amount_received / 100, 2, ".", ""));
        $payment->setRef($guid);
        $payment->setSystemRef($paymentConfirmation->id);
        $payment->setCardBrand($paymentMethod->brand);
        $payment->setCardExpiryDate($expiryDate);
        $payment->setCardNumber($paymentMethod->last4);
        $payment->setMethod('stripe');
        $payment->setStatus($paymentConfirmation->status);
        $payment->setIsAutomatic(true);
        $payment->setDate(new \DateTime());
        
        $response = new Response();

        if($paymentConfirmation->status == 'succeeded') {

            $payment->setDateCompleted(\DateTime::createFromFormat('U', $paymentConfirmation->charges->data[0]->created));
            $response->headers->removeCookie('cart', '/', null);
            $sale->setStatus('paid');
            $entityManager->persist($sale);

        }
        
        $entityManager->persist($payment);
        $entityManager->flush();

        if($paymentConfirmation->status == 'succeeded') {

            return $this->redirectToRoute('app_stripe_create_charge', $saleData);

        }

    }
}
