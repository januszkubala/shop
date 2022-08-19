<?php

namespace App\Controller;
use App\Repository\ConfigurationRepository;

use App\Entity\OrderComponent;
use App\Entity\Payment;
use App\Entity\Category;
use App\Form\ProductType;
use App\Form\FilterOrdersFormType;
use App\Repository\PropertyRepository;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use App\Repository\PriceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MenaraSolutions\Geographer\Earth;
use MenaraSolutions\Geographer\Country;
use Symfony\Component\HttpFoundation\Cookie;

class DashboardController extends AbstractController
{

    private $configuration;

    public function __construct(ConfigurationRepository $configurationRepository) {
        
        $configuration = $configurationRepository->findOneBy(['is_current' => true]);

        $this->configuration = $configuration;

    }

    #[Route('/dashboard/files', name: 'app_dashboard_files')]
    public function filesIndex(Request $request, OrderRepository $orderRepository): Response
    {

        return $this->render('dashboard/files/index.html.twig', [
            'configuration' => $this->configuration
        ]);

    }

    #[Route('/dashboard/products/product/{id}/edit', name: 'app_dashboard_products_product_edit')]
    public function productEdit(Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository, PriceRepository $priceRepository, CategoryRepository $categoryRepository, PropertyRepository $propertyRepository): Response
    {

        $id = $request->get('id');

        $product = $productRepository->findOneWithEntitiesBy(['id' => $id]);

        if($product->getParent() != null) {
            $product->setParent($productRepository->findOneWithEntitiesBy(['id' => $product->getParent()->getId()]));
        }

        $repository = $entityManager->getRepository(Category::class);
        $category = $repository->findOneBy(['id' => $product->getCategory()->getId()]);

        $properties = [];
        $path = $repository->getPath($product->getCategory());
        foreach($path as $category) {
            foreach($category->getProperties()->toArray() as $property) {
                $properties[] = $property;
            }
        }


        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        //dd($form->createView());
        
        return $this->render('dashboard/products/product/edit/index.html.twig', [
            'configuration' => $this->configuration,
            'productForm' => $form->createView(),
            'product' => $product,
            'properties' => $properties,
            'path' => $path
        ]);

    }

    #[Route('/dashboard/products/product/{id}/prices', name: 'app_dashboard_products_product_prices')]
    public function productPrices(Request $request, ProductRepository $productRepository, PriceRepository $priceRepository): Response
    {

        $id = $request->get('id');

        $product = $productRepository->findOneWithEntitiesBy(['id' => $id]);
        $price = $priceRepository->findCurrentPrice($product);
        
        return $this->render('dashboard/products/product/prices/index.html.twig', [
            'configuration' => $this->configuration,
            'product' => $product,
            'price' => $price
        ]);

    }

    #[Route('/dashboard/products/product/{id}', name: 'app_dashboard_products_product')]
    public function product(Request $request, ProductRepository $productRepository, PriceRepository $priceRepository): Response
    {

        $id = $request->get('id');

        $product = $productRepository->findOneWithEntitiesBy(['id' => $id]);
        $price = $priceRepository->findCurrentPrice($product);
        
        //dd($price);
        //dd($this->configuration);
        
        return $this->render('dashboard/products/product/index.html.twig', [
            'configuration' => $this->configuration,
            'product' => $product,
            'price' => $price
        ]);

    }
    
    #[Route('/dashboard/users/user/{id}', name: 'app_dashboard_users_user')]
    public function user(Request $request, UserRepository $userRepository, OrderRepository $orderRepository): Response
    {

        $earth = new Earth();
        $countries = [];

        foreach($earth->getCountries()->toArray() as $country) {
            $countries[$country['code']] = $country['name'];
        }

        $id = $request->get('id');
        $user = $userRepository->findOneBy(['id' => $id]);
        $orders = $orderRepository->findBy(['user' => $user]);

        return $this->render('dashboard/users/user/index.html.twig', [
            'configuration' => $this->configuration,
            'user' => $user,
            'countries' => $countries,
            'orders' => $orders
        ]);

    }

    #[Route('/dashboard/users', name: 'app_dashboard_users')]
    public function usersIndex(Request $request, UserRepository $userRepository): Response
    {

        $earth = new Earth();
        $countries = [];

        foreach($earth->getCountries()->toArray() as $country) {
            $countries[$country['code']] = $country['name'];
        }

        $users = $userRepository->findAllAndSortBy('id', 'DESC');

        return $this->render('dashboard/users/index.html.twig', [
            'configuration' => $this->configuration,
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


        return $this->render('dashboard/orders/order/payments/index.html.twig', [
            'configuration' => $this->configuration,
            'payments' => $payments,
            'order' => $order,
            'countries' => $countries
        ]);

    }

    #[Route('/dashboard/orders/order/{id}', name: 'app_dashboard_orders_order')]
    public function order(Request $request, EntityManagerInterface $entityManager, OrderRepository $orderRepository): Response
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
        
        $components = $entityManager->getRepository(OrderComponent::class)->findBy(['parent' => $order->getId()]);


        return $this->render('dashboard/orders/order/index.html.twig', [
            'configuration' => $this->configuration,
            'order' => $order,
            'components' => $components,
            'payments' => $payments,
            'countries' => $countries
        ]);

    }

    #[Route('/dashboard/orders', name: 'app_dashboard_orders')]
    public function ordersIndex(Request $request, OrderRepository $orderRepository): Response
    {

        $response = new Response();

        $form = $this->createForm(FilterOrdersFormType::class, []);
        $form->handleRequest($request);

        $filters = [
            'query' => null,
            'grossValueBetween' => null,
            'grossValueAnd' => null,
            'dateBetween' => null,
            'dateAnd' => null
        ];

        if($form->isSubmitted() && $form->isValid()) {

            $filters['query'] = $form->get('query')->getData();
            $filters['grossValueBetween'] = (double) $form->get('grossValueBetween')->getData();
            $filters['grossValueAnd'] = (double) $form->get('grossValueAnd')->getData();


            if(!($filters['grossValueBetween'] >= 0)) {
                $filters['grossValueBetween'] = 0;
            }

            if(($filters['grossValueBetween'] > $filters['grossValueAnd'] && $filters['grossValueAnd'] != 0) || ($filters['grossValueBetween'] == 0 && $filters['grossValueAnd'] == 0)) {
                $filters['grossValueBetween'] = null;
                $filters['grossValueAnd'] = null;
            }

            if(!empty($form->get('dateBetween')->getData())) {
                $filters['dateBetween'] = $form->get('dateBetween')->getData();
            }

            if(!empty($form->get('dateAnd')->getData())) {
                $filters['dateAnd'] = $form->get('dateAnd')->getData();
            }
        
            if(isset($filters['dateBetween']) && isset($filters['dateAnd']) && $filters['dateBetween'] > $filters['dateAnd']) {
                $filters['dateBetween'] = null;
            }
            
            $response->headers->setCookie(Cookie::create('filters_orders', base64_encode(json_encode($filters))));

        }
        elseif($request->query->get('filters') == 'reset') {
            
            $response->headers->clearCookie('filters_orders', '/', null);

        }
        elseif($request->cookies->get('filters_orders')) {

            $filters = json_decode(base64_decode($request->cookies->get('filters_orders')), true);

            $form->get('query')->setData($filters['query']);

            if($filters['grossValueBetween'] > 0) {
                $form->get('grossValueBetween')->setData($filters['grossValueBetween']);
            }

            if($filters['grossValueAnd'] > 0) {
                $form->get('grossValueAnd')->setData($filters['grossValueAnd']);
            }

            if($filters['dateBetween'] != null) {
                $filters['dateBetween'] = new \DateTime($filters['dateBetween']['date']);
            }
            
            if($filters['dateAnd'] != null) {
                $filters['dateAnd'] = new \DateTime($filters['dateAnd']['date']);
            }

            $form->get('dateBetween')->setData($filters['dateBetween']);
            $form->get('dateAnd')->setData($filters['dateAnd']);
            
        }

        if($filters['query'] != null || $filters['grossValueBetween'] != null || $filters['grossValueAnd'] != null || $filters['dateBetween'] != null || $filters['dateAnd'] != null) {

            $orders = $orderRepository->findFilteredOrders($filters);
        
        }
        else {

            $orders = $orderRepository->findAllOrders();

        }

        $response->sendHeaders();
        
        return $this->render('dashboard/orders/index.html.twig', [
            'configuration' => $this->configuration,
            'orders' => $orders,
            'filtersForm' => $form->createView()
        ]);

    }

    #[Route('/dashboard/settings', name: 'app_dashboard_settings')]
    public function settingsIndex(Request $request, OrderRepository $orderRepository): Response
    {
        
        // We'll see what's gonna be in here xD

        return $this->render('dashboard/settings/index.html.twig', [
            'configuration' => $this->configuration
        ]);

    }

}
