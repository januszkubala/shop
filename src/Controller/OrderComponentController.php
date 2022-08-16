<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderComponentController extends AbstractController
{
    #[Route('/order/component', name: 'app_order_component')]
    public function index(): Response
    {
        return $this->render('order_component/index.html.twig', [
            'controller_name' => 'OrderComponentController',
        ]);
    }
}
