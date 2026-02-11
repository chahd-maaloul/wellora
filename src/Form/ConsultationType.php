<?php

namespace App\Form;

use App\Entity\Consultation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsultationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('consultation_type', ChoiceType::class, [
                'choices' => [
                    'First Visit' => 'first-visit',
                    'Follow-up' => 'follow-up',
                    'Emergency' => 'emergency',
                ],
                'label' => 'Consultation Type',
                'required' => true,
            ])
            ->add('appointment_mode', ChoiceType::class, [
                'choices' => [
                    'In-Person' => 'in-person',
                    'Video Call' => 'video',
                    'Phone Call' => 'phone',
                ],
                'label' => 'Appointment Mode',
                'required' => true,
            ])
            ->add('reason_for_visit', TextareaType::class, [
                'label' => 'Reason for Visit',
                'required' => true,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Please describe your symptoms or reason for the visit...'
                ]
            ])
            ->add('symptoms_description', TextType::class, [
                'label' => 'Symptoms',
                'required' => false,
            ])
            ->add('date_consultation', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Consultation Date',
                'required' => true,
            ])
            ->add('time_consultation', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Consultation Time',
                'required' => true,
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Duration (minutes)',
                'data' => 30,
            ])
            ->add('location', TextType::class, [
                'label' => 'Location',
                'required' => false,
            ])
            ->add('fee', IntegerType::class, [
                'label' => 'Fee (TND)',
                'required' => true,
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Additional Notes',
                'required' => false,
                'attr' => ['rows' => 2]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Consultation::class,
        ]);
    }
}