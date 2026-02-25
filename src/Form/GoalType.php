<?php

namespace App\Form;

use App\Entity\Goal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GoalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // 1. Informations de base
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'objectif',
                'attr' => ['placeholder' => 'Ex: Perdre 4 kg en 30 jours'],
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'required' => true,
                'choices' => [
                    'Weight Loss' => 'weight_loss',
                    'Strength' => 'strength',
                    'Endurance' => 'endurance',
                    'Flexibility' => 'flexibility',
                    'Sports' => 'sports',
                    'Rehabilitation' => 'rehabilitation',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'required' => true,
                'choices' => [
                     'PENDING' => 'PENDING',
                    'in progress' => 'in progress',
                    'completed' => 'completed',
                ],
                'data' => 'PENDING',
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('date', DateType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text',
                'required' => false,
            ])
            
            // 2. Raison / Pourquoi
            ->add('relevant', TextareaType::class, [
                'label' => 'Pourquoi cet objectif est important',
                'required' => false,
                'attr' => ['rows' => 3, 'placeholder' => 'Ex: Améliorer ma santé, Préparer une compétition'],
            ])
            
            // 3. Engagement utilisateur
            ->add('userCommitment', CheckboxType::class, [
                'label' => 'Je m\'engage à suivre cet objectif',
                'required' => true,
            ])
            
            // 4. Niveau de difficulté
            ->add('difficultyLevel', ChoiceType::class, [
                'label' => 'Niveau de difficulté',
                'required' => false,
                'choices' => [
                    'Beginner' => 'Beginner',
                    'Intermediate' => 'Intermediate',
                    'Advanced' => 'Advanced',
                ],
            ])
            
            // 5. Public cible
            ->add('targetAudience', ChoiceType::class, [
                'label' => 'Public cible',
                'required' => false,
                'choices' => [
                    'General' => 'General',
                    'Weight Loss' => 'Weight Loss',
                    'Muscle Gain' => 'Muscle Gain',
                    'Endurance' => 'Endurance',
                    'Flexibility' => 'Flexibility',
                    'Rehabilitation' => 'Rehabilitation',
                ],
            ])
            
            // 6. Objectif mesurable
            ->add('targetValue', NumberType::class, [
                'label' => 'Valeur cible',
                'required' => true,
                'attr' => ['placeholder' => 'Ex: 5', 'step' => '0.01'],
            ])
            ->add('currentValue', NumberType::class, [
                'label' => 'Valeur actuelle',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: 0', 'step' => '0.01'],
            ])
            ->add('unit', ChoiceType::class, [
                'label' => 'Unité',
                'required' => true,
                'choices' => [
                    'kg' => 'kg',
                    'lbs' => 'lbs',
                    'minutes' => 'minutes',
                    'hours' => 'hours',
                    'days' => 'days',
                    'weeks' => 'weeks',
                    'reps' => 'reps',
                    'sets' => 'sets',
                    'points' => 'points',
                    'sessions' => 'sessions',
                    'cm' => 'cm',
                    'inches' => 'inches',
                    'calories' => 'calories',
                ],
            ])
            ->add('progress', IntegerType::class, [
                'label' => 'Progression (%)',
                'required' => false,
                'attr' => ['min' => 0, 'max' => 100, 'value' => 0],
            ])
            ->add('goalType', ChoiceType::class, [
                'label' => 'Type d\'objectif',
                'required' => false,
                'choices' => [
                    'Perte de poids' => 'weight_loss',
                    'Force' => 'strength',
                    'Endurance' => 'endurance',
                    'Flexibilité' => 'flexibility',
                    'Performance' => 'performance',
                    'Réhabilitation' => 'rehabilitation',
                ],
            ])
            
            // 7. Planning & organisation
            ->add('frequency', ChoiceType::class, [
                'label' => 'Fréquence',
                'required' => false,
                'choices' => [
                    'Daily' => 'Daily',
                    'Weekly' => 'Weekly',
                    'Monthly' => 'Monthly',
                    'Custom' => 'Custom',
                ],
            ])
            ->add('sessionsPerWeek', IntegerType::class, [
                'label' => 'Sessions par semaine',
                'required' => false,
                'attr' => ['min' => 1, 'max' => 7, 'placeholder' => 'Ex: 3'],
            ])
            ->add('sessionDuration', IntegerType::class, [
                'label' => 'Durée de session (minutes)',
                'required' => false,
                'attr' => ['min' => 5, 'max' => 180, 'placeholder' => 'Ex: 45'],
            ])
            ->add('preferredTime', TimeType::class, [
                'label' => 'Heure préférée',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('durationWeeks', IntegerType::class, [
                'label' => 'Durée (semaines)',
                'required' => false,
                'attr' => ['min' => 1, 'max' => 52, 'placeholder' => 'Ex: 12'],
            ])
            ->add('restDays', IntegerType::class, [
                'label' => 'Jours de repos par semaine',
                'required' => false,
                'attr' => ['min' => 0, 'max' => 6, 'placeholder' => 'Ex: 2'],
            ])
            
            // 8. Santé & contraintes
            ->add('weightStart', NumberType::class, [
                'label' => 'Poids de départ (kg)',
                'required' => false,
                'attr' => ['step' => '0.1', 'placeholder' => 'Ex: 70.5'],
            ])
            ->add('weightTarget', NumberType::class, [
                'label' => 'Poids cible (kg)',
                'required' => false,
                'attr' => ['step' => '0.1', 'placeholder' => 'Ex: 65.0'],
            ])
            ->add('height', IntegerType::class, [
                'label' => 'Taille (cm)',
                'required' => false,
                'attr' => ['min' => 100, 'max' => 250, 'placeholder' => 'Ex: 170'],
            ])
            ->add('caloriesTarget', IntegerType::class, [
                'label' => 'Objectif calories quotidien',
                'required' => false,
                'attr' => ['min' => 500, 'max' => 5000, 'placeholder' => 'Ex: 2000'],
            ])

           ->add('coachId',TextType::class, [
            'required' => false,
            'mapped' => true, // Important : true car le champ existe dans l'entité
            'attr' => [
                'id' => 'goal_coachId',
                'class' => 'coach-field'
            ]
        ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Goal::class,
            'coaches' => [],
        ]);
    }
    private function getAvailableCoaches($options)
{
    // Cette méthode sera appelée avec les options du formulaire
    // Vous devez passer les coachs depuis le contrôleur
    return $options['coaches'] ?? [];
}


}