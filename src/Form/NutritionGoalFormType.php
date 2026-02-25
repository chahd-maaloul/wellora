<?php

namespace App\Form;

use App\Entity\NutritionGoal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NutritionGoalFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('caloriesTarget', IntegerType::class, [
                'label' => 'Calories (kcal)',
                'required' => false,
                'attr' => ['min' => 1000, 'max' => 5000, 'placeholder' => '2000']
            ])
            ->add('waterTarget', IntegerType::class, [
                'label' => "Verres d'eau",
                'required' => false,
                'attr' => ['min' => 1, 'max' => 20, 'placeholder' => '8']
            ])
            ->add('proteinTarget', IntegerType::class, [
                'label' => 'Protéines (g)',
                'required' => false,
                'attr' => ['min' => 0, 'max' => 300, 'placeholder' => '120']
            ])
            ->add('carbsTarget', IntegerType::class, [
                'label' => 'Glucides (g)',
                'required' => false,
                'attr' => ['min' => 0, 'max' => 500, 'placeholder' => '200']
            ])
            ->add('fatsTarget', IntegerType::class, [
                'label' => 'Lipides (g)',
                'required' => false,
                'attr' => ['min' => 0, 'max' => 200, 'placeholder' => '65']
            ])
            ->add('fiberTarget', IntegerType::class, [
                'label' => 'Fibres (g)',
                'required' => false,
                'attr' => ['min' => 0, 'max' => 100, 'placeholder' => '25']
            ])
            ->add('sugarTarget', IntegerType::class, [
                'label' => 'Sucre (g)',
                'required' => false,
                'attr' => ['min' => 0, 'max' => 100, 'placeholder' => '25']
            ])
            ->add('sodiumTarget', IntegerType::class, [
                'label' => 'Sodium (mg)',
                'required' => false,
                'attr' => ['min' => 0, 'max' => 5000, 'placeholder' => '2300']
            ])
            ->add('weightTarget', NumberType::class, [
                'label' => 'Poids objectif (kg)',
                'required' => false,
                'attr' => ['min' => 30, 'max' => 300, 'step' => 0.1]
            ])
            ->add('currentWeight', NumberType::class, [
                'label' => 'Poids actuel (kg)',
                'required' => false,
                'attr' => ['min' => 30, 'max' => 300, 'step' => 0.1]
            ])
            ->add('startWeight', NumberType::class, [
                'label' => 'Poids de départ (kg)',
                'required' => false,
                'attr' => ['min' => 30, 'max' => 300, 'step' => 0.1]
            ])
            ->add('activityLevel', TextType::class, [
                'label' => "Niveau d'activité",
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer les objectifs',
                'attr' => ['class' => 'btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NutritionGoal::class,
        ]);
    }
}
