<?php
// src/Controller/GeminiChatbotController.php

namespace App\Controller;

use App\Service\GeminiChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ChatbotAvanceController extends AbstractController
{
    private GeminiChatbotService $chatbotService;
    
    public function __construct(GeminiChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }
    
    #[Route('/chatbot-ia', name: 'app_chatbot_ia')]
    public function index(): Response
    {
        return $this->render('appointment/chatbot.html.twig');
    }
    
    #[Route('/chatbot-ia/message', name: 'app_chatbot_ia_message', methods: ['POST'])]
    public function message(Request $request, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = trim($data['message'] ?? '');
        
        if (empty($message)) {
            return $this->json([
                'message' => "Veuillez écrire un message.",
                'level' => 'info'
            ]);
        }
        
        // 1. Vérifier les urgences immédiates
        if ($this->chatbotService->detectEmergency($message)) {
            return $this->json([
                'message' => "⚠️ **URGENCE DÉTECTÉE** ⚠️\n\n" .
                            "Votre message mentionne des symptômes graves.\n\n" .
                            "🔴 **ACTION IMMÉDIATE :** Appelez le **15** (SAMU) sans attendre.\n\n" .
                            "En attendant les secours :\n" .
                            "• Ne prenez pas de médicament\n" .
                            "• Allongez-vous si possible\n" .
                            "• Ne restez pas seul(e)",
                'level' => 'rouge'
            ]);
        }
        
        // 2. Récupérer l'historique
        $history = $session->get('chat_history', []);
        
        // 3. Appeler l'IA
        $response = $this->chatbotService->generateMedicalResponse($message, $history);
        
        if ($response['success']) {
            // 4. Sauvegarder dans l'historique
            $history[] = ['role' => 'user', 'content' => $message];
            $history[] = ['role' => 'model', 'content' => $response['message']];
            
            // Garder seulement les 10 derniers messages
            if (count($history) > 20) {
                $history = array_slice($history, -20);
            }
            $session->set('chat_history', $history);
        }
        
        return $this->json([
            'message' => $response['message'],
            'level' => $response['level']
        ]);
    }
    
    #[Route('/chatbot-ia/reset', name: 'app_chatbot_ia_reset', methods: ['POST'])]
    public function reset(SessionInterface $session): JsonResponse
    {
        $session->remove('chat_history');
        
        return $this->json([
            'message' => "Conversation réinitialisée. Comment puis-je vous aider ?",
            'level' => 'info'
        ]);
    }
}