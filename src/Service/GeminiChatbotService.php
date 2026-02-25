<?php
// src/Service/GeminiChatbotService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class GeminiChatbotService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private ?string $apiKey;
    
    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiKey = $_ENV['MISTRAL_API_KEY'] ?? null;
    }
    
    /**
     * Génère une réponse de l'IA avec contexte médical
     */
    public function generateMedicalResponse(string $userMessage, array $conversationHistory = []): array
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'message' => "Le service IA n'est pas configuré. Veuillez ajouter MISTRAL_API_KEY dans le fichier .env",
                'level' => 'info',
                'specialist' => null
            ];
        }
        
        try {
            // Construire le prompt système médical
            $systemPrompt = $this->buildMedicalSystemPrompt();
            
            // Construire le texte complet de la conversation
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt]
            ];
            
            // Ajouter l'historique
            foreach ($conversationHistory as $message) {
                $messages[] = [
                    'role' => $message['role'] === 'user' ? 'user' : 'assistant',
                    'content' => $message['content']
                ];
            }
            
            // Ajouter le message actuel
            $messages[] = ['role' => 'user', 'content' => $userMessage];
            
            // Liste des modèles à essayer dans l'ordre
            $models = [
                'mistral-large-latest',
                'mistral-medium-latest',
                'mistral-small-latest'
            ];
            
            $lastError = null;
            
                        foreach ($models as $model) {
                try {
                    $response = $this->httpClient->request('POST', 
                        'https://api.mistral.ai/v1/chat/completions', [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $this->apiKey
                            ],
                            'json' => [
                                'model' => $model,
                                'messages' => $messages,
                                'temperature' => 0.7,
                                'max_tokens' => 800,
                                'top_p' => 0.95
                            ],
                            'timeout' => 30
                        ]
                    );
                    
                    $statusCode = $response->getStatusCode();
                    
                    if ($statusCode === 200) {
                        $data = $response->toArray();
                        
                        // Extraire le texte de la réponse
                        $responseText = '';
                        if (isset($data['choices'][0]['message']['content'])) {
                            $responseText = $data['choices'][0]['message']['content'];
                        }
                        
                        if (!empty($responseText)) {
                            return [
                                'success' => true,
                                'message' => $responseText,
                                'level' => $this->determineUrgencyLevel($responseText),
                                'specialist' => $this->extractSpecialist($responseText)
                            ];
                        }
                    } elseif ($statusCode === 429) {
                        // Rate limit - try next model
                        $lastError = 'Rate limit exceeded for model: ' . $model;
                        continue;
                    } elseif ($statusCode === 404) {
                        // Model not found - try next model
                        $lastError = 'Model not found: ' . $model;
                        continue;
                    } else {
                        $lastError = 'HTTP error ' . $statusCode . ' for model: ' . $model;
                        continue;
                    }
                    
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                    continue;
                }
            }
            
            // Tous les modèles ont échoué
            $this->logger->error('All Mistral models failed: ' . $lastError);
            
            return [
                'success' => false,
                'message' => "Désolé, le service IA est temporairement indisponible. Veuillez réessayer dans quelques minutes.",
                'level' => 'info',
                'specialist' => null
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Mistral API error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => "Désolé, une erreur technique est survenue. Veuillez réessayer.",
                'level' => 'info',
                'specialist' => null
            ];
        }
    }
    
    /**
     * Construit le prompt système pour guider l'IA
     */
    private function buildMedicalSystemPrompt(): string
    {
        return <<<PROMPT
Tu es un assistant médical WellCare, spécialisé en pré-consultation.

**RÈGLES ABSOLUES :**
1. Ne donne JAMAIS de diagnostic médical
2. Recommande TOUJOURS de consulter un médecin
3. Détecte les URGENCES et alerte immédiatement (⚠️ URGENCE ⚠️)
4. Si patient a pris des médicaments, demande lesquels
5. Propose des remèdes naturels simples (tisanes, repos, hydratation)
6. Pose UNE seule question à la fois. Attends la réponse avant de poser la suivante.
7. Ne liste pas plusieurs questions dans une même réponse.

**CLASSIFICATION :**
- ROUGE (urgence) : douleur thoracique, difficulté respiratoire, perte connaissance
- ORANGE (rapide) : fièvre >39°C, douleur intense, vertiges persistants
- VERT (normal) : symptômes légers, fatigue, toux légère

**CONSEILS NATURELS :**
- Toux → tisane thym-miel
- Fièvre → infusion de sureau, compresses fraîches
- Maux de tête → repos dans le noir, infusion de menthe
- Nausées → gingembre, petites gorgées d'eau

 Réponds en français, de façon claire et bienveillante. N'utilise pas d'emoji.
PROMPT;
    }
    
    /**
     * Détermine le niveau d'urgence
     */
    private function determineUrgencyLevel(string $response): string
    {
        if (stripos($response, 'URGENCE') !== false) {
            return 'rouge';
        }
        if (stripos($response, 'consultez') !== false || stripos($response, 'CONSULTEZ') !== false) {
            return 'orange';
        }
        return 'vert';
    }
    
    /**
     * Extrait le spécialiste recommandé
     */
    private function extractSpecialist(string $response): ?string
    {
        $specialists = [
            'cardiologue', 'généraliste', 'pneumologue', 'neurologue',
            'dermatologue', 'rhumatologue', 'pédiatre', 'ophtalmologue'
        ];
        
        foreach ($specialists as $spec) {
            if (stripos($response, $spec) !== false) {
                return $spec;
            }
        }
        
        return null;
    }
    
    /**
     * Détecte les urgences dans le message
     */
    public function detectEmergency(string $message): bool
    {
        $emergencyKeywords = [
            'poitrine', 'étouffe', 'respire pas', 'inconscient', 
            'évanoui', 'saigne', 'brûlure grave', 'accident'
        ];
        
        $message = strtolower($message);
        
        foreach ($emergencyKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
}
