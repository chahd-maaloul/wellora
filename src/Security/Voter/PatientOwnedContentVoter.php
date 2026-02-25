<?php

namespace App\Security\Voter;

use App\Entity\CommentairePublication;
use App\Entity\ParcoursDeSante;
use App\Entity\Patient;
use App\Entity\PublicationParcours;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class PatientOwnedContentVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, ['EDIT', 'DELETE'], true)) {
            return false;
        }

        return $subject instanceof ParcoursDeSante
            || $subject instanceof PublicationParcours
            || $subject instanceof CommentairePublication;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        if (!$user instanceof Patient) {
            return false;
        }

        $owner = $this->resolveOwner($subject);

        return $owner !== null && $owner->getUuid() === $user->getUuid();
    }

    private function resolveOwner(mixed $subject): ?Patient
    {
        if ($subject instanceof ParcoursDeSante) {
            return $subject->getOwnerPatient();
        }

        if ($subject instanceof PublicationParcours) {
            return $subject->getOwnerPatient();
        }

        if ($subject instanceof CommentairePublication) {
            return $subject->getOwnerPatient();
        }

        return null;
    }
}
