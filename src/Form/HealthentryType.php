<?php

namespace App\Form;

use App\Entity\Healthentry;
use App\Entity\Healthjournal;
use App\Entity\Symptom;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HealthentryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', null, [
                'widget' => 'single_text',
                'label' => 'Date',
                'attr' => [
                    'max' => 'today',
                ],
            ])
            ->add('poids', NumberType::class, [
                'label' => 'Poids (kg)',
                'required' => false,
                'scale' => 1,
                'attr' => [
                    'min' => 30,
                    'max' => 200,
                    'step' => 0.1,
                ],
            ])
            ->add('glycemie', NumberType::class, [
                'label' => 'Glycémie (g/l)',
                'required' => false,
                'scale' => 1,
                'attr' => [
                    'min' => 0.5,
                    'max' => 3,
                    'step' => 0.1,
                ],
            ])
            ->add('tension', NumberType::class, [
                'label' => 'Tension diastolique (mmHg)',
                'required' => false,
                'scale' => 0,
                'attr' => [
                    'min' => 40,
                    'max' => 120,
                    'step' => 1,
                    'placeholder' => '80',
                ],
            ])
            ->add('sommeil', NumberType::class, [
                'label' => 'Heures de sommeil',
                'required' => false,
                'scale' => 1,
                'attr' => [
                    'min' => 0,
                    'max' => 12,
                    'step' => 0.5,
                ],
            ])
            // Journal is set programmatically in controller
            ->add('symptoms', CollectionType::class, [
                'entry_type' => SymptomType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Symptômes',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Healthentry::class,
        ]);
    }
}
