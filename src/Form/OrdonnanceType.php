<?php

namespace App\Form;

use App\Entity\Consultation;
use App\Entity\Ordonnance;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrdonnanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_ordonnance')
            ->add('medicament')
            ->add('dosage')
            ->add('forme')
            ->add('duree_traitement')
            ->add('instructions')
            ->add('frequency')
            ->add('diagnosis_code')
            ->add('id_consultation', EntityType::class, [
                'class' => Consultation::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ordonnance::class,
        ]);
    }
}
