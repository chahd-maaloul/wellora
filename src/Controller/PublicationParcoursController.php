<?php

namespace App\Controller;

use App\Entity\PublicationParcours;
use App\Form\PublicationParcoursType;
use App\Repository\PublicationParcoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/publication/parcours')]
final class PublicationParcoursController extends AbstractController
{
    #[Route(name: 'app_publication_parcours_index', methods: ['GET'])]
    public function index(PublicationParcoursRepository $publicationParcoursRepository): Response
    {
        return $this->render('publication_parcours/index.html.twig', [
            'publication_parcours' => $publicationParcoursRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_publication_parcours_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $publicationParcour = new PublicationParcours();
        $form = $this->createForm(PublicationParcoursType::class, $publicationParcour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($publicationParcour);
            $entityManager->flush();

            return $this->redirectToRoute('app_publication_parcours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('publication_parcours/new.html.twig', [
            'publication_parcour' => $publicationParcour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_publication_parcours_show', methods: ['GET'])]
    public function show(PublicationParcours $publicationParcour): Response
    {
        return $this->render('publication_parcours/show.html.twig', [
            'publication_parcour' => $publicationParcour,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_publication_parcours_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PublicationParcours $publicationParcour, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PublicationParcoursType::class, $publicationParcour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_publication_parcours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('publication_parcours/edit.html.twig', [
            'publication_parcour' => $publicationParcour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_publication_parcours_delete', methods: ['POST'])]
    public function delete(Request $request, PublicationParcours $publicationParcour, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$publicationParcour->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($publicationParcour);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_publication_parcours_index', [], Response::HTTP_SEE_OTHER);
    }
}
