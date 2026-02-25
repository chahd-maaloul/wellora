<?php
// src/Command/TestNotificationCommand.php

namespace App\Command;

use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-notification',
    description: 'Teste le système de notification (email et SMS)',
)]
class TestNotificationCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('consultation-id', InputArgument::OPTIONAL, 'ID de la consultation à tester')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Type de notification (confirmation ou rappel)', 'confirmation')
            ->addOption('test-email', null, InputOption::VALUE_OPTIONAL, 'Email de test (remplace celui du patient)')
            ->addOption('list', 'l', InputOption::VALUE_NONE, 'Lister les consultations disponibles');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Lister les consultations si demandé
        if ($input->getOption('list')) {
            return $this->listConsultations($io);
        }
        
        $consultationId = $input->getArgument('consultation-id');
        
        if (!$consultationId) {
            $io->error('Veuillez spécifier un ID de consultation ou utiliser --list pour voir les consultations disponibles.');
            $io->text('Usage: php bin/console app:test-notification <consultation-id> [--type=confirmation|rappel]');
            return Command::INVALID;
        }
        
        // Récupérer la consultation
        $consultation = $this->em->find('App\Entity\Consultation', $consultationId);
        
        if (!$consultation) {
            $io->error(sprintf('Consultation #%d non trouvée', $consultationId));
            return Command::FAILURE;
        }
        
        $type = $input->getOption('type');
        $patient = $consultation->getPatient();
        
        $io->title('🧪 Test du système de notification');
        $io->definitionList(
            ['Consultation ID' => $consultation->getId()],
            ['Patient' => $patient ? sprintf('%s %s', $patient->getFirstName(), $patient->getLastName()) : 'N/A'],
            ['Email patient' => $patient ? $patient->getEmail() : 'N/A'],
            ['Téléphone patient' => $patient ? $patient->getPhone() : 'N/A'],
            ['Date consultation' => $consultation->getDateConsultation() ? $consultation->getDateConsultation()->format('d/m/Y') : 'N/A'],
            ['Type de test' => $type]
        );
        
        if (!$patient) {
            $io->error('Cette consultation n\'a pas de patient associé.');
            return Command::FAILURE;
        }
        
        if (!$patient->getEmail() && !$input->getOption('test-email')) {
            $io->warning('Le patient n\'a pas d\'email. Utilisez --email=votre@email.com pour tester.');
        }
        
        try {
            if ($type === 'rappel') {
                $io->section('📧 Envoi d\'un rappel de rendez-vous...');
                $this->notificationService->envoyerRappel($consultation);
            } else {
                $io->section('📧 Envoi d\'une confirmation de rendez-vous...');
                $this->notificationService->envoyerConfirmation($consultation);
            }
            
            $io->success(sprintf('Notification de type "%s" envoyée avec succès !', $type));
            $io->note('Vérifiez votre boîte email (et les spams).');
            
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'envoi: ' . $e->getMessage());
            $io->note('Vérifiez votre configuration MAILER_DSN dans .env');
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
    
    private function listConsultations(SymfonyStyle $io): int
    {
        $consultations = $this->em->createQueryBuilder()
            ->select('c.id, c.date_consultation, c.status, p.firstName, p.lastName, p.email')
            ->from('App\Entity\Consultation', 'c')
            ->leftJoin('c.patient', 'p')
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
        
        if (empty($consultations)) {
            $io->warning('Aucune consultation trouvée dans la base de données.');
            return Command::SUCCESS;
        }
        
        $io->title('📋 Liste des consultations disponibles');
        
        $tableRows = [];
        foreach ($consultations as $c) {
            $tableRows[] = [
                $c['id'],
                $c['date_consultation'] ? $c['date_consultation']->format('d/m/Y') : 'N/A',
                $c['status'] ?? 'N/A',
                trim(($c['firstName'] ?? '') . ' ' . ($c['lastName'] ?? '')) ?: 'N/A',
                $c['email'] ?? 'N/A'
            ];
        }
        
        $io->table(
            ['ID', 'Date', 'Statut', 'Patient', 'Email'],
            $tableRows
        );
        
        $io->text('Utilisez: php bin/console app:test-notification <ID> [--type=confirmation|rappel]');
        
        return Command::SUCCESS;
    }
}
