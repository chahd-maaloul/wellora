<?php

namespace App\Twig;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private AuthorizationCheckerInterface $authorizationChecker
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_role', [$this, 'checkRole']),
            new TwigFunction('user_role', [$this, 'getUserRole']),
            new TwigFunction('is_granted_any', [$this, 'isGrantedAny']),
        ];
    }

    public function checkRole(string $role): bool
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return false;
        }

        // Use authorization checker with 'ROLE_' prefix
        return $this->authorizationChecker->isGranted($role);
    }

    public function getUserRole(): ?string
    {
        $token = $this->tokenStorage->getToken();
        if (!$token || !$token->getUser()) {
            return null;
        }

        // Get roles from token
        $roles = $token->getRoles();
        if (!empty($roles)) {
            // Roles are returned as strings like "ROLE_ADMIN"
            foreach ($roles as $role) {
                if (str_starts_with($role, 'ROLE_')) {
                    return $role;
                }
            }
            return $roles[0];
        }

        return null;
    }

    public function isGrantedAny(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->checkRole($role)) {
                return true;
            }
        }
        return false;
    }
}
