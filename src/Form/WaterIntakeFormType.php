<?php

namespace App\Form;

use App\Entity\WaterIntake;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WaterIntakeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('glasses', IntegerType::class, [
                'label' => 'Nombre de verres',
                'required' => true,
                'attr' => ['min' => 1, 'max' => 20, 'placeholder' => '8']
            ])
            ->add('milliliters', IntegerType::class, [
                'label' => 'Millilitres (optionnel)',
                'required' => false,
                'attr' => ['min' => 0, 'placeholder' => '0']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => ['class' => 'btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WaterIntake::class,
        ]);
    }
}
