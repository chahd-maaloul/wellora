<?php

namespace App\Controller;

use App\Service\AIPythonService;
use App\Entity\Goal;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/patient/ai')]
#[IsGranted('ROLE_PATIENT')]
class PatientAIController extends AbstractController
{
    public function __construct(
        private AIPythonService $aiPythonService
    ) {}

    #[Route('/generate', name: 'patient_ai_generate_form')]
public function generateForm(): Response
{
    // Vérifier que le service Python est accessible
    $isHealthy = $this->aiPythonService->healthCheck();
    
    // Si le service n'est pas accessible, essayer de le lancer
    if (!$isHealthy) {
        $pythonAiPath = dirname(__DIR__, 2) . '/python-ai';
        $pythonScript = $pythonAiPath . '/app.py';
        
        // Vérifications de débogage
        $debug = [];
        $debug[] = "Chemin Python-AI: " . $pythonAiPath;
        $debug[] = "Script existe: " . (file_exists($pythonScript) ? 'OUI' : 'NON');
        
        // Vérifier si Python est installé
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $pythonCheck = shell_exec('where python 2>&1');
            $debug[] = "Python trouvé: " . ($pythonCheck ? $pythonCheck : 'NON');
        } else {
            $pythonCheck = shell_exec('which python3 2>&1');
            $debug[] = "Python3 trouvé: " . ($pythonCheck ? $pythonCheck : 'NON');
        }
        
        // Vérifier si le port 5000 est déjà utilisé
        $connection = @fsockopen('localhost', 5000, $errno, $errstr, 1);
        if ($connection) {
            $debug[] = "Port 5000 déjà utilisé!";
            fclose($connection);
        } else {
            $debug[] = "Port 5000 libre, tentative de lancement...";
            
            // Créer un fichier de log pour voir les erreurs
            $logFile = $pythonAiPath . '/startup.log';
            
            // Lancer le serveur Python avec redirection des erreurs
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $command = sprintf(
                    'cd /d %s && start /B python app.py > %s 2>&1',
                    $pythonAiPath,
                    $logFile
                );
                pclose(popen($command, 'r'));
            } else {
                $command = sprintf(
                    'cd %s && nohup python3 app.py > %s 2>&1 &',
                    $pythonAiPath,
                    $logFile
                );
                exec($command);
            }
            
            sleep(3); // Attendre le démarrage
            
            // Vérifier le fichier de log
            if (file_exists($logFile)) {
                $logContent = file_get_contents($logFile);
                $debug[] = "Contenu du log: " . substr($logContent, 0, 500);
            }
            
            // Revérifier si le service est maintenant accessible
            $isHealthy = $this->aiPythonService->healthCheck();
            
            if ($isHealthy) {
                $this->addFlash('success', '✅ Service IA démarré avec succès!');
            } else {
                $this->addFlash('warning', '⚠️ Échec du démarrage. Vérifiez les logs.');
                
                // Essayer de lancer manuellement en premier plan pour voir l'erreur
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $manualCommand = sprintf('cd /d %s && python app.py', $pythonAiPath);
                    $debug[] = "Commande manuelle: " . $manualCommand;
                }
            }
        }
        
        // Ajouter les infos de débogage à la session
$this->addFlash('debug', implode('<br>', $debug));    }
    
    return $this->render('patient/ai_generate_form.html.twig', [
        'isHealthy' => $isHealthy
    ]);
}
    #[Route('/generate/program', name: 'patient_ai_generate_program', methods: ['POST'])]
    public function generateProgram(Request $request): Response
    {
        $userRequest = $request->request->get('user_request');
        
        // Patient connecté
        $patient = $this->getUser();
        
        try {
            // Générer et sauvegarder le programme
            $result = $this->aiPythonService->generateAndSaveProgram(
                $userRequest, 
                $patient
            );
            
            $this->addFlash('success', '✅ Votre programme a été généré avec succès !');
            
            return $this->redirectToRoute('patient_view_program', [
                'id' => $result['goal']->getId()
            ]);
            
        } catch (\Exception $e) {
            $this->addFlash('error', '❌ Erreur : ' . $e->getMessage());
            return $this->redirectToRoute('patient_ai_generate_form');
        }
    }

    #[Route('/program/{id}', name: 'patient_view_program')]
    public function viewProgram(Goal $goal): Response
    {
        // Vérifier que ce goal appartient bien au patient connecté
        $patient = $this->getUser();
        if ($goal->getPatient() !== $patient) {
            throw $this->createAccessDeniedException('Ce programme ne vous appartient pas');
        }
        
        return $this->render('patient/program_view.html.twig', [
            'goal' => $goal,
            'dailyPlans' => $goal->getDailyplan()
        ]);
    }
}