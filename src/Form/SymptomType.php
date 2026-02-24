<?php

namespace App\Form;

use App\Entity\Symptom;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SymptomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type de symptôme',
                'required' => false,
                'placeholder' => 'Sélectionnez un symptôme',
                'choices' => [
                    'Céphalée (Mal de tête)' => 'Céphalée',
                    'Nausée' => 'Nausée',
                    'Vertige' => 'Vertige',
                    'Douleurs musculaires' => 'Douleurs musculaires',
                    'Douleurs articulaires' => 'Douleurs articulaires',
                    'Fatigue' => 'Fatigue',
                    'Fièvre' => 'Fièvre',
                    'Toux' => 'Toux',
                    'Difficultés respiratoires' => 'Difficultés respiratoires',
                    'Douleurs thoraciques' => 'Douleurs thoraciques',
                    'Insomnie' => 'Insomnie',
                    'Anxiété' => 'Anxiété',
                    'Dépression' => 'Dépression',
                    'Brûlure d\'estomac' => 'Brûlure d\'estomac',
                    'Diarrhée' => 'Diarrhée',
                    'Constipation' => 'Constipation',
                    'Douleurs abdominales' => 'Douleurs abdominales',
                    'Autre' => 'Autre',
                ],
            ])
            ->add('intensite', IntegerType::class, [
                'label' => 'Intensité (1-10)',
                'required' => false,
                'attr' => [
                    'min' => 1,
                    'max' => 10,
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
