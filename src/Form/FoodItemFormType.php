<?php

namespace App\Form;

use App\Entity\FoodItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FoodItemFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => "Nom de l'aliment",
                'required' => true,
                'attr' => ['placeholder' => 'Ex: Pomme, Poulet grillé, Riz']
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Quantité',
                'required' => false,
                'attr' => ['min' => 0.1, 'step' => 0.1, 'placeholder' => '1']
            ])
            ->add('unit', TextType::class, [
                'label' => 'Unité',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: portion, gramme, pièce, tasse']
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'required' => false,
                'choices' => [
                    'Toutes catégories' => null,
                    'Petit-déjeuner' => 'breakfast',
                    'Déjeuner' => 'lunch',
                    'Dîner' => 'dinner',
                    'Collation' => 'snacks',
                    'Dessert' => 'desserts',
                    'Boisson' => 'drinks',
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('isRecipe', ChoiceType::class, [
                'label' => 'Type d\'entrée',
                'required' => true,
                'choices' => [
                    'Aliment normal' => false,
                    'Recette' => true,
                ],
                'data' => false,
                'attr' => ['class' => 'form-select']
            ])
            ->add('calories', IntegerType::class, [
                'label' => 'Calories (kcal)',
                'required' => false,
                'attr' => ['min' => 0, 'placeholder' => '0']
            ])
            ->add('protein', NumberType::class, [
                'label' => 'Protéines (g)',
                'required' => false,
                'attr' => ['min' => 0, 'step' => 0.1, 'placeholder' => '0']
            ])
            ->add('carbs', NumberType::class, [
                'label' => 'Glucides (g)',
                'required' => false,
                'attr' => ['min' => 0, 'step' => 0.1, 'placeholder' => '0']
            ])
            ->add('fats', NumberType::class, [
                'label' => 'Lipides (g)',
                'required' => false,
                'attr' => ['min' => 0, 'step' => 0.1, 'placeholder' => '0']
            ])
            ->add('fiber', NumberType::class, [
                'label' => 'Fibres (g)',
                'required' => false,
                'attr' => ['min' => 0, 'step' => 0.1, 'placeholder' => '0']
            ])
            ->add('sugar', NumberType::class, [
                'label' => 'Sucres (g)',
                'required' => false,
                'attr' => ['min' => 0, 'step' => 0.1, 'placeholder' => '0']
            ])
            ->add('sodium', IntegerType::class, [
                'label' => 'Sodium (mg)',
                'required' => false,
                'attr' => ['min' => 0, 'placeholder' => '0']
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => ['placeholder' => 'Notes optionnelles...', 'rows' => 3]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ajouter l\'aliment',
                'attr' => ['class' => 'btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FoodItem::class,
        ]);
    }
}
