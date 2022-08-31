<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserRoleFormType extends AbstractType
{

    private $security;
    private $translator;

    public function __construct(Security $security, TranslatorInterface $translator)
    {
        $this->security = $security;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $roles = [
            $this->translator->trans('Registered user') => 'ROLE_USER',
            $this->translator->trans('Trainee') => 'ROLE_TRAINEE',
            $this->translator->trans('Floor staff') => 'ROLE_FLOOR_STAFF',
            $this->translator->trans('Customer care') => 'ROLE_CUSTOMER_CARE',
            $this->translator->trans('Stock-keeper') => 'ROLE_STOCK_KEEPER',
            $this->translator->trans('Accountant') => 'ROLE_ACCOUNTANT',
            $this->translator->trans('Manager') => 'ROLE_MANAGER',
            $this->translator->trans('Administrator') => 'ROLE_ADMIN'
        ];

        if(in_array('ROLE_SUPER_ADMIN', $this->security->getUser()->getRoles())) {
            // Leave all
        }
        elseif(in_array('ROLE_ADMIN', $this->security->getUser()->getRoles())) {
            // Do not allow to control other admins as only super admin can do this
            unset($roles[$this->translator->trans('Administrator')]);
        }
        elseif(in_array('ROLE_MANAGER', $this->security->getUser()->getRoles())) {
            // Do not allow to control admins or other managers
            unset($roles[$this->translator->trans('Administrator')]);
            unset($roles[$this->translator->trans('Manager')]);
        }
        else {
            // Do not allow to control any role
            $roles = [];
        }

        $builder
            ->add('user', HiddenType::class, [
                'mapped' => false
            ])
            ->add('role', ChoiceType::class, [
                'label' => $this->translator->trans('Set account type'),
                'choices' => $roles,
                'mapped' => false
            ])
            ->add('submit', SubmitType::class, [
                
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
