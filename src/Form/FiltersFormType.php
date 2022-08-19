<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FilterOrdersFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', TextType::class, [
                'label' => null,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Search for...'
                ],
                'help' => 'Search for orders using keywords.'
            ])
            ->add('grossValueBetween', TextType::class, [
                'label' => 'Order value between',
                'required' => false,
                'help' => 'Filters by gross value.'
            ])
            ->add('grossValueAnd', TextType::class, [
                'label' => 'and',
                'required' => false
            ])
            ->add('dateBetween', DateType::class, [
                'label' => 'Date between',
                'required' => false,
                'widget' => 'single_text'
            ])
            ->add('dateAnd', DateType::class, [
                'label' => 'and',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'max' => (new \DateTime())->format('Y-m-d')
                ]
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
        ]);
    }
}