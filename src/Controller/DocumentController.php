<?php

namespace App\Controller;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Repository\OrderRepository;
use App\Repository\OrderComponentRepository;
use App\Repository\ProductRepository;
use App\Repository\PriceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class DocumentController extends AbstractController
{

    private $configuration;

    public function __construct(ParameterBagInterface $parameters)
    {
        $configuration = $parameters->all();
    }

    #[Route('/document/order-sheet', name: 'app_document_order_sheet')]
    public function orderSheetPDF(Request $request, OrderRepository $orderRepository, OrderComponentRepository $orderComponentRepository): Response
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

    #[Route('/document/orders-spreadsheet', name: 'app_document_orders_spreadsheet')]
    public function ordersSpreadsheetXLSX(Request $request, OrderRepository $orderRepository, TranslatorInterface $translator): Response
    {

        if($request->query->get('file') == true) {

            $filters = [
                'query' => null,
                'grossValueBetween' => null,
                'grossValueAnd' => null,
                'dateBetween' => null,
                'dateAnd' => null
            ];

            if(!empty($request->cookies->get('filters_orders'))) {

                $filters = json_decode(base64_decode($request->cookies->get('filters_orders')), true);

                if($filters['dateBetween'] != null) {
                    $filters['dateBetween'] = new \DateTime($filters['dateBetween']['date']);
                }
                
                if($filters['dateAnd'] != null) {
                    $filters['dateAnd'] = new \DateTime($filters['dateAnd']['date']);
                }

            }

            
            if($filters['query'] != null || $filters['grossValueBetween'] != null || $filters['grossValueAnd'] != null || $filters['dateBetween'] != null || $filters['dateAnd'] != null) {

                $orders = $orderRepository->findFilteredOrders($filters);
            
            }
            else {

                $orders = $orderRepository->findAllOrders();

            }

            $headers = [
                $translator->trans('Order ID'),
                $translator->trans('Customer name'),
                $translator->trans('Items ordered'),
                $translator->trans('Net value'),
                $translator->trans('Tax value'),
                $translator->trans('Gross value'),
                $translator->trans('Date'),
                $translator->trans('Status')
            ];
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $styleArray = [
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => [
                            'rgb' => '000000'
                        ]
                    ]
                ],
                'font' => [
                    'bold' => true,
                    'color' => [
                        'rgb' => 'FFFFFF'
                    ]
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => '198754'
                    ]
                ],
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ]
            ];

            $sheet
                ->getRowDimension(1)
                ->setRowHeight(26)
            ;

            $sheet
                ->getStyle('A1:H1')
                ->applyFromArray($styleArray)
            ;
            
            foreach($sheet->getColumnIterator() as $column) {
                $sheet
                    ->getColumnDimension($column->getColumnIndex())
                    ->setAutoSize(true)
                ;
            }

            $sheet->fromArray($headers, null, 'A1');
            
            $row = 2;

            foreach($orders as $order) {

                $sheet
                    ->getStyle('D' . $row, 'F' . $row)
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1)
                ;

                $sheet
                    ->getStyle('G' . $row)
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY)
                ;

                $sheet->fromArray([
                    $order->getId(),
                    join(' ', [$order->getFirstName(), $order->getLastName()]),
                    $order->getQuantity(),
                    $order->getNetAmount(),
                    $order->getTaxAmount(),
                    $order->getAmount(),
                    $order->getDate(),
                    $order->getStatus()
                ],
                null,
                'A' . $row);

                $sheet->setCellValue('G' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($order->getDate()));

                switch($order->getStatus()) {

                    case 'pending': {
                        $sheet->setCellValue('H' . $row, $translator->trans('Pending / awaiting payment'));
                    }
                    break;

                    case 'paid': {
                        $sheet->setCellValue('H' . $row, $translator->trans('Paid'));
                    }
                    break;

                    case 'ready_to_ship': {
                        $sheet->setCellValue('H' . $row, $translator->trans('Ready to ship'));
                    }
                    break;

                    case 'delivered': {
                        $sheet->setCellValue('H' . $row, $translator->trans('Delivered'));
                    }
                    break;

                    case 'cancelled': {
                        $sheet->setCellValue('H' . $row, $translator->trans('Cancelled'));
                    }
                    break;

                    case 'disputed': {
                        $sheet->setCellValue('H' . $row, $translator->trans('Disputed'));
                    }
                    break;

                    case 'refunded': {
                        $sheet->setCellValue('H' . $row, $translator->trans('Refunded'));
                    }
                    break;

                }

                $row++;

            }

            $writer = new Xlsx($spreadsheet);

            $response =  new StreamedResponse(
                function () use ($writer) {
                    $writer->save('php://output');
                }
            );
            
            $response->headers->set('Content-Type', 'application/vnd.ms-excel');
            $response->headers->set('Content-Disposition', 'attachment;filename="Orders.xlsx"');
            $response->headers->set('Cache-Control','max-age=0');

            return $response;

        }
        else {

            return $this->render('download/index.html.twig', [
                'configuration' => $this->configuration
            ]);

        }


        
    }

    #[Route('/document/prices-spreadsheet/{id}', name: 'app_document_prices_spreadsheet')]
    public function pricesSpreadsheetXLSX(Request $request, PriceRepository $priceRepository, ProductRepository $ProductRepository, TranslatorInterface $translator): Response
    {
        
        if($request->query->get('file') == true) {

            $product = $ProductRepository->findOneBy(['id' => $request->get('id')]);
            $prices = $priceRepository->findBy(['product' => $request->get('id')]);

            $headers = [
                $translator->trans('Price ID'),
                $translator->trans('Date entered'),
                $translator->trans('Net price'),
                $translator->trans('Net price before'),
                $translator->trans('Tax rate'),
                $translator->trans('Gross price'),
                $translator->trans('Gross price before'),
                $translator->trans('Valid from'),
                $translator->trans('Valid to'),
                $translator->trans('Description')
            ];
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet
                ->getStyle('A1')
                ->getFont()
                ->setBold(true)
                ->setSize(16);
            
            $sheet
                ->getStyle('A')
                ->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            $sheet
                ->getRowDimension(1)
                ->setRowHeight(30);

            $sheet
                ->setCellValue('A1', $product->getName())
                ->mergeCells('A1:J1');

            $styleArray = [
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => [
                            'rgb' => '000000'
                        ]
                    ]
                ],
                'font' => [
                    'bold' => true,
                    'color' => [
                        'rgb' => 'FFFFFF'
                    ]
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => '198754'
                    ]
                ],
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ]
            ];

            $sheet
                ->getRowDimension(3)
                ->setRowHeight(26)
            ;

            $sheet
                ->getStyle('A3:J3')
                ->applyFromArray($styleArray)
            ;
            
            foreach($sheet->getColumnIterator() as $column) {

                $sheet
                    ->getColumnDimension($column->getColumnIndex())
                    ->setAutoSize(true)
                ;
            }

            $sheet->fromArray($headers, null, 'A3');
            
            $row = 4;

            foreach($prices as $price) {

                $sheet
                    ->getStyle('C' . $row . ':G' . $row)
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1)
                ;

                $sheet
                    ->getStyle('B' . $row)
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY)
                ;

                $sheet
                    ->getStyle('H' . $row, 'I' . $row)
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME)
                ;

                $sheet->fromArray([
                    $price->getId(),
                    $price->getDate(),
                    $price->getPrice(),
                    $price->getPreviousPrice(),
                    $price->getTax()->getRate() * 100,
                    ($price->getPrice() + $price->getPrice() * $price->getTax()->getRate()),
                    ($price->getPreviousPrice() + $price->getPreviousPrice() * $price->getTax()->getRate()),
                    $price->getDateValidFrom(),
                    $price->getDateValidTo(),
                    $price->getDescription()
                ],
                null,
                'A' . $row);

                $sheet->setCellValue('B' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($price->getDate()));

                $row++;

            }

            $writer = new Xlsx($spreadsheet);

            $response =  new StreamedResponse(
                function () use ($writer) {
                    $writer->save('php://output');
                }
            );
            
            $response->headers->set('Content-Type', 'application/vnd.ms-excel');
            $response->headers->set('Content-Disposition', 'attachment;filename="Prices ' . $product->getName() . '.xlsx"');
            $response->headers->set('Cache-Control','max-age=0');

            return $response;

        }
        else {

            return $this->render('download/index.html.twig', [
                'configuration' => $this->configuration
            ]);

        }


        
    }

}
