<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FilterSalessType extends AbstractType
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
                'help' => $this->translator->trans('Search for orders using keywords.')
            ])
            ->add('grossValueBetween', TextType::class, [
                'label' => $this->translator->trans('Order value between'),
                'required' => false,
                'help' => $this->translator->trans('Filters by gross value.')
            ])
            ->add('grossValueAnd', TextType::class, [
                'label' => $this->translator->trans('and'),
                'required' => false
            ])
            ->add('dateBetween', DateType::class, [
                'label' => $this->translator->trans('Date between'),
                'required' => false,
                'widget' => 'single_text'
            ])
            ->add('dateAnd', DateType::class, [
                'label' => $this->translator->trans('and'),
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