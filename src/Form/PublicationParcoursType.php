<?php

namespace App\Form;

use App\Entity\PublicationParcours;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicationParcoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('textPublication', TextareaType::class, [
                'label' => 'Publication Text',
                'attr' => [
                    'placeholder' => 'Enter your publication text (minimum 10 characters)',
                    'rows' => 5,
                ]
            ])
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
