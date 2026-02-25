<?php
// src/Command/EnvoyerRappelsCommand.php

namespace App\Command;

use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:envoyer-rappels',
    description: 'Envoie les rappels de rendez-vous pour le lendemain',
)]
class EnvoyerRappelsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Définir la plage horaire pour demain
        $demain = new \DateTime('+1 day');
        $debut = (clone $demain)->setTime(0, 0, 0);
        $fin = (clone $demain)->setTime(23, 59, 59);
        
        $io->title('Envoi des rappels de rendez-vous');
        $io->text(sprintf('Recherche des consultations entre %s et %s', 
            $debut->format('d/m/Y H:i'), 
            $fin->format('d/m/Y H:i')
        ));
        
        // Récupérer les consultations confirmées de demain
        $consultations = $this->em->createQueryBuilder()
            ->select('c')
            ->from('App\Entity\Consultation', 'c')
            ->where('c.date_consultation BETWEEN :debut AND :fin')
            ->andWhere('c.status = :statut')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('statut', 'confirmed')
            ->getQuery()
            ->getResult();
        
        $io->text(sprintf('Trouvé %d consultation(s) pour demain', count($consultations)));
        
        $compte = 0;
        $erreurs = 0;
        
        foreach ($consultations as $consultation) {
            try {
                $patient = $consultation->getPatient();
                $io->text(sprintf('Envoi rappel pour %s %s', 
                    $patient ? $patient->getFirstName() : 'N/A',
                    $patient ? $patient->getLastName() : 'N/A'
                ));
                
                $this->notificationService->envoyerRappel($consultation);
                $compte++;
                
            } catch (\Exception $e) {
                $io->error(sprintf('Erreur pour la consultation #%d: %s', $consultation->getId(), $e->getMessage()));
                $erreurs++;
            }
        }
        
        $io->success(sprintf('%d rappel(s) envoyé(s), %d erreur(s)', $compte, $erreurs));
        
        return Command::SUCCESS;
    }
}
