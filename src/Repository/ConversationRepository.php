<?php
// src/Repository/ConversationRepository.php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Goal;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * Récupère toutes les conversations d'un utilisateur avec SQL natif
     */
    public function findUserConversationsWithGoals(User $user): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $uuid = $user->getUuid();
        
        // CORRIGÉ: 'users' au lieu de 'user' (avec un 's')
        $sql = '
            SELECT 
                c.id,
                c.patient_uuid,
                c.coach_uuid,
                c.goal_id,
                c.created_at,
                c.last_message_at,
                g.id as goal_id,
                g.title as goal_title,
                g.status as goal_status,
                p.uuid as patient_uuid,
                p.first_name as patient_first_name,
                p.last_name as patient_last_name,
                co.uuid as coach_uuid,
                co.first_name as coach_first_name,
                co.last_name as coach_last_name
            FROM conversation c
            LEFT JOIN goal g ON c.goal_id = g.id
            LEFT JOIN users p ON c.patient_uuid = p.uuid
            LEFT JOIN users co ON c.coach_uuid = co.uuid
            WHERE c.patient_uuid = :uuid OR c.coach_uuid = :uuid
            ORDER BY COALESCE(c.last_message_at, c.created_at) DESC
        ';
        
        $result = $conn->executeQuery($sql, ['uuid' => $uuid])->fetchAllAssociative();
        
        // Convertir les résultats en entités Conversation
        $conversations = [];
        foreach ($result as $row) {
            $conversation = $this->find($row['id']);
            if ($conversation) {
                $conversations[] = $conversation;
            }
        }
        
        return $conversations;
    }

    /**
     * Version simplifiée sans les détails des goals
     */
    public function findUserConversationsSimple(User $user): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        // CORRIGÉ: Pas de jointure avec users ici, donc pas de problème
        $sql = '
            SELECT c.* 
            FROM conversation c 
            WHERE c.patient_uuid = :uuid 
            OR c.coach_uuid = :uuid 
            ORDER BY COALESCE(c.last_message_at, c.created_at) DESC
        ';
        
        $result = $conn->executeQuery($sql, ['uuid' => $user->getUuid()])->fetchAllAssociative();
        
        $conversations = [];
        foreach ($result as $row) {
            $conversation = $this->find($row['id']);
            if ($conversation) {
                $conversations[] = $conversation;
            }
        }
        
        return $conversations;
    }

    /**
     * Trouve une conversation par goal
     */
    public function findByGoal(Goal $goal): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->where('c.goal = :goal')
            ->setParameter('goal', $goal)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve ou crée une conversation pour un goal
     */
    public function findOrCreateForGoal(Goal $goal): Conversation
    {
        $entityManager = $this->getEntityManager();
        
        $existingConversation = $this->findByGoal($goal);
        
        if ($existingConversation) {
            return $existingConversation;
        }
        
        $patient = $goal->getPatient();
        $coach = $entityManager->getRepository(User::class)->find($goal->getCoachId());
            
        if (!$patient || !$coach) {
            throw new \Exception('Patient or coach not found for goal #' . $goal->getId());
        }
        
        $conversation = new Conversation();
        $conversation->setPatient($patient);
        $conversation->setCoach($coach);
        $conversation->setGoal($goal);
        $conversation->setCreatedAt(new \DateTimeImmutable());
        
        $entityManager->persist($conversation);
        $entityManager->flush();
        
        return $conversation;
    }
    public function findByCoachAndPatient(User $coach, User $patient): ?Conversation
{
    return $this->createQueryBuilder('c')
        ->where('c.coach = :coach')
        ->andWhere('c.patient = :patient')
        ->setParameter('coach', $coach)
        ->setParameter('patient', $patient)
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}
}