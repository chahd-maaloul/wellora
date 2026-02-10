<?php

namespace App\Form;

use App\Entity\Symptom;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SymptomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', TextType::class, [
                'label' => 'Type de symptôme',
                'required' => false,
                'attr' => [
                    'placeholder' => 'ex: Céphalée, Nausée...',
                ]
            ])
            ->add('intensite', IntegerType::class, [
                'label' => 'Intensité',
                'required' => false,
                'attr' => [
                    'min' => 1,
                    'max' => 10,
                    'placeholder' => '1-10',
                ]
            ])
            ->add('zone', TextType::class, [
                'label' => 'Zone du corps',
                'required' => false,
                'attr' => [
                    'placeholder' => 'ex: Tête, Cou, Bras...',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Symptom::class,
        ]);
    }
}
