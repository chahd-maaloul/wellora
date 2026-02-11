<?php

namespace App\Controller;

use App\Entity\NutritionGoal;
use App\Form\NutritionGoalType;
use App\Repository\NutritionGoalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nutrition/goal')]
final class NutritionGoalController extends AbstractController
{
    #[Route(name: 'app_nutrition_goal_index', methods: ['GET'])]
    public function index(NutritionGoalRepository $nutritionGoalRepository): Response
    {
        return $this->render('nutrition_goal/index.html.twig', [
            'nutrition_goals' => $nutritionGoalRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_nutrition_goal_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $nutritionGoal = new NutritionGoal();
        $form = $this->createForm(NutritionGoalType::class, $nutritionGoal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($nutritionGoal);
            $entityManager->flush();

            return $this->redirectToRoute('app_nutrition_goal_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('nutrition_goal/new.html.twig', [
            'nutrition_goal' => $nutritionGoal,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_nutrition_goal_show', methods: ['GET'])]
    public function show(NutritionGoal $nutritionGoal): Response
    {
        return $this->render('nutrition_goal/show.html.twig', [
            'nutrition_goal' => $nutritionGoal,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_nutrition_goal_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, NutritionGoal $nutritionGoal, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NutritionGoalType::class, $nutritionGoal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_nutrition_goal_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('nutrition_goal/edit.html.twig', [
            'nutrition_goal' => $nutritionGoal,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_nutrition_goal_delete', methods: ['POST'])]
    public function delete(Request $request, NutritionGoal $nutritionGoal, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$nutritionGoal->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($nutritionGoal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_nutrition_goal_index', [], Response::HTTP_SEE_OTHER);
    }
}
