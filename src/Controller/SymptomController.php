<?php

namespace App\Controller;

use App\Entity\Symptom;
use App\Form\SymptomType;
use App\Repository\SymptomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/symptom')]
final class SymptomController extends AbstractController
{
    #[Route(name: 'app_symptom_index', methods: ['GET'])]
    public function index(SymptomRepository $symptomRepository): Response
    {
        return $this->render('symptom/index.html.twig', [
            'symptoms' => $symptomRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_symptom_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $symptom = new Symptom();
        $form = $this->createForm(SymptomType::class, $symptom);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($symptom);
            $entityManager->flush();

            return $this->redirectToRoute('app_symptom_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('symptom/new.html.twig', [
            'symptom' => $symptom,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_symptom_show', methods: ['GET'])]
    public function show(Symptom $symptom): Response
    {
        return $this->render('symptom/show.html.twig', [
            'symptom' => $symptom,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_symptom_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Symptom $symptom, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SymptomType::class, $symptom);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_symptom_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('symptom/edit.html.twig', [
            'symptom' => $symptom,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_symptom_delete', methods: ['POST'])]
    public function delete(Request $request, Symptom $symptom, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$symptom->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($symptom);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_symptom_index', [], Response::HTTP_SEE_OTHER);
    }
}
