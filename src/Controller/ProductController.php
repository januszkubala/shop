<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Price;
use App\Entity\Tax;
use App\Entity\Stock;
use App\Entity\Property;
use App\Entity\PropertyValue;
use App\Repository\PriceRepository;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ProductController extends AbstractController
{

    #[Route('/product/create', name: 'app_product_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        $taxes = $entityManager->getRepository(Tax::class)->findAll();
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

            $entityManager->persist($price);
            $entityManager->persist($stock);
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product', ['id' => $product->getId()]);

        }

        return $this->render('product/create.html.twig', [
            'productForm' => $form->createView(),
            'taxes' => $taxes
        ]);

    }

    #[Route('/product/search', name: 'app_product_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

        $query = $request->query->get('query');

        $products = $entityManager->getRepository(Product::class)->findByKeywords($query);

        foreach($products as $product) {
            
            //$product->getCategory();
            //return new JsonResponse($serializer->serialize($product, 'json'));

        }

        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return null;
            }
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], [$encoder]);

        return new JsonResponse(json_decode($serializer->serialize($products, 'json', ['ignored_attributes' => ['user']])));
        

    }

    #[Route('/product', name: 'app_product')]
    public function index(Request $request, EntityManagerInterface $entityManager, PriceRepository $priceRepository): Response
    {

        $id = $request->query->get('id');

        $product = $entityManager->getRepository(Product::class)->findOneBy(['id' => $id]);
        $repository = $entityManager->getRepository(Category::class);

        $properties = [];
        foreach($product->getPropertyValues()->toArray() as $propertyValue) {
            $property = $entityManager->getRepository(Property::class)->findOneBy(['id' => $propertyValue->getProperty()->getId()]);
        }

        $category = $entityManager->getRepository(Category::class)->findOneBy(['id' => $product->getCategory()]);
        $path = $repository->getPath($category);
        $price = $priceRepository->findCurrentPrice($product);

        foreach($product->getFiles() as $file) {
            $host = null;
            switch($file->getSource()) {
                case 'local_cdn':
                default: {
                    // THIS HAS BE CHANGED AND THIS DIRECTORY SET M<UST BE TAKEN FROM services.yaml !!!!!
                    $host = '../cdn/';
                } break;
            }

            $file->setUrl($host . $file->getFileName() . '.' . $file->getExtension());
            switch($file->getMimeType()) {
                case 'image/jpeg':
                case 'image/png':
                case 'image/gif': {
                    $file->setSquareThumbnailUrl($host . $file->getFileName() . '_thumb.' . $file->getExtension());
                    $file->setFixedHeightThumbnailUrl($host . $file->getFileName() . '_thumb_h.' . $file->getExtension());

                } break;
            }
        }

        return $this->render('product/index.html.twig', [
            'product' => $product,
            'price' => $price,
            'path' => $path
        ]);

    }

}