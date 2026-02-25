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