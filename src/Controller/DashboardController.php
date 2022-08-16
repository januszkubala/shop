<?php

namespace App\Controller;

use App\Entity\OrderComponent;
use App\Entity\Payment;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MenaraSolutions\Geographer\Earth;
use MenaraSolutions\Geographer\Country;

class DashboardController extends AbstractController
{

    
    #[Route('/dashboard/users', name: 'app_dashboard_users')]
    public function users_index(Request $request, UserRepository $userRepository): Response
    {

        $earth = new Earth();
        $countries = [];

        foreach($earth->getCountries()->toArray() as $country) {
            $countries[$country['code']] = $country['name'];
        }

        $users = $userRepository->findAllAndSortBy('id', 'DESC');

        return $this->render('dashboard/users/index.html.twig', [
            'users' => $users,
            'countries' => $countries
        ]);

    }

    #[Route('/dashboard/orders/order/{id}/payments', name: 'app_dashboard_orders_order_payments')]
    public function payment(Request $request, EntityManagerInterface $entityManager, OrderRepository $orderRepository): Response
    {


        $earth = new Earth();
        $countries = [];

        foreach($earth->getCountries()->toArray() as $country) {
            $countries[$country['code']] = $country['name'];
        }

        $id = $request->get('id');
        $order = $orderRepository->findOneBy(['id' => $id]);

        $payments = $entityManager->getRepository(Payment::class)->findBy(
            ['allocation' => $order->getId()],
            ['id' => 'DESC']
        );


        return $this->render('dashboard/orders/order/payments.html.twig', [
            'payments' => $payments,
            'order' => $order,
            'countries' => $countries
        ]);

    }

    #[Route('/dashboard/orders/order', name: 'app_dashboard_orders_order')]
    public function order(Request $request, EntityManagerInterface $entityManager, OrderRepository $orderRepository): Response
    {

        $earth = new Earth();
        $countries = [];

        foreach($earth->getCountries()->toArray() as $country) {
            $countries[$country['code']] = $country['name'];
        }

        $id = $request->query->get('id');
        $order = $orderRepository->findOneBy(['id' => $id]);

        $payments = $entityManager->getRepository(Payment::class)->findBy(
            ['allocation' => $order->getId()],
            ['id' => 'DESC']
        );

        $components = $entityManager->getRepository(OrderComponent::class)->findBy(['parent' => $order->getId()]);


        return $this->render('dashboard/orders/order/index.html.twig', [
            'order' => $order,
            'components' => $components,
            'payments' => $payments,
            'countries' => $countries
        ]);

    }

    #[Route('/dashboard/orders', name: 'app_dashboard_orders')]
    public function orders_index(Request $request, OrderRepository $orderRepository): Response
    {

        $orders = $orderRepository->findAllOrders();

        return $this->render('dashboard/orders/index.html.twig', [
            'orders' => $orders
        ]);

    }
}
