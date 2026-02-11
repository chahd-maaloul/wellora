<?php

namespace App\Controller;

use App\Entity\GroceryList;
use App\Form\GroceryListType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/grocery/list')]
final class GroceryListController extends AbstractController
{
    #[Route(name: 'app_grocery_list_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $groceryLists = $entityManager
            ->getRepository(GroceryList::class)
            ->findAll();

        return $this->render('grocery_list/index.html.twig', [
            'grocery_lists' => $groceryLists,
        ]);
    }

    #[Route('/new', name: 'app_grocery_list_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $groceryList = new GroceryList();
        $form = $this->createForm(GroceryListType::class, $groceryList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($groceryList);
            $entityManager->flush();

            return $this->redirectToRoute('app_grocery_list_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('grocery_list/new.html.twig', [
            'grocery_list' => $groceryList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_grocery_list_show', methods: ['GET'])]
    public function show(GroceryList $groceryList): Response
    {
        return $this->render('grocery_list/show.html.twig', [
            'grocery_list' => $groceryList,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_grocery_list_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, GroceryList $groceryList, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GroceryListType::class, $groceryList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_grocery_list_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('grocery_list/edit.html.twig', [
            'grocery_list' => $groceryList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_grocery_list_delete', methods: ['POST'])]
    public function delete(Request $request, GroceryList $groceryList, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$groceryList->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($groceryList);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_grocery_list_index', [], Response::HTTP_SEE_OTHER);
    }
}
