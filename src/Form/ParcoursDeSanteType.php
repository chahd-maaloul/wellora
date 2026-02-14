<?php

namespace App\Form;

use App\Entity\ParcoursDeSante;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ParcoursDeSanteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomParcours', TextType::class, [
                'label' => 'Parcours Name',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter parcours name (at least 5 letters only)',
                ]
            ])
            ->add('localisationParcours', TextType::class, [
                'label' => 'Location',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Selected from map',
                ]
            ])
            ->add('latitudeParcours', NumberType::class, [
                'label' => 'Latitude',
                'required' => true,
                'scale' => 6,
                'attr' => [
                    'readonly' => true,
                    'step' => '0.000001',
                ],
            ])
            ->add('longitudeParcours', NumberType::class, [
                'label' => 'Longitude',
                'required' => true,
                'scale' => 6,
                'attr' => [
                    'readonly' => true,
                    'step' => '0.000001',
                ],
            ])
            ->add('distanceParcours', NumberType::class, [
                'label' => 'Distance (km)',
                'attr' => [
                    'placeholder' => '0 to 20 km',
                    'step' => '0.1',
                    'min' => '0',
                    'max' => '20',
                ]
            ])
            ->add('dateCreation', DateType::class, [
                'label' => 'Creation Date',
                'widget' => 'single_text',
                'attr' => [
                    'type' => 'date',
                ]
            ]);

        // Image is always required
        $imageConstraints = [
            new \Symfony\Component\Validator\Constraints\NotBlank(
                message: 'Please upload a parcours image'
            ),
        ];
        $imageConstraints[] = new Image([
            'maxSize' => '10M',
            'maxSizeMessage' => 'The image file is too large (max 10 MB)',
            'mimeTypes' => [
                'image/jpeg',
                'image/png',
            ],
            'mimeTypesMessage' => 'Please upload a valid JPG or PNG image',
            'minWidth' => 100,
            'maxWidth' => 10000,
            'minHeight' => 100,
            'maxHeight' => 10000,
            'minWidthMessage' => 'The image width must be at least 100px',
            'maxWidthMessage' => 'The image width cannot exceed 10000px',
            'minHeightMessage' => 'The image height must be at least 100px',
            'maxHeightMessage' => 'The image height cannot exceed 10000px',
        ]);
        
        $builder->add('imageFile', FileType::class, [
            'label' => 'Parcours Image (JPG or PNG)',
            'mapped' => false,
            'required' => true,
            'constraints' => $imageConstraints,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ParcoursDeSante::class,
        ]);
    }
}
