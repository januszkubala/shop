<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FilterOrdersType extends AbstractType
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

class FilterUsersType extends AbstractType
{

    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', TextType::class, [
                'label' => null,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Search for...'
                ],
                'help' => 'Search for users by name.'
            ])
            ->add('email', EmailType::class, [
                'label' => null,
                'required' => false,
                'attr' => [
                    'placeholder' => ''
                ],
                'help' => 'Search for users by email.'
            ])
            ->add('role', ChoiceType::class, [
                'label' => $translate->trans('Filter by account type'),
                'required' => false,
                'choices' => [
                    'ROLE_USER' => $this->translator->trans('Registered users'),
                    'ROLE_TRAINEE' => $this->translator->trans('Trainee'),
                    'ROLE_FLOOR_STAFF' => $this->translator->trans('Floor staff'),
                    'ROLE_CUSTOMER_CARE' => $this->translator->trans('Customer care'),
                    'ROLE_STOCK_KEEPER' => $this->translator->trans('Stock-keeper'),
                    'ROLE_ACCOUNTANT' => $this->translator->trans('Accountant'),
                    'ROLE_MANAGER' => $this->translator->trans('Manager'),
                    'ROLE_ADMIN' => $this->translator->trans('Administrator'),
                    'ROLE_SUPER_ADMIN' => $this->translator->trans('Super administrator')
                ],
                'help' => 'Search for users by email.'
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