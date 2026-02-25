<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Form\GoalType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

final class GoalController extends AbstractController
{
    #[Route('/goal/new', name: 'fitness_goal_new', methods: ['GET', 'POST'])]
    public function new(
        ManagerRegistry $doctrine, 
        Request $request,
        UserRepository $userRepository
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $em = $doctrine->getManager();
        $goal = new Goal();
        $goal->setPatient($this->getUser());
        
        // Initialiser les valeurs par défaut
        $goal->setStatus('PENDING');
        $goal->setProgress(0);
        $goal->setDate(new \DateTime());

        // Récupérer tous les coachs actifs
        $coaches = $userRepository->findAllCoaches();

        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($goal);
            $em->flush();
            
            $this->addFlash('success', 'Objectif créé avec succès !');
            return $this->redirectToRoute('fitness_goal_show');
        }

        return $this->render('fitness/goal/goal-wizard.html.twig', [
            'page_title' => 'Create SMART Goal',
            'form' => $form->createView(),
            'coaches' => $coaches,
        ]);
    }
    
    /// Dans GoalController.php - méthode show()
#[Route('/goal/show', name: 'fitness_goal_show', methods: ['GET'])]
public function show(ManagerRegistry $doctrine, UserRepository $userRepository): Response
{
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    $em = $doctrine->getManager();
    $goalRepository = $em->getRepository(Goal::class);
    $goals = $goalRepository->findBy(['patient' => $this->getUser()]);
    
    // Pour chaque goal, récupérer les informations du coach si coachId existe
    $goalsWithCoach = [];
    foreach ($goals as $goal) {
        $coachInfo = null;
        if ($goal->getCoachId()) {
            $coachInfo = $userRepository->findOneBy(['uuid' => $goal->getCoachId()]);
        }
        $goalsWithCoach[] = [
            'goal' => $goal,
            'coach' => $coachInfo
        ];
    }
    
    return $this->render('fitness/goal/show-goal.html.twig', [
        'page_title' => 'Goal Details',
        'goalsWithCoach' => $goalsWithCoach, // Nouvelle variable avec les infos coach
        'goals' => $goals, // Garder l'ancienne variable pour la compatibilité
    ]);
}
     #[Route('/goal/delete/{id}', name: 'delete_goal', methods: ['GET'])]
    public function delete(ManagerRegistry $m,  $id)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $em = $m->getManager();
        $goal = $em->getRepository(Goal::class)->findOneBy(['id' => $id]);
        if (!$goal || $goal->getPatient() !== $this->getUser()) {
            throw $this->createNotFoundException('Goal not found');
        }
        $em->remove($goal);
        $em->flush();
        return $this->redirectToRoute('fitness_goal_show');

    }
    
     #[Route('/goal/update/{id}', name: 'fitness_goal_edit', methods: ['GET', 'POST'])]
    public function update(Request $request, ManagerRegistry $m, UserRepository $userRepository, $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $em = $m->getManager();
        $goal = $em->getRepository(Goal::class)->find($id);

        // Vérifier que l'utilisateur est bien le propriétaire du goal
        if (!$goal || $goal->getPatient() !== $this->getUser()) {
            throw $this->createNotFoundException('Goal not found');
        }

        // Récupérer tous les coachs pour le select
        $coaches = $userRepository->findAllCoaches();

        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            
            $this->addFlash('success', 'Goal updated successfully!');
            return $this->redirectToRoute('fitness_goal_show');
        }

        return $this->render('fitness/goal/update-goal.html.twig', [
            'form' => $form->createView(),
            'goal' => $goal,
            'coaches' => $coaches,
        ]);
    }
}