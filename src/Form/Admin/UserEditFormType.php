<?php

namespace App\Form\Admin;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'admin.users.form.email',
                'disabled' => true, // Email cannot be changed
            ])
            ->add('firstName', TextType::class, [
                'label' => 'admin.users.form.first_name',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'admin.users.form.last_name',
            ])
            ->add('phone', TextType::class, [
                'label' => 'admin.users.form.phone',
                'required' => false,
            ])
            ->add('address', TextType::class, [
                'label' => 'admin.users.form.address',
                'required' => false,
            ])
            ->add('birthdate', DateType::class, [
                'label' => 'admin.users.form.birthdate',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'admin.users.form.is_active',
                'required' => false,
            ])
            ->add('isEmailVerified', CheckboxType::class, [
                'label' => 'admin.users.form.is_email_verified',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'admin',
        ]);
    }
}
