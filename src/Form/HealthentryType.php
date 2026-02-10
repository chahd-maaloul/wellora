<?php

namespace App\Form;

use App\Entity\Healthentry;
use App\Entity\Healthjournal;
use App\Entity\Symptom;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HealthentryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date')
            ->add('poids')
            ->add('glycemie')
            ->add('tension')
            ->add('sommeil')
            ->add('journal', EntityType::class, [
                'class' => Healthjournal::class,
                'choice_label' => 'id',
            ])
            ->add('symptoms', CollectionType::class, [
                'entry_type' => SymptomType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
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
