<?php

namespace App\Controller;

use App\Entity\Sale;
use App\Entity\Product;
use App\Entity\SaleComponent;
use App\Repository\PriceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Ramsey\Uuid\Uuid;

class SaleController extends AbstractController
{

    #[Route('/sale/create', name: 'app_sale_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, PriceRepository $priceRepository): Response
    {

        $cart = json_decode(base64_decode($request->cookies->get('cart')), true);
        $paymentMethod = $request->get('paymentMethod');

        if(isset($cart['items']) && !empty($cart['items'])) {

            $guid = $uuid = Uuid::uuid4();

            $sale = new Sale();
            $sale->setUser($this->getUser());
            $sale->setDate(new \DateTime());
            $sale->setRef($guid);
            $sale->setFirstName($this->getUser()->getFirstName());
            $sale->setLastName($this->getUser()->getLastName());
            $sale->setCity($this->getUser()->getCity());
            $sale->setStreet($this->getUser()->getStreet());
            $sale->setPostalCode($this->getUser()->getPostalCode());
            $sale->setRegion($this->getUser()->getRegion());
            $sale->setCountry($this->getUser()->getCountry());
            $sale->setPhone($this->getUser()->getPhone());
            $sale->setEmail($this->getUser()->getEmail());

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

                    $saleComponent = new SaleComponent();
                    
                    $saleComponent->setUser($this->getUser());
                    $saleComponent->setParent($sale);
                    $saleComponent->setProduct($product);
                    $saleComponent->setNetPrice($price->getPrice());
                    $saleComponent->setPrice(number_format($price->getPrice() + $price->getPrice() * $tax->getRate(), 2, '.', ''));
                    $saleComponent->setQuantity($itemQuantity);
                    $saleComponent->setTaxRate($tax->getRate());
                    $saleComponent->setTaxName($tax->getName());
                    $saleComponent->setNetAmount(number_format($saleComponent->getNetPrice() * $saleComponent->getQuantity(), 2, '.', ''));
                    $saleComponent->setAmount(number_format($saleComponent->getPrice() * $saleComponent->getQuantity(), 2, '.', ''));
                    $saleComponent->setTaxAmount(number_format($saleComponent->getAmount() - $saleComponent->getNetPrice(), 2, '.', ''));
                    $saleComponent->setName($product->getName());
                    $saleComponent->setSku($product->getSku());
                    $saleComponent->setEan($product->getEan());
                    $saleComponent->setGtin($product->getGtin());
                    $saleComponent->setIsbn($product->getIsbn());

                    $entityManager->persist($saleComponent);

                    $netAmount += $saleComponent->getNetAmount();
                    $taxAmount += $saleComponent->getTaxAmount();
                    $amount += $saleComponent->getAmount();
                    $quantity += $saleComponent->getQuantity();

                }
                else {

                    $errors[$id] = 'stock_exceeded';

                }
                
            }

            $saleDate = [
                'firstName' => $sale->getFirstName(),
                'lastName' => $sale->getLastName(),
                'city' => $sale->getCity(),
                'street' => $sale->getStreet(),
                'postalCode' => $sale->getPostalCode(),
                'region' => $sale->getRegion(),
                'country' => $sale->getCountry(),
                'phone' => $sale->getPhone(),
                'email' => $sale->getEmail(),
                'amount' => $amount,
                'ref' => $sale->getRef()
            ];

            if($paymentMethod == "stripe") {

                $saleData['stripeToken'] = $request->get('stripeToken');
            
            }

            if(empty($errors)) {

                $sale->setNetAmount($netAmount);
                $sale->setTaxAmount($taxAmount);
                $sale->setAmount($amount);
                $sale->setStatus('pending');
                $sale->setQuantity($quantity);

                $cart['sale_ref'] = $sale->getRef();

                $response = new Response();
                $response->headers->setCookie(Cookie::create('cart', base64_encode(json_encode($cart))));
                
                $entityManager->persist($sale);
                $entityManager->flush();

                if($paymentMethod == "stripe") {
                    
                    return $this->redirectToRoute('app_stripe_create_charge', $saleData);
                    
                }

            }

        }

    }

    #[Route('/sale', name: 'app_sale')]
    public function index(): Response
    {
        return $this->render('sale/index.html.twig', [
            'controller_name' => 'saleController',
        ]);
    }
}
