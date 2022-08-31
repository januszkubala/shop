<?php

namespace App\Controller;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Entity\SaleComponent;
use App\Entity\Product;
use App\Entity\File;
use App\Entity\Price;
use App\Entity\Tax;
use App\Entity\Payment;
use App\Entity\Property;
use App\Entity\PropertyValue;
use App\Entity\Category;
use App\Entity\Stock;
use App\Entity\Configuration;
use App\Form\ProductType;
use App\Form\FilterSalesType;
use App\Form\FilterUsersType;
use App\Form\ConfigurationRegionalType;
use App\Form\UserRoleFormType;
use App\Form\StockType;
use App\Repository\PropertyRepository;
use App\Repository\CategoryRepository;
use App\Repository\SaleRepository;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use App\Repository\PriceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MenaraSolutions\Geographer\Earth;
use MenaraSolutions\Geographer\Country;
use Symfony\Component\HttpFoundation\Cookie;

class DashboardController extends AbstractController
{

    #[Route('/dashboard/files', name: 'app_dashboard_files')]
    public function filesIndex(Request $request, SaleRepository $saleRepository): Response
    {

        return $this->render('dashboard/files/index.html.twig', [
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
        
        return $this->render('dashboard/products/product/edit/index.html.twig', [
            'productForm' => $form->createView(),
            'product' => $product,
            'properties' => $properties,
            'path' => $path
        ]);

    }

    #[Route('/dashboard/products/product/{id}/stock/create', name: 'app_dashboard_products_product_stock_create')]
    public function productStockCreate(Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {

        $id = $request->get('id');
        $product = $productRepository->findOneWithEntitiesBy(['id' => $id]);

        $stock = new Stock();
        
        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            
            $product->setStock($product->getStock() + $stock->getStockChange());

            $stock->setUser($this->getUser());
            $stock->setProduct($product);
            $stock->setDate(new \DateTime);

            $entityManager->persist($stock);
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirect('/dashboard/products/product/' . $stock->getId() . '/stock');

        }

        return $this->render('dashboard/products/product/stock/create.html.twig', [
            'product' => $product,
            'stockForm' => $form->createView()
        ]);

    }

    #[Route('/dashboard/products/product/{id}/stock', name: 'app_dashboard_products_product_stock')]
    public function productStock(Request $request, ProductRepository $productRepository): Response
    {

        $id = $request->get('id');
        $product = $productRepository->findOneWithEntitiesBy(['id' => $id]);

        return $this->render('dashboard/products/product/stock/index.html.twig', [
            'product' => $product
        ]);

    }

    #[Route('/dashboard/products/product/{id}/prices', name: 'app_dashboard_products_product_prices')]
    public function productPrices(Request $request, ProductRepository $productRepository, PriceRepository $priceRepository): Response
    {

        $id = $request->get('id');

        $product = $productRepository->findOneWithEntitiesBy(['id' => $id]);
        $price = $priceRepository->findCurrentPrice($product);

        return $this->render('dashboard/products/product/prices/index.html.twig', [
            'product' => $product,
            'price' => $price
        ]);

    }

    #[Route('/dashboard/products/product/{id}', name: 'app_dashboard_products_product')]
    public function product(Request $request, ProductRepository $productRepository, PriceRepository $priceRepository, SaleRepository $saleRepository): Response
    {

        $id = $request->get('id');

        $product = $productRepository->findOneWithEntitiesBy(['id' => $id]);
        $price = $priceRepository->findCurrentPrice($product);

        // Get last n paid sales with this product
        $sales = $saleRepository->findLastSalesForProduct($product, 10);

        dd($sales);

        return $this->render('dashboard/products/product/index.html.twig', [
            'product' => $product,
            'price' => $price,
            'salers' => $sales
        ]);

    }

    #[Route('/dashboard/products/create', name: 'app_dashboard_products_create')]
    public function productCreate(Request $request, EntityManagerInterface $entityManager): Response
    {

        $product = new Product();
        $properties = [];

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $product->setUser($this->getUser());
            
            if($request->get('product')['assignment'] == 'parent') {
                $parent = $entityManager->getRepository(Product::class)->findOneBy(['id' => $request->get('product')['parent']]);
                $category = $entityManager->getRepository(Category::class)->findOneBy(['id' => $parent->getCategory()]);
                $product->setParent($parent);
                $product->setCategory($category);
            }
            else {
                $category = $entityManager->getRepository(Category::class)->findOneBy(['id' => $request->get('product')['category']]);
                $product->setCategory($category);
            }

            if($request->get('property')) {
                foreach($request->get('property') as $propertyId => $propertyValue) {
                    if($propertyValue['value']) {
                        
                        $property = $entityManager->getRepository(Property::class)->findOneBy(['id' => $propertyId]);
                        
                        $value = new PropertyValue();

                        switch($property->getType()) {

                            case 'text': {
                                $value->setText($propertyValue['value']);
                            }
                            break;

                            case 'integer': {
                                $value->setNumber($propertyValue['value']);
                            }
                            break;

                            case 'decimal': {
                                $value->setFloatingPointNumber($propertyValue['value']);
                            }
                            break;

                        }

                        if(isset($propertyValue['unit'])) {
                            $value->setUnit($propertyValue['unit']);
                        }

                        $value->setProperty($property);
                        $product->addPropertyValue($value);
                        $entityManager->persist($value);
                    }
                }
            }

            $additionalProperties = [];
            
            if($request->get('additional_property')) {
                foreach($request->get('additional_property') as $additionalProperty) {
                    if($additionalProperty['value']) {
                        $additionalProperties[] = $additionalProperty;
                    }
                }
            }

            $date = new \DateTime();

            $product->setAdditionalProperties($additionalProperties);
            $product->setDate($date);

            $stock = new Stock();
            $stock->setUser($this->getUser());
            $stock->setProduct($product);
            $stock->setDate($date);
            $stock->setStockChange($product->getStock());
            $stock->setChangesDescription('Initial stock level');

            $price = new Price();
            $price->setUser($this->getUser());
            

            $priceValue = 0;
            
            if(is_numeric($request->get('product')['price']) && $request->get('product')['price'] > 0) {
                $priceValue = $request->get('product')['price'];
            }

            $tax = $entityManager->getRepository(Tax::class)->findOneById(['tax' => $request->get('product')['tax']]);

            $price->setPrice($priceValue);
            $price->setProduct($product);
            $price->setDate($date);
            $price->setTax($tax);

            if($request->get('files')) {

                foreach($request->get('files') as $fileId) {
                    
                    $file = $entityManager->getRepository(File::class)->findOneById(['id' => $fileId]);
                    $product->addFile($file);

                }

            }
            
            $entityManager->persist($price);
            $entityManager->persist($stock);
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('/dashboard/products/product/' . $product->getId());

        }
        
        return $this->render('dashboard/products/product/edit/index.html.twig', [
            'productForm' => $form->createView(),
            'product' => $product,
            'properties' => $properties
        ]);

    }
    
    #[Route('/dashboard/users/user/{id}', name: 'app_dashboard_users_user')]
    public function user(Request $request, UserRepository $userRepository, SaleRepository $saleRepository): Response
    {

        $earth = new Earth();
        $countries = [];

        foreach($earth->getCountries()->toArray() as $country) {
            $countries[$country['code']] = $country['name'];
        }

        $id = $request->get('id');
        $user = $userRepository->findOneBy(['id' => $id]);
        $sales = $saleRepository->findBy(['user' => $user]);

        return $this->render('dashboard/users/user/index.html.twig', [
            'user' => $user,
            'countries' => $countries,
            'sales' => $sales
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
            'users' => $users,
            'countries' => $countries
        ]);

    }

    #[Route('/dashboard/sales/sale/{id}/payments', name: 'app_dashboard_sales_sale_payments')]
    public function payment(Request $request, EntityManagerInterface $entityManager, SalesRepository $saleRepository): Response
    {


        $earth = new Earth();
        $countries = [];

        foreach($earth->getCountries()->toArray() as $country) {
            $countries[$country['code']] = $country['name'];
        }

        $id = $request->get('id');
        $sale = $saleRepository->findOneBy(['id' => $id]);

        $payments = $entityManager->getRepository(Payment::class)->findBy(
            ['allocation' => $sale->getId()],
            ['id' => 'DESC']
        );


        return $this->render('dashboard/sales/sale/payments/index.html.twig', [
            'payments' => $payments,
            'sale' => $sale,
            'countries' => $countries
        ]);

    }

    #[Route('/dashboard/sales/sale/{id}', name: 'app_dashboard_sales_sale')]
    public function sale(Request $request, EntityManagerInterface $entityManager, SalesRepository $saleRepository): Response
    {

        $earth = new Earth();
        $countries = [];

        foreach($earth->getCountries()->toArray() as $country) {
            $countries[$country['code']] = $country['name'];
        }

        $id = $request->get('id');
        $sale = $saleRepository->findOneBy(['id' => $id]);

        $payments = $entityManager->getRepository(Payment::class)->findBy(
            ['allocation' => $sale->getId()],
            ['id' => 'DESC']
        );
        
        $components = $entityManager->getRepository(SaleComponent::class)->findBy(['parent' => $sale->getId()]);


        return $this->render('dashboard/sales/sale/index.html.twig', [
            'sale' => $sale,
            'components' => $components,
            'payments' => $payments,
            'countries' => $countries
        ]);

    }

    #[Route('/dashboard/sales', name: 'app_dashboard_sales')]
    public function salesIndex(Request $request, SaleRepository $saleRepository): Response
    {

        $response = new Response();

        $form = $this->createForm(FilterSalesType::class, []);
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
            
            $response->headers->setCookie(Cookie::create('filters_sales', base64_encode(json_encode($filters))));

        }
        elseif($request->query->get('filters') == 'reset') {
            
            $response->headers->clearCookie('filters_sales', '/', null);

        }
        elseif($request->cookies->get('filters_sales')) {

            $filters = json_decode(base64_decode($request->cookies->get('filters_sales')), true);

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

            $sales = $saleRepository->findFilteredSales($filters);
        
        }
        else {

            $sales = $saleRepository->findAllSales();

        }

        $response->sendHeaders();

        return $this->render('dashboard/sales/index.html.twig', [
            'sales' => $sales,
            'filtersForm' => $form->createView()
        ]);

    }

    #[Route('/dashboard/settings', name: 'app_dashboard_settings')]
    public function settingsIndex(Request $request): Response
    {
        
        // We'll see what's gonna be in here xD

        return $this->render('dashboard/settings/index.html.twig', [
        ]);

    }

    #[Route('/dashboard/configuration/access/{id}', name: 'app_dashboard_configuration_access_user')]
    public function configurationAccessUser(Request $request, RoleHierarchyInterface $roleHierarchy, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $earth = new Earth();
        $countries = [];

        foreach($earth->getCountries()->toArray() as $country) {
            $countries[$country['code']] = $country['name'];
        }

        $id = $request->get('id');
        $user = $userRepository->findOneBy(['id' => $id]);
        
        dd($this->isGranted($this->getUser(), 'ROLE_ADMIN'));

        $roles = [
            'ROLE_USER' => $translator->trans('Registered user'),
            'ROLE_TRAINEE' => $translator->trans('Trainee'),
            'ROLE_FLOOR_STAFF' => $translator->trans('Floor staff'),
            'ROLE_CUSTOMER_CARE' => $translator->trans('Customer care'),
            'ROLE_STOCK_KEEPER' => $translator->trans('Stock-keeper'),
            'ROLE_ACCOUNTANT' => $translator->trans('Accountant'),
            'ROLE_MANAGER' => $translator->trans('Manager'),
            'ROLE_ADMIN' => $translator->trans('Administrator'),
            'ROLE_SUPER_ADMIN' => $translator->trans('Super administrator')
        ];

        $form = $this->createForm(UserRoleFormType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $userId = $form->get('user')->getData();
            $role = $form->get('role')->getData();
            
            if($userId != $id) {
                throw new \Exception('User mismatch.');
            }

            if($role == 'ROLE_SUPER_ADMIN') {
                throw new \Exception('Forbidden role ROLE_SUPER_ADMIN.');
            }
            
            if(!in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())) {
                if($role == 'ROLE_ADMIN') {
                    throw new \Exception('Invalid or forbidden role ROLE_ADMIN.');
                }
            }
            elseif(!in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
                if($role == 'ROLE_MANAGER') {
                    throw new \Exception('Invalid or forbidden role ROLE_MANAGER.');
                }
            }
            elseif(!in_array('ROLE_MANAGER', $this->getUser()->getRoles())) {
                throw new \Exception('Invalid role.');
            }



        }



        return $this->render('dashboard/configuration/access/user/index.html.twig', [
            'roleForm' => $form->createView(),
            'user' => $user,
            'countries' => $countries,
            'roles' => $roles
        ]);

    }

    #[Route('/dashboard/configuration/access', name: 'app_dashboard_configuration_access')]
    public function configurationAccessIndex(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {

        $response = new Response();

        $form = $this->createForm(FilterUsersType::class, []);
        $form->handleRequest($request);

        $filters = [
            'query' => null,
            'email' => null,
            'role' => null
        ];

        if($form->isSubmitted() && $form->isValid()) {

            $filters['query'] = $form->get('query')->getData();
            $filters['email'] = $form->get('email')->getData();
            $filters['role'] = $form->get('role')->getData();
            
            $response->headers->setCookie(Cookie::create('filters_access', base64_encode(json_encode($filters))));

        }
        elseif($request->query->get('filters') == 'reset') {
            
            $response->headers->clearCookie('filters_access', '/', null);

        }
        elseif($request->cookies->get('filters_access')) {

            $filters = json_decode(base64_decode($request->cookies->get('filters_sales')), true);
            
            if(isset($filters['query'])) {

                $form->get('query')->setData($filters['query']);

            }

            if(isset($filters['email'])) {

                $form->get('email')->setData($filters['email']);

            }

            if(isset($filters['role'])) {

                $form->get('role')->setData($filters['role']);

            }
            
        }

        if(isset($filters['query']) && $filters['query'] != null || isset($filters['email']) && $filters['email'] != null || isset($filters['role']) && $filters['role'] != null) {

            $users = $userRepository->findFilteredUsers($filters);
        
        }
        else {

            $users = $userRepository->findAll();

        }

        $roles = [
            'ROLE_USER' => $translator->trans('Registered user'),
            'ROLE_TRAINEE' => $translator->trans('Trainee'),
            'ROLE_FLOOR_STAFF' => $translator->trans('Floor staff'),
            'ROLE_CUSTOMER_CARE' => $translator->trans('Customer care'),
            'ROLE_STOCK_KEEPER' => $translator->trans('Stock-keeper'),
            'ROLE_ACCOUNTANT' => $translator->trans('Accountant'),
            'ROLE_MANAGER' => $translator->trans('Manager'),
            'ROLE_ADMIN' => $translator->trans('Administrator'),
            'ROLE_SUPER_ADMIN' => $translator->trans('Super administrator')
        ];

        $response->sendHeaders();

        return $this->render('dashboard/configuration/access/index.html.twig', [
            'filtersForm' => $form->createView(),
            'users' => $users,
            'roles' => $roles
        ]);

    }

    #[Route('/dashboard/configuration/regional', name: 'app_dashboard_configuration_regional')]
    public function configurationGeneralIndex(Request $request, EntityManagerInterface $entityManager, ParameterBagInterface $parameters): Response
    {

        $configuration = new Configuration();
        $configuration->setTimezone($parameters->get('timezone'));
        $configuration->setLocale($parameters->get('locale'));
        $configuration->setCurrency($parameters->get('currency'));

        $form = $this->createForm(ConfigurationRegionalType::class, $configuration);
        $form->handleRequest($request);

        $saved = false;

        if($form->isSubmitted() && $form->isValid()) {

            $saved = true;

            // Set new configuration
            $yaml = [
                'parameters' => [
                    'timezone' => $form->get('timezone')->getData(),
                    'locale' => $form->get('locale')->getData(),
                    'currency' => $form->get('currency')->getData()
                ]
            ];

            // Save to regional.yaml
            file_put_contents('../config/regional.yaml', Yaml::dump($yaml));
            
        }

        return $this->render('dashboard/configuration/regional/index.html.twig', [
            'configurationForm' => $form->createView(),
            'saved' => $saved
        ]);

    }

    #[Route('/dashboard/configuration', name: 'app_dashboard_configuration')]
    public function configurationIndex(Request $request): Response
    {

        return $this->render('dashboard/configuration/index.html.twig', [
        ]);

    }

}
