<?php

namespace App\Form;

use App\Entity\NutritionGoal;
use App\Entity\Nutritionist;
use App\Entity\Patient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NutritionGoalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('goalType')
            ->add('dailyCalories')
            ->add('proteinPercent')
            ->add('fatPercent')
            ->add('carbPercent')
            ->add('startDate')
            ->add('targetDate')
            ->add('isActive')
            ->add('notes')
            ->add('nutritionist', EntityType::class, [
                'class' => Nutritionist::class,
                'choice_label' => 'id',
            ])
            ->add('patient', EntityType::class, [
                'class' => Patient::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NutritionGoal::class,
        ]);
    }
}
