<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ChangePasswordFormType extends AbstractType
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentUser = $options['current_user'] ?? null;

        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Current Password',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Current password is required']),
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'New Password',
                    'constraints' => [
                        new NotBlank(['message' => 'New password is required']),
                        new Length([
                            'min' => 8,
                            'max' => 255,
                            'minMessage' => 'Password must be at least {{ limit }} characters',
                            'maxMessage' => 'Password cannot be longer than {{ limit }} characters',
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                            'message' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&)',
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirm New Password',
                ],
                'invalid_message' => 'The password fields must match.',
            ])
        ;

        // Add validation for current password
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($currentUser) {
            $form = $event->getForm();
            $data = $event->getData();
            $currentPassword = $data['currentPassword'] ?? null;

            if ($currentUser && $currentPassword) {
                if (!$this->passwordHasher->isPasswordValid($currentUser, $currentPassword)) {
                    $form->get('currentPassword')->addError(
                        new \Symfony\Component\Form\FormError('Current password is incorrect')
                    );
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'current_user' => null,
        ]);
    }
}
