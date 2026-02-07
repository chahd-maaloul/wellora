<?php

namespace App\Form;

use App\Entity\PublicationParcours;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicationParcoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imagePublication')
            ->add('ambiance')
            ->add('securite')
            ->add('datePublication')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PublicationParcours::class,
        ]);
    }
}
