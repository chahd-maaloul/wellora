<?php
// src/Security/Voter/ConversationVoter.php

namespace App\Security\Voter;

use App\Entity\Conversation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ConversationVoter extends Voter
{
    public const VIEW = 'view';
    public const SEND = 'send';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::SEND])
            && $subject instanceof Conversation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Conversation $conversation */
        $conversation = $subject;

        return match($attribute) {
            self::VIEW, self::SEND => $this->canAccess($conversation, $user),
            default => false,
        };
    }

    private function canAccess(Conversation $conversation, UserInterface $user): bool
    {
        // L'utilisateur peut accéder à la conversation s'il est le patient ou le coach
        return $conversation->getPatient() === $user 
            || $conversation->getCoach() === $user;
    }
}