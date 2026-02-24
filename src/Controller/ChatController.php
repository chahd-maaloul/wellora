<?php
// src/Controller/ChatController.php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\GoalRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/chat')]
class ChatController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ConversationRepository $conversationRepo,
        private MessageRepository $messageRepo,
        private GoalRepository $goalRepo,
        private ?HubInterface $hub = null
    ) {}

    #[Route('/coach', name: 'app_chat_coach')]
    #[IsGranted('ROLE_COACH')]
    public function coachChat(): Response
    {
        /** @var User $coach */
        $coach = $this->getUser();
        
        $conversations = $this->conversationRepo->findUserConversationsWithGoals($coach);
        $unreadCounts = $this->messageRepo->getUnreadCountByConversation($coach);
        
        $formattedConversations = [];
        foreach ($conversations as $conv) {
            $lastMessage = $this->messageRepo->findLastMessage($conv);
            $patient = $conv->getPatient();
            
            if (!$patient) continue;
            
            $formattedConversations[] = [
                'id' => $conv->getId(),
                'client' => $patient->getFirstName() . ' ' . $patient->getLastName(),
                'avatar' => strtoupper(substr($patient->getFirstName(), 0, 1)),
                'lastMessage' => $lastMessage ? 
                    (strlen($lastMessage->getContent()) > 50 ? 
                        substr($lastMessage->getContent(), 0, 50) . '...' : 
                        $lastMessage->getContent()) 
                    : 'No messages yet',
                'time' => $lastMessage ? $lastMessage->getSentAt()->format('H:i') : '',
                'unread' => $unreadCounts[$conv->getId()] ?? 0,
                'online' => false,
                'goalId' => $conv->getGoal()?->getId(),
                'goalTitle' => $conv->getGoal()?->getTitle(),
                'goalStatus' => $conv->getGoal()?->getStatus(),
            ];
        }
        
        return $this->render('coach/communication-hub.html.twig', [
            'pageTitle' => 'Communication Hub - Coach',
            'conversations' => $formattedConversations,
        ]);
    }

    #[Route('/patient', name: 'app_chat_patient')]
    #[IsGranted('ROLE_PATIENT')]
    public function patientChat(): Response
    {
        /** @var User $patient */
        $patient = $this->getUser();
        
        // Récupérer toutes les conversations du patient
        $conversations = $this->conversationRepo->findUserConversationsWithGoals($patient);
        
        // Créer automatiquement les conversations manquantes
        if (empty($conversations)) {
            $goals = $this->goalRepo->findBy(['patient' => $patient]);
            foreach ($goals as $goal) {
                if ($goal->getCoachId()) {
                    try {
                        $this->conversationRepo->findOrCreateForGoal($goal);
                    } catch (\Exception $e) {
                        // Silence is golden
                    }
                }
            }
            $conversations = $this->conversationRepo->findUserConversationsWithGoals($patient);
        }
        
        $unreadCounts = $this->messageRepo->getUnreadCountByConversation($patient);
        
        $formattedConversations = [];
        foreach ($conversations as $conv) {
            $lastMessage = $this->messageRepo->findLastMessage($conv);
            $coach = $conv->getCoach();
            $goal = $conv->getGoal();
            
            if (!$coach) continue;
            
            $firstName = $coach->getFirstName() ?? 'Coach';
            $lastName = $coach->getLastName() ?? '';
            
            $formattedConversations[] = [
                'id' => $conv->getId(),
                'name' => trim($firstName . ' ' . $lastName),
                'avatar' => strtoupper(substr($firstName, 0, 1)),
                'lastMessage' => $lastMessage ? 
                    (strlen($lastMessage->getContent()) > 50 ? 
                        substr($lastMessage->getContent(), 0, 50) . '...' : 
                        $lastMessage->getContent()) 
                    : 'No messages yet',
                'time' => $lastMessage ? $lastMessage->getSentAt()->format('H:i') : '',
                'unread' => $unreadCounts[$conv->getId()] ?? 0,
                'online' => false,
                'goalId' => $goal?->getId(),
                'goalTitle' => $goal?->getTitle() ?? null,
                'goalStatus' => $goal?->getStatus() ?? null,
            ];
        }
        
        return $this->render('fitness/coach-communication.html.twig', [
            'conversations' => $formattedConversations,
        ]);
    }

    #[Route('/conversation/{id}', name: 'app_chat_conversation', methods: ['GET'])]
    #[IsGranted('view', 'conversation')]
    public function getConversation(Conversation $conversation): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Vérification de sécurité
        if (!$this->isGranted('view', $conversation)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        // Marquer les messages comme lus
        $this->messageRepo->markAsRead($conversation, $user);
        
        // Récupérer tous les messages
        $messages = $this->messageRepo->findMessagesByConversation($conversation);
        
        $formattedMessages = [];
        foreach ($messages as $message) {
            $sender = $message->getSender();
            
            if ($sender === $user) {
                $senderType = 'me';
            } else {
                $senderType = ($user === $conversation->getCoach()) ? 'patient' : 'coach';
            }
            
            $formattedMessages[] = [
                'id' => $message->getId(),
                'sender' => $senderType,
                'content' => $message->getContent(),
                'time' => $message->getSentAt()->format('H:i'),
                'date' => $message->getSentAt()->format('Y-m-d'),
            ];
        }
        
        $otherParticipant = $conversation->getPatient() === $user 
            ? $conversation->getCoach() 
            : $conversation->getPatient();
        
        if (!$otherParticipant) {
            return $this->json(['error' => 'Participant not found'], 404);
        }
        
        return $this->json([
            'conversationId' => $conversation->getId(),
            'messages' => $formattedMessages,
            'participant' => [
                'name' => trim($otherParticipant->getFirstName() . ' ' . ($otherParticipant->getLastName() ?? '')),
                'avatar' => strtoupper(substr($otherParticipant->getFirstName() ?? 'U', 0, 1)),
            ],
        ]);
    }

    #[Route('/conversation/{id}/last-message', name: 'app_chat_last_message', methods: ['GET'])]
    #[IsGranted('view', 'conversation')]
    public function getLastMessage(Conversation $conversation): JsonResponse
    {
        $lastMessage = $this->messageRepo->findLastMessage($conversation);
        
        if (!$lastMessage) {
            return $this->json(['content' => 'No messages yet']);
        }
        
        $sender = $lastMessage->getSender();
        
        return $this->json([
            'id' => $lastMessage->getId(),
            'content' => $lastMessage->getContent(),
            'sender' => $sender->getFirstName(),
            'time' => $lastMessage->getSentAt()->format('H:i'),
        ]);
    }

    #[Route('/send/{id}', name: 'app_chat_send', methods: ['POST'])]
    #[IsGranted('send', 'conversation')]
    public function sendMessage(Conversation $conversation, Request $request): JsonResponse
    {
        /** @var User $sender */
        $sender = $this->getUser();
        
        // Vérification de sécurité
        if (!$this->isGranted('send', $conversation)) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        $content = $request->request->get('content');
        
        if (empty($content)) {
            return $this->json(['error' => 'Message content is required'], 400);
        }
        
        if (strlen($content) > 5000) {
            return $this->json(['error' => 'Message is too long'], 400);
        }
        
        // Nettoyer le contenu
        $content = trim(strip_tags($content));
        
        // Créer et sauvegarder le message
        $message = new Message();
        $message->setConversation($conversation);
        $message->setSender($sender);
        $message->setContent($content);
        $message->setSentAt(new \DateTimeImmutable());
        $message->setIsRead(false);
        
        $conversation->setLastMessageAt(new \DateTime());
        
        $this->entityManager->persist($message);
        $this->entityManager->flush();
        
        // Déterminer le destinataire
        $recipient = $conversation->getPatient() === $sender 
            ? $conversation->getCoach() 
            : $conversation->getPatient();
        
        // Publier sur Mercure si disponible
        if ($this->hub && $recipient) {
            try {
                $update = new Update(
                    [
                        sprintf('/chat/%d', $conversation->getId()),
                        sprintf('/notifications/%s', $recipient->getUuid())
                    ],
                    json_encode([
                        'type' => 'new_message',
                        'conversationId' => $conversation->getId(),
                        'message' => [
                            'id' => $message->getId(),
                            'sender' => 'me',
                            'content' => $message->getContent(),
                            'time' => $message->getSentAt()->format('H:i'),
                        ],
                        'notification' => [
                            'title' => 'Nouveau message',
                            'message' => substr($content, 0, 100),
                            'sender' => $sender->getFirstName(),
                            'time' => (new \DateTime())->format('H:i')
                        ]
                    ])
                );
                
                $this->hub->publish($update);
                
            } catch (\Exception $e) {
                // Ne pas bloquer l'envoi du message
                error_log('Mercure error: ' . $e->getMessage());
            }
        }
        
        return $this->json([
            'id' => $message->getId(),
            'sender' => 'me',
            'content' => $message->getContent(),
            'time' => $message->getSentAt()->format('H:i'),
        ]);
    }

    #[Route('/unread/count', name: 'app_chat_unread_count', methods: ['GET'])]
    public function getUnreadCount(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }
        
        $totalUnread = $this->messageRepo->countUnreadForUser($user);
        $unreadByConversation = $this->messageRepo->getUnreadCountByConversation($user);
        
        return $this->json([
            'total' => $totalUnread,
            'byConversation' => $unreadByConversation,
        ]);
    }
}