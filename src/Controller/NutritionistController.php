<?php

namespace App\Controller;

use App\Entity\Nutritionist;
use App\Form\NutritionistType;
use App\Repository\NutritionistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nutritionist')]
final class NutritionistController extends AbstractController
{
    #[Route(name: 'app_nutritionist_index', methods: ['GET'])]
    public function index(NutritionistRepository $nutritionistRepository): Response
    {
        return $this->render('nutritionist/index.html.twig', [
            'nutritionists' => $nutritionistRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_nutritionist_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $nutritionist = new Nutritionist();
        $form = $this->createForm(NutritionistType::class, $nutritionist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($nutritionist);
            $entityManager->flush();

            return $this->redirectToRoute('app_nutritionist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('nutritionist/new.html.twig', [
            'nutritionist' => $nutritionist,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_nutritionist_show', methods: ['GET'])]
    public function show(Nutritionist $nutritionist): Response
    {
        return $this->render('nutritionist/show.html.twig', [
            'nutritionist' => $nutritionist,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_nutritionist_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Nutritionist $nutritionist, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NutritionistType::class, $nutritionist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_nutritionist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('nutritionist/edit.html.twig', [
            'nutritionist' => $nutritionist,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_nutritionist_delete', methods: ['POST'])]
    public function delete(Request $request, Nutritionist $nutritionist, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$nutritionist->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($nutritionist);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_nutritionist_index', [], Response::HTTP_SEE_OTHER);
    }
}
