<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\OrderComponentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;

class DocumentController extends AbstractController
{
    #[Route('/document/order-sheet', name: 'app_document_order_sheet')]
    public function index(Request $request, OrderRepository $orderRepository, OrderComponentRepository $orderComponentRepository): Response
    {

        $order = $orderRepository->findOneBy(['id' => (int) $request->query->get('id')]);
        $items = [];
        $items = $orderComponentRepository->findBy(['parent' => $order]);

        $html = $this->render('@templates/order_sheet.html.twig', [
            'order' => $order,
            'items' => $items
        ]);
        
        $dompdf = new Dompdf();
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();
        
        
        return new Response(
            $dompdf->stream('OS-' . $order->getId() . '.pdf', ['Attachment' => false]),
            200,
            array(
                'Content-Type' => 'application/pdf'
            )
        );
        
    }
}
