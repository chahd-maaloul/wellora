<?php
// src/Command/ChatbotTestCommand.php

namespace App\Command;

use App\Service\GeminiChatbotService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:chatbot-test',
    description: 'Teste le chatbot Gemini',
)]
class ChatbotTestCommand extends Command
{
    public function __construct(
        private GeminiChatbotService $chatbotService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('message', InputArgument::REQUIRED, 'Message à envoyer au chatbot');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $message = $input->getArgument('message');
        
        $io->title('Test du chatbot Gemini');
        $io->text('Message: ' . $message);
        $io->text('Envoi en cours...');
        
        try {
            $result = $this->chatbotService->generateMedicalResponse($message);
            
            $io->section('Résultat');
            $io->definitionList(
                ['Succès' => $result['success'] ? 'Oui' : 'Non'],
                ['Niveau' => $result['level'] ?? 'N/A'],
                ['Spécialiste' => $result['specialist'] ?? 'N/A'],
                ['Message' => $result['message']]
            );
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('Erreur: ' . $e->getMessage());
            $io->text('Trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
