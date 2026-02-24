<?php
// src/Repository/MessageRepository.php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }
 public function findMessagesAfter(Conversation $conversation, int $lastMessageId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.conversation = :conversation')
            ->andWhere('m.id > :lastMessageId')
            ->setParameter('conversation', $conversation)
            ->setParameter('lastMessageId', $lastMessageId)
            ->orderBy('m.sentAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
    /**
     * Récupère tous les messages d'une conversation
     */
    public function findMessagesByConversation(Conversation $conversation): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.sentAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Marque les messages comme lus
     */
    public function markAsRead(Conversation $conversation, User $reader): int
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            UPDATE message 
            SET is_read = 1, read_at = :now
            WHERE conversation_id = :conversation_id 
            AND sender_uuid != :reader_uuid 
            AND is_read = 0
        ';
        
        return $conn->executeStatement($sql, [
            'conversation_id' => $conversation->getId(),
            'reader_uuid' => $reader->getUuid(),
            'now' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Récupère les compteurs non lus par conversation
     */
    public function getUnreadCountByConversation(User $user): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT m.conversation_id, COUNT(m.id) as unread_count
            FROM message m
            INNER JOIN conversation c ON m.conversation_id = c.id
            WHERE (c.patient_uuid = :uuid OR c.coach_uuid = :uuid)
            AND m.sender_uuid != :uuid
            AND m.is_read = 0
            GROUP BY m.conversation_id
        ';
        
        $results = $conn->executeQuery($sql, [
            'uuid' => $user->getUuid()
        ])->fetchAllAssociative();
        
        $unreadCounts = [];
        foreach ($results as $result) {
            $unreadCounts[(int) $result['conversation_id']] = (int) $result['unread_count'];
        }
        
        return $unreadCounts;
    }

    /**
     * Compte tous les messages non lus
     */
    public function countUnreadForUser(User $user): int
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT COUNT(m.id) as total
            FROM message m
            INNER JOIN conversation c ON m.conversation_id = c.id
            WHERE (c.patient_uuid = :uuid OR c.coach_uuid = :uuid)
            AND m.sender_uuid != :uuid
            AND m.is_read = 0
        ';
        
        $result = $conn->executeQuery($sql, [
            'uuid' => $user->getUuid()
        ])->fetchAssociative();
        
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Récupère le dernier message d'une conversation
     */
    public function findLastMessage(Conversation $conversation): ?Message
    {
        return $this->createQueryBuilder('m')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.sentAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}