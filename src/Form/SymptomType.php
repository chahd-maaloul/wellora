<?php

namespace App\Form;

use App\Entity\Symptom;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SymptomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', TextType::class, [
                'label' => 'Type de symptôme',
                'attr' => [
                    'placeholder' => 'ex: Maux de tête, Fatigue...',
                ]
            ])
            ->add('intensite', IntegerType::class, [
                'label' => 'Intensité',
                'attr' => [
                    'min' => 1,
                    'max' => 10,
                    'placeholder' => '1-10',
                ]
            ])
            ->add('date_observation', DateType::class, [
                'label' => 'Date d\'observation',
                'widget' => 'single_text',
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
