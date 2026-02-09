<?php

namespace App\Form;

use App\Entity\PublicationParcours;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class PublicationParcoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $imageConstraints = [
            new Image([
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
            ]),
        ];

        if ($options['require_image']) {
            $imageConstraints[] = new NotBlank(message: 'Please upload a publication image');
        }

        $builder
            ->add('textPublication', TextareaType::class, [
                'label' => 'Publication Text',
                'attr' => [
                    'placeholder' => 'Enter your publication text (minimum 10 characters)',
                    'rows' => 5,
                ]
            ])
            ->add('ambiance', IntegerType::class, [
                'label' => 'Ambiance (1-5)',
                'attr' => [
                    'min' => 1,
                    'max' => 5,
                ],
            ])
            ->add('securite', IntegerType::class, [
                'label' => 'Safety (1-5)',
                'attr' => [
                    'min' => 1,
                    'max' => 5,
                ],
            ])
            ->add('datePublication', DateType::class, [
                'label' => 'Publication Date',
                'widget' => 'single_text',
                'attr' => [
                    'type' => 'date',
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Parcours Image (JPG or PNG)',
                'mapped' => false,
                'required' => $options['require_image'],
                'constraints' => $imageConstraints,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PublicationParcours::class,
            'require_image' => false,
        ]);

        $resolver->setAllowedTypes('require_image', 'bool');
    }
}
