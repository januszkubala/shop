<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SaleComponentController extends AbstractController
{
    #[Route('/sale/component', name: 'app_sale_component')]
    public function index(): Response
    {
        return $this->render('sale_component/index.html.twig', [
            'controller_name' => 'saleComponentController',
        ]);
    }
}
