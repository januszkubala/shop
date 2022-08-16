<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Price;
use App\Repository\PriceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Cookie;

class CartController extends AbstractController
{

    #[Route('/api/cart/put', name: 'api_cart_put', methods: ['PUT'])]
    public function put(Request $request, EntityManagerInterface $entityManager, PriceRepository $priceRepository): JsonResponse
    {

        $cart = json_decode(base64_decode($request->cookies->get('cart')), true);
        $id = (int) $request->get('id');
        $quantity = (int) $request->get('quantity');

        if(empty($cart)) {
            $cart = [];
            $cart['order_ref'] = null;
            $cart['items'] = [];
        }

        $cart['totals'] = [];
        $cart['totals']['quantity'] = 0;
        $cart['totals']['value'] = 0;

        if($id > 0 && $quantity > 0) {

            $product = $entityManager->getRepository(Product::class)->findOneBy(['id' => $id]);
            $price = $priceRepository->findCurrentPrice($product);

            if(isset($cart['items'][$id]) && is_array($cart['items'][$id])) {
                $quantity += $cart['items'][$id]['quantity'];
            }
            
            $cartItem = [];
            $cartItem['name'] = $product->getName();
            $cartItem['price_net'] = number_format($price->getPrice(), 2, '.', '');
            $cartItem['price'] = number_format($price->getPrice() + $price->getPrice() * $price->getTax()->getRate(), 2, '.', '');
            $cartItem['total_net'] = number_format($cartItem['price_net'] * $quantity, 2, '.', '');
            $cartItem['total'] = number_format($cartItem['price'] * $quantity, 2, '.', '');
            $cartItem['quantity'] = $quantity;

            $cart['items'][$id] = $cartItem;

        }

        if(!empty($cart['items'])) {
            foreach($cart['items'] as $cartItem) {
                $cart['totals']['quantity'] += $cartItem['quantity'];
                $cart['totals']['value'] += $cartItem['total'];
            }
            $cart['totals']['value'] = number_format($cart['totals']['value'], 2, '.', '');
        }

        $response = new JsonResponse();
        $response->headers->setCookie(Cookie::create('cart', base64_encode(json_encode($cart))));
        $response->setContent(json_encode(['success' => true]));

        return $response;

    }

    #[Route('/api/cart/delete', name: 'api_cart_delete', methods: ['DELETE'])]
    public function delete(Request $request): JsonResponse
    {

        $cart = json_decode(base64_decode($request->cookies->get('cart')), true);
        $id = (int) $request->get('id');

        if(isset($cart['items'][$id])) {
            unset($cart['items'][$id]);
        }

        $response = new JsonResponse();

        if(empty($cart['items'])) {

            $cart = [];
            $response->headers->clearCookie('cart', '/', null);

        }
        else {
            $cart['totals'] = [];
            $cart['totals']['quantity'] = 0;
            $cart['totals']['value'] = 0;

            foreach($cart['items'] as $cartItem) {
                $cart['totals']['quantity'] += $cartItem['quantity'];
                $cart['totals']['value'] += $cartItem['total'];
            }
            
            $cart['totals']['value'] = number_format($cart['totals']['value'], 2, '.', '');

            $response->headers->setCookie(Cookie::create('cart', base64_encode(json_encode($cart))));
        }

        $response->setContent(json_encode(['success' => true]));

        return $response;

    }

    #[Route('/api/cart/patch', name: 'api_cart_patch', methods: ['PATCH'])]
    public function patch(Request $request, EntityManagerInterface $entityManager, PriceRepository $priceRepository): JsonResponse
    {

        $cart = json_decode(base64_decode($request->cookies->get('cart')), true);
        $id = (int) $request->get('id');
        $quantity = (int) $request->get('quantity');

        if(isset($cart['items'][$id])) {

            $cart['items'][$id]['quantity'] += $quantity;

            if($cart['items'][$id]['quantity'] <= 0) {
                unset($cart['items'][$id]);
            }

        }

        $response = new JsonResponse();

        if(empty($cart['items'])) {

            $cart = [];
            $response->headers->clearCookie('cart', '/', null);

        }
        else {

            if(isset($cart['items'][$id])) {
                
                $product = $entityManager->getRepository(Product::class)->findOneBy(['id' => $id]);
                $price = $priceRepository->findCurrentPrice($product);

                $cart['items'][$id]['price_net'] = number_format($price->getPrice(), 2, '.', '');
                $cart['items'][$id]['price'] = number_format($price->getPrice() + $price->getPrice() * $price->getTax()->getRate(), 2, '.', '');
                $cart['items'][$id]['total_net'] = number_format($cart['items'][$id]['price_net'] * $cart['items'][$id]['quantity'], 2, '.', '');
                $cart['items'][$id]['total'] = number_format($cart['items'][$id]['price'] * $cart['items'][$id]['quantity'], 2, '.', '');
            
            }

            $cart['totals'] = [];
            $cart['totals']['quantity'] = 0;
            $cart['totals']['value'] = 0;

            foreach($cart['items'] as $cartItem) {
                $cart['totals']['quantity'] += $cartItem['quantity'];
                $cart['totals']['value'] += $cartItem['total'];
            }
            
            $cart['totals']['value'] = number_format($cart['totals']['value'], 2, '.', '');

            $response->headers->setCookie(Cookie::create('cart', base64_encode(json_encode($cart))));
        }

        $response->setContent(json_encode(['success' => true]));

        return $response;

    }

    #[Route('/api/cart/get', name: 'api_cart_get', methods: ['GET'])]
    public function get(Request $request, EntityManagerInterface $entityManager, PriceRepository $priceRepository): JsonResponse
    {

        $cart = base64_decode($request->cookies->get('cart'));

        return(new JsonResponse(json_decode($cart)));

    }

}
