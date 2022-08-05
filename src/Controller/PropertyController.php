<?php

namespace App\Controller;

use App\Entity\Property;
use App\Form\PropertyType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PropertyController extends AbstractController
{

    #[Route('/property/create', name: 'app_property_create')]
    public function create(EntityManagerInterface $entityManager, Request $request): Response
    {

        $property = new Property();
        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            
            $tmpUnits = $request->get('units');
            $units = [];
            
            foreach($tmpUnits['name'] as $index => $unitName) {
                
                if(trim($unitName)) {
                    
                    $unit = [];
                    $unit['name'] = $unitName;
                    if(is_numeric($tmpUnits['multiplier'][$index])) {
                        $unit['multiplier'] = $tmpUnits['multiplier'][$index];
                    }
                    else {
                        $unit['multiplier'] = 1;
                    }
                    $unit['default'] = false;
    
                    if($tmpUnits['default'] == $index) {
                        $unit['default'] = true;
                    }
                    $units[] = $unit;
                    
                }
                
            }
    
            if($units) {
                $property->setUnits($units);
            }


            $entityManager->persist($property);
            $entityManager->flush();
            
            return $this->redirectToRoute('app_property', ['id' => $property->getId()]);

        }

        return $this->render('property/create.html.twig', [
            'propertyForm' => $form->createView()
        ]);

    }

    #[Route('/property', name: 'app_property')]
    public function index(): Response
    {
        return $this->render('property/index.html.twig', [
            'controller_name' => 'PropertyController',
        ]);
    }
}
