<?php

namespace App\Controller;

use App\Entity\GroceryItem;
use App\Form\GroceryItemType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/grocery/item')]
final class GroceryItemController extends AbstractController
{
    #[Route(name: 'app_grocery_item_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $groceryItems = $entityManager
            ->getRepository(GroceryItem::class)
            ->findAll();

        return $this->render('grocery_item/index.html.twig', [
            'grocery_items' => $groceryItems,
        ]);
    }

    #[Route('/new', name: 'app_grocery_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $groceryItem = new GroceryItem();
        $form = $this->createForm(GroceryItemType::class, $groceryItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($groceryItem);
            $entityManager->flush();

            return $this->redirectToRoute('app_grocery_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('grocery_item/new.html.twig', [
            'grocery_item' => $groceryItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_grocery_item_show', methods: ['GET'])]
    public function show(GroceryItem $groceryItem): Response
    {
        return $this->render('grocery_item/show.html.twig', [
            'grocery_item' => $groceryItem,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_grocery_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, GroceryItem $groceryItem, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GroceryItemType::class, $groceryItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_grocery_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('grocery_item/edit.html.twig', [
            'grocery_item' => $groceryItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_grocery_item_delete', methods: ['POST'])]
    public function delete(Request $request, GroceryItem $groceryItem, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$groceryItem->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($groceryItem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_grocery_item_index', [], Response::HTTP_SEE_OTHER);
    }
}
