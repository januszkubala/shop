<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class CategoryController extends AbstractController
{

    #[Route('/category/pick', name: 'app_category_pick', methods: ['GET'])]
    public function pick(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): JsonResponse
    {

        $parent = $request->query->get('parent');

        if(!$parent) {
            $parent = null;
        }

        $categories = $categoryRepository->findBy(['parent' => $parent]);

        $json = [];

        foreach($categories as $category) {
            $json[$category->getName()] = $category->getId();
        }

        return new JsonResponse($json);

    }

    #[Route('/category/path', name: 'app_category_path', methods: ['GET'])]
    public function path(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $id = $request->query->get('id');
        $repository = $entityManager->getRepository(Category::class);
        $category = $repository->findOneById($id);

        if(!$category) {
            return new JsonResponse([]);
        }

        $path = $repository->getPath($category);
        $json = [];
        foreach($path as $category) {
            $json[$category->getId()] = $category->getName();
        }

        return new JsonResponse($json);

    }

    #[Route('/category/properties', name: 'app_category_properties', methods: ['GET'])]
    public function properties(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository, PropertyRepository $propertyRepository): JsonResponse
    {
        $id = (int) $request->query->get('id');
        $includeParents = (bool) $request->query->get('ip');
        $fill = (int) $request->query->get('fill');


        $repository = $entityManager->getRepository(Category::class);
        $category = $repository->findOneById($id);

        $properties = [];
        if($includeParents) {
            $path = $repository->getPath($category);
            foreach($path as $category) {
                foreach($category->getProperties()->toArray() as $property) {
                    $properties[] = $property;
                }
            }
        }
        else {
            $properties = $category->getProperties()->toArray();
        }


        if($fill) {
            
            $prefilledProperties = [];
            $prefilledAdditionalProperties = [];

            $product = $entityManager->getRepository(Product::class)->findOneById($fill);

            $predefinedProperties = $product->getPropertyValues()->toArray();
            
            if($predefinedProperties) {
                foreach($product->getPropertyValues()->toArray() as $property) {
                    $prefilledProperties[$property->getProperty()->getId()] = $property;
                }
            }

            $additionalProperties = $product->getAdditionalProperties();
            
            if($additionalProperties) {
                foreach($product->getAdditionalProperties() as $additionalProperty) {
                    $prefilledAdditionalProperties[] = $additionalProperty;
                }
            }

            $output = [];
            $output["properties"] = $properties;
            $output['prefilledProperties'] = $prefilledProperties;
            $output['prefilledAdditionalProperties'] = $prefilledAdditionalProperties;
            $properties = $output;

        }

        //dd($properties);

        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return null;
            }
        ];

        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], [$encoder]);

        return new JsonResponse(json_decode($serializer->serialize($properties, 'json', ['ignored_attributes' => ['parent', 'children', 'products', 'category', 'user']])));

    }

    #[Route('/category/create', name: 'app_category_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        $parents = $categoryRepository->findBy(['parent' => null]);
        $parentChoices = [null => '-- current category --'];

        foreach($parents as $parent) {
            $parentChoices[$parent->getId()] = $parent->getName();
        }
        
        if($form->isSubmitted() && $form->isValid()) {
            

            $parent = $categoryRepository->findOneBy(['id' => $request->get('category')['parent']]);
            $category->setParent($parent);

            $entityManager->persist($category);
            $entityManager->flush();

            //return $this->redirectToRoute('app_product', ['id' => $product->getId()]);

        }

        return $this->render('category/create.html.twig', [
            'parentChoices' => $parentChoices,
            'categoryForm' => $form->createView()
        ]);

    }

    #[Route('/category', name: 'app_category')]
    public function index(EntityManagerInterface $entityManager): Response
    {

        $food = new Category();
        $food->setName('Food');
        
        $fruits = new Category();
        $fruits->setName('Fruits');
        $fruits->setParent($food);
        
        $vegetables = new Category();
        $vegetables->setName('Vegetables');
        $vegetables->setParent($food);
        
        $carrots = new Category();
        $carrots->setName('Carrots');
        $carrots->setParent($vegetables);

        $entityManager->persist($food);
        $entityManager->persist($fruits);
        $entityManager->persist($vegetables);
        $entityManager->persist($carrots);
        /*
        $entityManager->flush();
        */

        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

}