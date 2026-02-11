<?php

namespace App\Form;

use App\Entity\ConsultationRequest;
use App\Entity\Nutritionist;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsultationRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('patientName')
            ->add('patientEmail')
            ->add('requestedAt')
            ->add('durationMinutes')
            ->add('status')
            ->add('createdAt')
            ->add('nutritionist', EntityType::class, [
                'class' => Nutritionist::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConsultationRequest::class,
        ]);
    }
}
