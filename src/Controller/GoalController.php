<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Form\GoalType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GoalController extends AbstractController
{
    #[Route('/goal/new', name: 'fitness_goal_new', methods: ['GET', 'POST'])]
    public function goal(ManagerRegistry $m, Request $req): Response
    {  
        $em = $m->getManager();  
        $goal = new Goal();  
        
        // This should work if your controller is properly set up
        $form = $this->createForm(GoalType::class, $goal);
        
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            
            
            $em->persist($goal);
            $em->flush();
            return $this->redirectToRoute('fitness/dashboard');
        }
        
        return $this->render('fitness/goal/goal-wizard.html.twig', [
            'page_title' => 'Create SMART Goal',
            'form' => $form->createView(),        
        ]);  
    }
    
    // AJOUTER CETTE MÉTHODE POUR AFFICHER UN OBJECTIF
    #[Route('/goal/show', name: 'fitness_goal_show', methods: ['GET'])]
    public function show(ManagerRegistry $m): Response
    {
        $em = $m->getManager();
        $goal = $em->getRepository(Goal::class);
        if (!$goal) {
            throw $this->createNotFoundException('Goal not found');
        }
        $goals = $goal->findAll();
        return $this->render('fitness/goal/show-goal.html.twig', [
            'page_title' => 'Goal Details',
            'goals' => $goals,
        ]);  
    }
     #[Route('/goal/delete/{id}', name: 'delete_goal', methods: ['GET'])]
    public function delete(ManagerRegistry $m,  $id)
    {

        $em = $m->getManager();
        $goal = $em->getRepository(Goal::class)->findOneBy(['id' => $id]);
        if (!$goal) {
            throw $this->createNotFoundException('Goal not found');
        }
        $em->remove($goal);
        $em->flush();
        return $this->redirectToRoute('fitness_goal_show');

    } 
    
    #[Route('/goal/update/{id}', name: 'fitness_goal_edit', methods: ['GET', 'POST'])]
    public function update(Request $request, ManagerRegistry $m, $id): Response
    {
        $em = $m->getManager();
        $goal = $em->getRepository(Goal::class)->find($id); // CORRECTION ICI !

        if (!$goal) {
            throw $this->createNotFoundException('Goal not found');
        }

        $form = $this->createForm(GoalType::class, $goal); // Maintenant $goal est une instance de Goal, pas un repository

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('fitness_goal_show');
        }

        return $this->render('fitness/goal/update-goal.html.twig', [
            'form' => $form->createView(),
            'goal' => $goal, // Ajout de la variable goal au template
        ]);
    }
    
}