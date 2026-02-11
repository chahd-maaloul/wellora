<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->userRepository->findOneByEmail($identifier);

        if (!$user) {
            throw new \Symfony\Component\Security\Core\Exception\BadCredentialsException('Email ou mot de passe incorrect');
        }

        if ($user->isLocked()) {
            $lockedUntil = $user->getLockedUntil();
            $minutes = ceil(($lockedUntil->getTimestamp() - time()) / 60);
            throw new LockedException(sprintf('Votre compte est verrouillé. Réessayez dans %d minutes.', $minutes));
        }

        if (!$user->isIsActive()) {
            throw new LockedException('Votre compte est désactivé. Veuillez contacter l\'administrateur.');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}
