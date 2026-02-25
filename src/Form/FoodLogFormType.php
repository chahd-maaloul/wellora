<?php

namespace App\Form;

use App\Entity\FoodLog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FoodLogFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'label' => 'Date',
                'required' => true,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('mealType', ChoiceType::class, [
                'label' => 'Type de repas',
                'required' => true,
                'choices' => [
                    'Petit-déjeuner' => 'breakfast',
                    'Déjeuner' => 'lunch',
                    'Dîner' => 'dinner',
                    'Collation' => 'snacks'
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FoodLog::class,
        ]);
    }
}
