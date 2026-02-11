<?php

namespace App\Form;

use App\Entity\Consultation;
use App\Entity\Examens;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExamenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type_examen')
            ->add('date_examen')
            ->add('resultat')
            ->add('status')
            ->add('notes')
            ->add('nom_examen')
            ->add('date_realisation')
            ->add('result_file')
            ->add('id_consultation', EntityType::class, [
                'class' => Consultation::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Examens::class,
        ]);
    }
}
