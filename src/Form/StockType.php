<?php

namespace App\Form;

use App\Entity\Stock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class StockType extends AbstractType
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
        $builder
            ->add('stockChange', TextType::class, [
                'label' => $this->translator->trans('Stock change'),
                'help' => $this->translator->trans('This value must be different than 0. Negative and positive values are accepted.')
            ])
            ->add('changesDescription', TextareaType::class, [
                'label' => $this->translator->trans('Changes description'),
                'required' => false,
                'attr' => [
                    'placeholder' => $this->translator->trans('E.g. new stock from Monday\'s delivery.')
                ]
            ])
            ->add('submit', SubmitType::class, [

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}
