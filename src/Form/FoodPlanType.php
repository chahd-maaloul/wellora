<?php

namespace App\Form;

use App\Entity\FoodItem;
use App\Entity\FoodPlan;
use App\Entity\NutritionGoal;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FoodPlanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomPlan')
            ->add('calories')
            ->add('protein')
            ->add('fat')
            ->add('carbs')
            ->add('planDate')
            ->add('nutritionGoal', EntityType::class, [
                'class' => NutritionGoal::class,
                'choice_label' => 'id',
            ])
            ->add('foodItems', EntityType::class, [
                'class' => FoodItem::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FoodPlan::class,
        ]);
    }
}
