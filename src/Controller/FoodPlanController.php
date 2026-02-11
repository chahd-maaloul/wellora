<?php

namespace App\Controller;

use App\Entity\FoodPlan;
use App\Form\FoodPlanType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/food/plan')]
final class FoodPlanController extends AbstractController
{
    #[Route(name: 'app_food_plan_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $foodPlans = $entityManager
            ->getRepository(FoodPlan::class)
            ->findAll();

        return $this->render('food_plan/index.html.twig', [
            'food_plans' => $foodPlans,
        ]);
    }

    #[Route('/new', name: 'app_food_plan_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $foodPlan = new FoodPlan();
        $form = $this->createForm(FoodPlanType::class, $foodPlan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($foodPlan);
            $entityManager->flush();

            return $this->redirectToRoute('app_food_plan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('food_plan/new.html.twig', [
            'food_plan' => $foodPlan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_food_plan_show', methods: ['GET'])]
    public function show(FoodPlan $foodPlan): Response
    {
        return $this->render('food_plan/show.html.twig', [
            'food_plan' => $foodPlan,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_food_plan_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FoodPlan $foodPlan, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FoodPlanType::class, $foodPlan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_food_plan_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('food_plan/edit.html.twig', [
            'food_plan' => $foodPlan,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_food_plan_delete', methods: ['POST'])]
    public function delete(Request $request, FoodPlan $foodPlan, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$foodPlan->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($foodPlan);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_food_plan_index', [], Response::HTTP_SEE_OTHER);
    }
}
