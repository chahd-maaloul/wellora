<?php

namespace App\Form;

use App\Entity\DailyPlan;
use App\Entity\Exercises;
use App\Entity\Goal;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DailyPlanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre du plan quotidien',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Entraînement complet du lundi',
                ],
                'required' => true,
            ])
            
            ->add('date', DateType::class, [
                'label' => 'Date du plan',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => true,
            ])
            
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Planifié' => 'planned',
                    'En cours' => 'in_progress',
                    'Terminé' => 'completed',
                    'Annulé' => 'cancelled',
                    'Reporté' => 'postponed',
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
                'required' => true,
                'placeholder' => '-- Sélectionner un statut --',
            ])
            
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Instructions spéciales, conseils, remarques...',
                ],
            ])
            
            ->add('calories', IntegerType::class, [
                'label' => 'Calories estimées',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'placeholder' => 'Ex: 500',
                ],
                'help' => 'Total estimé de calories brûlées pour ce plan',
            ])
            
            ->add('duree_min', IntegerType::class, [
                'label' => 'Durée totale (minutes)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'placeholder' => 'Ex: 60',
                ],
                'help' => 'Durée totale estimée du plan en minutes',
            ])
            
            ->add('goal', EntityType::class, [
                'label' => 'Objectif associé',
                'class' => Goal::class,
                'choice_label' => 'title',
                'placeholder' => '-- Sélectionner un objectif (optionnel) --',
                'required' => false,
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            
            ->add('exercices', EntityType::class, [
                'label' => 'Exercices',
                'class' => Exercises::class,
                'choice_label' => function(Exercises $exercise) {
                    return sprintf('%s (%s - %s)', 
                        $exercise->getName(), 
                        $exercise->getCategory(), 
                        $exercise->getDifficultyLevel()
                    );
                },
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'form-select select2-multiple',
                    'data-placeholder' => 'Sélectionnez les exercices',
                ],
                'required' => true,
                'by_reference' => false,
                'help' => 'Sélectionnez plusieurs exercices pour ce plan quotidien',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.isActive = :isActive')
                        ->setParameter('isActive', true)
                        ->orderBy('e.category', 'ASC')
                        ->addOrderBy('e.name', 'ASC');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DailyPlan::class,
        ]);
    }
}