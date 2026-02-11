<?php

namespace App\Controller;

use App\Entity\FoodItem;
use App\Form\FoodItemType;
use App\Repository\FoodItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/food/item')]
final class FoodItemController extends AbstractController
{
    #[Route(name: 'app_food_item_index', methods: ['GET'])]
    public function index(FoodItemRepository $foodItemRepository): Response
    {
        return $this->render('food_item/index.html.twig', [
            'food_items' => $foodItemRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_food_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $foodItem = new FoodItem();
        $form = $this->createForm(FoodItemType::class, $foodItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($foodItem);
            $entityManager->flush();

            return $this->redirectToRoute('app_food_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('food_item/new.html.twig', [
            'food_item' => $foodItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_food_item_show', methods: ['GET'])]
    public function show(FoodItem $foodItem): Response
    {
        return $this->render('food_item/show.html.twig', [
            'food_item' => $foodItem,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_food_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FoodItem $foodItem, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FoodItemType::class, $foodItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_food_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('food_item/edit.html.twig', [
            'food_item' => $foodItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_food_item_delete', methods: ['POST'])]
    public function delete(Request $request, FoodItem $foodItem, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$foodItem->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($foodItem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_food_item_index', [], Response::HTTP_SEE_OTHER);
    }
}
