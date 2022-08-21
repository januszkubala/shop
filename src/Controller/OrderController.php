<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\OrderComponent;
use App\Repository\PriceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Ramsey\Uuid\Uuid;

class OrderController extends AbstractController
{

    #[Route('/order/create', name: 'app_order_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, PriceRepository $priceRepository): Response
    {

        $cart = json_decode(base64_decode($request->cookies->get('cart')), true);
        $paymentMethod = $request->get('paymentMethod');

        if(isset($cart['items']) && !empty($cart['items'])) {

            $guid = $uuid = Uuid::uuid4();

            $order = new Order();
            $order->setUser($this->getUser());
            $order->setDate(new \DateTime());
            $order->setRef($guid);
            $order->setFirstName($this->getUser()->getFirstName());
            $order->setLastName($this->getUser()->getLastName());
            $order->setCity($this->getUser()->getCity());
            $order->setStreet($this->getUser()->getStreet());
            $order->setPostalCode($this->getUser()->getPostalCode());
            $order->setRegion($this->getUser()->getRegion());
            $order->setCountry($this->getUser()->getCountry());
            $order->setPhone($this->getUser()->getPhone());
            $order->setEmail($this->getUser()->getEmail());

            $netAmount = 0;
            $taxAmount = 0;
            $amount = 0;
            $quantity = 0;

            foreach($cart['items'] as $itemId => $item) {

                $itemQuantity = (int) $item['quantity'];

                $product = $entityManager->getRepository(Product::class)->findOneBy(['id' => $itemId]);
                $price = $priceRepository->findCurrentPrice($product);

                if($product->getStock() >= $itemQuantity) {
                    
                    $tax = $price->getTax();

                    $orderComponent = new OrderComponent();
                    
                    $orderComponent->setUser($this->getUser());
                    $orderComponent->setParent($order);
                    $orderComponent->setProduct($product);
                    $orderComponent->setNetPrice($price->getPrice());
                    $orderComponent->setPrice(number_format($price->getPrice() + $price->getPrice() * $tax->getRate(), 2, '.', ''));
                    $orderComponent->setQuantity($itemQuantity);
                    $orderComponent->setTaxRate($tax->getRate());
                    $orderComponent->setTaxName($tax->getName());
                    $orderComponent->setNetAmount(number_format($orderComponent->getNetPrice() * $orderComponent->getQuantity(), 2, '.', ''));
                    $orderComponent->setAmount(number_format($orderComponent->getPrice() * $orderComponent->getQuantity(), 2, '.', ''));
                    $orderComponent->setTaxAmount(number_format($orderComponent->getAmount() - $orderComponent->getNetPrice(), 2, '.', ''));
                    $orderComponent->setName($product->getName());
                    $orderComponent->setSku($product->getSku());
                    $orderComponent->setEan($product->getEan());
                    $orderComponent->setGtin($product->getGtin());
                    $orderComponent->setIsbn($product->getIsbn());

                    $entityManager->persist($orderComponent);

                    $netAmount += $orderComponent->getNetAmount();
                    $taxAmount += $orderComponent->getTaxAmount();
                    $amount += $orderComponent->getAmount();
                    $quantity += $orderComponent->getQuantity();

                }
                else {

                    $errors[$id] = 'stock_exceeded';

                }
                
            }

            $orderData = [
                'firstName' => $order->getFirstName(),
                'lastName' => $order->getLastName(),
                'city' => $order->getCity(),
                'street' => $order->getStreet(),
                'postalCode' => $order->getPostalCode(),
                'region' => $order->getRegion(),
                'country' => $order->getCountry(),
                'phone' => $order->getPhone(),
                'email' => $order->getEmail(),
                'amount' => $amount,
                'ref' => $order->getRef()
            ];

            if($paymentMethod == "stripe") {

                $orderData['stripeToken'] = $request->get('stripeToken');
            
            }

            if(empty($errors)) {

                $order->setNetAmount($netAmount);
                $order->setTaxAmount($taxAmount);
                $order->setAmount($amount);
                $order->setStatus('pending');
                $order->setQuantity($quantity);

                $cart['order_ref'] = $order->getRef();

                $response = new Response();
                $response->headers->setCookie(Cookie::create('cart', base64_encode(json_encode($cart))));
                
                $entityManager->persist($order);
                $entityManager->flush();

                if($paymentMethod == "stripe") {
                    
                    return $this->redirectToRoute('app_stripe_create_charge', $orderData);
                    
                }

            }

        }

    }

    #[Route('/order', name: 'app_order')]
    public function index(): Response
    {
        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }
}
