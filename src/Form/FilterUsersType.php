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
                    'placeholder' => $this->translator->trans('Search for...')
                ],
                'help' => $this->translator->trans('Search for users by name.')
            ])
            ->add('email', EmailType::class, [
                'label' => null,
                'required' => false,
                'attr' => [
                    'placeholder' => ''
                ],
                'help' => $this->translator->trans('Search for users by email.')
            ])
            ->add('role', ChoiceType::class, [
                'label' => $this->translator->trans('Filter by account type'),
                'required' => false,
                'choices' => [
                    $this->translator->trans('Registered user') => 'ROLE_USER',
                    $this->translator->trans('Trainee') => 'ROLE_TRAINEE',
                    $this->translator->trans('Floor staff') => 'ROLE_FLOOR_STAFF',
                    $this->translator->trans('Customer care') => 'ROLE_CUSTOMER_CARE',
                    $this->translator->trans('Stock-keeper') => 'ROLE_STOCK_KEEPER',
                    $this->translator->trans('Accountant') => 'ROLE_ACCOUNTANT',
                    $this->translator->trans('Manager') => 'ROLE_MANAGER',
                    $this->translator->trans('Administrator') => 'ROLE_ADMIN',
                    $this->translator->trans('Super administrator') => 'ROLE_SUPER_ADMIN'
                ],
                'help' => $this->translator->trans('Search for users by email.')
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