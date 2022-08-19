<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Tax;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('price', TextType::class, [
                'mapped' => false,
                'help' => 'Price must be 0.00 or higher, otherwise the product will be hidden.'
            ])
            ->add('tax', EntityType::class, [
                'class' => Tax::class,
                'mapped' => false,
                'query_builder' => function (EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('t');
                },
                'choice_label' => 'name'
            ])
            ->add('stock', TextType::class, [
                'label' => 'Stock level',
                'help' => 'This will be initial stock.'
            ])
            ->add('sku', TextType::class, [
                'required' => false,
                'label' => 'SKU'
            ])
            ->add('ean', TextType::class, [
                'required' => false,
                'label' => 'EAN'
            ])
            ->add('gtin', TextType::class, [
                'required' => false,
                'label' => 'GTIN'
            ])
            ->add('isbn', TextType::class, [
                'required' => false,
                'label' => 'ISBN',
                'help' => 'Applies to books only.'
            ])
            ->add('manufacturerCode', TextType::class, [
                'required' => false,
                'label' => 'Manufacturer code'
            ])
            ->add('modelNumber', TextType::class, [
                'required' => false,
                'label' => 'Model no'
            ])
            ->add('assignment', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'Assign to category' => 'category',
                    'Assign to parent product' => 'parent'
                ],
                'data' => 'category',
                'expanded' => true,
                'help' => 'This product will inherit category from its parent if \'Assign to parent product\' is chosen.'
            ])
            ->add('category', HiddenType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Select category'
            ])
            ->add('parent', HiddenType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Pick parent product'
            ])
            ->add('highlights', CollectionType::class, [
                'allow_add' => true,
                'allow_delete'=> true
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save product'
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            
            $product = $event->getData();
            $form = $event->getForm();


            if($product->getParent() != null) {

                $form
                    ->add('assignment', ChoiceType::class, [
                        'mapped' => false,
                        'choices' => [
                            'Assign to category' => 'category',
                            'Assign to parent product' => 'parent'
                        ],
                        'data' => 'parent',
                        'expanded' => true,
                        'help' => 'This product will inherit category from its parent if \'Assign to parent product\' is chosen.'
                    ]);

            }

            //$event->setData($product);


        });



        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'allow_extra_fields' => true
        ]);
    }
}
