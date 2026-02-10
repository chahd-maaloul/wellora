<?php

namespace App\Controller;

use App\Entity\Healthentry;
use App\Form\HealthentryType;
use App\Repository\HealthentryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/healthentry')]
final class HealthentryController extends AbstractController
{
    #[Route(name: 'app_healthentry_index', methods: ['GET'])]
    public function index(HealthentryRepository $healthentryRepository): Response
    {
        return $this->render('healthentry/index.html.twig', [
            'healthentries' => $healthentryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_healthentry_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $healthentry = new Healthentry();
        $form = $this->createForm(HealthentryType::class, $healthentry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($healthentry);
            $entityManager->flush();

            return $this->redirectToRoute('app_healthentry_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('health/accessible/journal-entry.html.twig', [
            'healthentry' => $healthentry,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_healthentry_show', methods: ['GET'])]
    public function show(Healthentry $healthentry): Response
    {
        return $this->render('healthentry/show.html.twig', [
            'healthentry' => $healthentry,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_healthentry_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Healthentry $healthentry, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HealthentryType::class, $healthentry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_healthentry_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('healthentry/edit.html.twig', [
            'healthentry' => $healthentry,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_healthentry_delete', methods: ['POST'])]
    public function delete(Request $request, Healthentry $healthentry, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$healthentry->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($healthentry);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_healthentry_index', [], Response::HTTP_SEE_OTHER);
    }
}
