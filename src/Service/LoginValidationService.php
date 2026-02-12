<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class LoginValidationService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Validate login credentials via AJAX
     * Returns clear messages for each scenario
     */
    public function validateLogin(string $email, string $password): JsonResponse
    {
        // Scenario 1: Empty email
        if (empty($email)) {
            return $this->errorResponse(
                'email_empty',
                'Email requis',
                'Veuillez entrer votre adresse email.',
                'email'
            );
        }

        // Scenario 2: Empty password
        if (empty($password)) {
            return $this->errorResponse(
                'password_empty',
                'Mot de passe requis',
                'Veuillez entrer votre mot de passe.',
                'password'
            );
        }

        // Scenario 3: User not found
        $user = $this->entityManager->getRepository(User::class)->findOneByEmail($email);
        if (!$user) {
            return $this->errorResponse(
                'user_not_found',
                'Compte non trouvé',
                'Aucun compte n\'existe avec cette adresse email. Veuillez vérifier vos identifiants ou créer un nouveau compte.',
                'email'
            );
        }

        // Scenario 4: Account locked (too many failed attempts)
        if ($user->getLockedUntil() && $user->getLockedUntil() > new \DateTime()) {
            $lockedMinutes = ceil(($user->getLockedUntil()->getTimestamp() - time()) / 60);
            return $this->errorResponse(
                'account_locked',
                'Compte verrouillé',
                "Votre compte a été temporairement verrouillé après {$user->getLoginAttempts()} tentatives échouées. Veuillez réessayer dans {$lockedMinutes} minute(s).",
                'email',
                true
            );
        }

        // Scenario 5: Account inactive
        if (!$user->isIsActive()) {
            return $this->errorResponse(
                'account_inactive',
                'Compte désactivé',
                'Votre compte a été désactivé. Veuillez contacter l\'administrateur du système pour plus d\'informations.',
                'email',
                true
            );
        }

        // Scenario 6: Professional not verified by admin
        $roles = $user->getRoles();
        $professionalRoles = ['ROLE_MEDECIN', 'ROLE_COACH', 'ROLE_NUTRITIONIST'];
        $hasProfessionalRole = !empty(array_intersect($roles, $professionalRoles));
        
        if ($hasProfessionalRole && method_exists($user, 'isVerifiedByAdmin') && !$user->isVerifiedByAdmin()) {
            $roleName = $this->getRoleDisplayName($user);
            return $this->errorResponse(
                'professional_not_verified',
                'Compte en attente de vérification',
                "Votre compte {$roleName} est en attente de vérification par l'administrateur. Vous recevrez un email de confirmation une fois votre compte approuvé (délai de 24 à 48 heures).",
                'email',
                true
            );
        }

        // Scenario 7: Invalid password (using native PHP password_verify for bcrypt)
        if (!password_verify($password, $user->getPassword())) {
            $attemptsLeft = max(0, 5 - $user->getLoginAttempts());
            if ($attemptsLeft > 0) {
                return $this->errorResponse(
                    'invalid_password',
                    'Mot de passe incorrect',
                    "Le mot de passe que vous avez entré est incorrect. Il vous reste {$attemptsLeft} tentative(s) avant le verrouillage de votre compte.",
                    'password'
                );
            } else {
                return $this->errorResponse(
                    'invalid_password',
                    'Mot de passe incorrect',
                    'Le mot de passe que vous avez entré est incorrect. Votre compte est maintenant verrouillé.',
                    'password',
                    true
                );
            }
        }

        // Success - credentials valid
        return $this->successResponse($user);
    }

    /**
     * Get all possible login scenarios with messages
     */
    public function getLoginScenarios(): array
    {
        return [
            [
                'code' => 'email_empty',
                'title' => 'Email requis',
                'message' => 'Veuillez entrer votre adresse email.',
                'field' => 'email',
                'type' => 'error'
            ],
            [
                'code' => 'password_empty',
                'title' => 'Mot de passe requis',
                'message' => 'Veuillez entrer votre mot de passe.',
                'field' => 'password',
                'type' => 'error'
            ],
            [
                'code' => 'user_not_found',
                'title' => 'Compte non trouvé',
                'message' => 'Aucun compte n\'existe avec cette adresse email. Veuillez vérifier vos identifiants ou créer un nouveau compte.',
                'field' => 'email',
                'type' => 'error'
            ],
            [
                'code' => 'account_locked',
                'title' => 'Compte verrouillé',
                'message' => 'Votre compte a été temporairement verrouillé suite à plusieurs tentatives échouées. Veuillez réessayer plus tard.',
                'field' => 'email',
                'type' => 'error'
            ],
            [
                'code' => 'account_inactive',
                'title' => 'Compte désactivé',
                'message' => 'Votre compte a été désactivé. Veuillez contacter l\'administrateur.',
                'field' => 'email',
                'type' => 'error'
            ],
            [
                'code' => 'professional_not_verified',
                'title' => 'Compte en attente de vérification',
                'message' => 'Votre compte professionnel est en attente de vérification par l\'administrateur.',
                'field' => 'email',
                'type' => 'warning'
            ],
            [
                'code' => 'invalid_password',
                'title' => 'Mot de passe incorrect',
                'message' => 'Le mot de passe que vous avez entré est incorrect.',
                'field' => 'password',
                'type' => 'error'
            ],
            [
                'code' => 'success',
                'title' => 'Connexion réussie',
                'message' => 'Vos identifiants sont corrects. Redirection en cours...',
                'field' => null,
                'type' => 'success'
            ],
        ];
    }

    /**
     * Get role-specific dashboard URL
     */
    public function getDashboardUrl(User $user): string
    {
        $roles = $user->getRoles();
        
        if (in_array('ROLE_ADMIN', $roles)) {
            return '/admin/trail-analytics';
        }
        if (in_array('ROLE_MEDECIN', $roles)) {
            return '/doctor/patient-queue';
        }
        if (in_array('ROLE_COACH', $roles)) {
            return '/coach/dashboard';
        }
        if (in_array('ROLE_NUTRITIONIST', $roles)) {
            return '/nutritionniste/dashboard';
        }
        
        // Default: patient dashboard
        return '/appointment/patient-dashboard';
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayName(User $user): string
    {
        $roles = $user->getRoles();
        
        if (in_array('ROLE_ADMIN', $roles)) {
            return 'Administrateur';
        }
        if (in_array('ROLE_MEDECIN', $roles)) {
            return 'Médecin';
        }
        if (in_array('ROLE_COACH', $roles)) {
            return 'Coach';
        }
        if (in_array('ROLE_NUTRITIONIST', $roles)) {
            return 'Nutritionniste';
        }
        
        return 'Patient';
    }

    private function errorResponse(string $code, string $title, string $message, string $field, bool $blocking = false): JsonResponse
    {
        return new JsonResponse([
            'valid' => false,
            'code' => $code,
            'title' => $title,
            'message' => $message,
            'field' => $field,
            'blocking' => $blocking,
            'type' => $code === 'professional_not_verified' ? 'warning' : 'error',
        ], 200);
    }

    private function successResponse(User $user): JsonResponse
    {
        return new JsonResponse([
            'valid' => true,
            'code' => 'success',
            'title' => 'Connexion réussie',
            'message' => 'Vos identifiants sont corrects.',
            'redirect' => $this->getDashboardUrl($user),
            'role' => $this->getRoleDisplayName($user),
            'type' => 'success',
        ], 200);
    }
    
    /**
     * Validate password strength
     * Returns strength level and validation result
     */
    public function validatePasswordStrength(string $password): array
    {
        $strength = 0;
        $messages = [];
        
        // Check minimum length
        if (strlen($password) >= 8) {
            $strength += 1;
        } else {
            $messages[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }
        
        // Check for uppercase
        if (preg_match('/[A-Z]/', $password)) {
            $strength += 1;
        } else {
            $messages[] = 'Le mot de passe doit contenir au moins une lettre majuscule.';
        }
        
        // Check for lowercase
        if (preg_match('/[a-z]/', $password)) {
            $strength += 1;
        } else {
            $messages[] = 'Le mot de passe doit contenir au moins une lettre minuscule.';
        }
        
        // Check for numbers
        if (preg_match('/[0-9]/', $password)) {
            $strength += 1;
        } else {
            $messages[] = 'Le mot de passe doit contenir au moins un chiffre.';
        }
        
        // Check for special characters
        if (preg_match('/[!@#$%^&*()_+\-=\[\]{};\'\":\\|,.<>\/?]/', $password)) {
            $strength += 1;
        } else {
            $messages[] = 'Le mot de passe doit contenir au moins un caractère spécial.';
        }
        
        // Determine strength level
        $level = 'weak';
        if ($strength >= 5) {
            $level = 'strong';
        } elseif ($strength >= 3) {
            $level = 'medium';
        }
        
        return [
            'valid' => $strength >= 3,
            'strength' => $strength,
            'level' => $level,
            'messages' => $messages,
            'requirements' => [
                ['label' => 'Au moins 8 caractères', 'met' => strlen($password) >= 8],
                ['label' => 'Une lettre majuscule', 'met' => preg_match('/[A-Z]/', $password)],
                ['label' => 'Une lettre minuscule', 'met' => preg_match('/[a-z]/', $password)],
                ['label' => 'Un chiffre', 'met' => preg_match('/[0-9]/', $password)],
                ['label' => 'Un caractère spécial', 'met' => preg_match('/[!@#$%^&*()_+\-=\[\]{};\'\":\\|,.<>\/?]/', $password)],
            ]
        ];
    }
}
