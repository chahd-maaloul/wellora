<?php

namespace App\Form;

use App\Entity\Exercises;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class ExerciseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Exercise Name',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., Push-ups, Squats, Plank',
                ]
            ])
            
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Brief description of the exercise...',
                    'rows' => 3,
                ]
            ])
            
            ->add('category', ChoiceType::class, [
                'label' => 'Category',
                'required' => true,
                'choices' => [
                    'Strength' => 'Strength',
                    'Cardio' => 'Cardio',
                    'Flexibility' => 'Flexibility',
                    'Balance' => 'Balance',
                    'Core' => 'Core',
                    'Warm-up' => 'Warm-up',
                    'Cool-down' => 'Cool-down',
                ],
                'placeholder' => '-- Select a category --',
                'attr' => ['class' => 'form-select']
            ])
            
            ->add('difficulty_level', ChoiceType::class, [
                'label' => 'Difficulty Level',
                'required' => true,
                'choices' => [
                    'Beginner' => 'Beginner',
                    'Intermediate' => 'Intermediate',
                    'Advanced' => 'Advanced',
                ],
                'placeholder' => '-- Select difficulty --',
                'attr' => ['class' => 'form-select']
            ])
            
            ->add('defaultUnit', ChoiceType::class, [
                'label' => 'Default Unit',
                'required' => true,
                'choices' => [
                    'Repetitions' => 'reps',
                    'Seconds' => 'seconds',
                    'Minutes' => 'minutes',
                    'Meters' => 'meters',
                    'Kilometers' => 'km',
                    'Calories' => 'calories',
                    'Steps' => 'steps',
                ],
                'placeholder' => '-- Select default unit --',
                'attr' => ['class' => 'form-select']
            ])
            
            ->add('videoFile', VichFileType::class, [
                'label' => 'Video Upload',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Remove video',
                'download_label' => 'Download video',
                'download_uri' => true,
                'asset_helper' => true,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'video/*',
                ],
                'help' => 'Upload a video file (max 100MB) or provide a URL below',
            ])
            
            ->add('videoUrl', UrlType::class, [
                'label' => 'Video URL (Alternative)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://youtube.com/watch?v=...',
                ],
                'help' => 'Or paste a YouTube/Vimeo link if you prefer'
            ])
            
            ->add('duration', IntegerType::class, [
                'label' => 'Duration (minutes)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., 30',
                    'min' => 0,
                ],
                'help' => 'Average duration in minutes (optional)'
            ])
            
            ->add('calories', IntegerType::class, [
                'label' => 'Calories Burned (per minute)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., 10',
                    'min' => 0,
                ],
                'help' => 'Approximate calories burned per minute (optional)'
            ])
            
            ->add('sets', IntegerType::class, [
                'label' => 'Sets',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., 3',
                    'min' => 0,
                ],
                'help' => 'Recommended number of sets (optional)'
            ])
            
            ->add('reps', IntegerType::class, [
                'label' => 'Repetitions',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., 12',
                    'min' => 0,
                ],
                'help' => 'Recommended repetitions per set (optional)'
            ])
            
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label'],
                'data' => true,
            ])
            
            ->add('createdAt', DateTimeType::class, [
                'label' => 'Created Date',
                'required' => true,
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'form-control datetimepicker'],
                'data' => new \DateTime(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Exercises::class,
        ]);
    }
}