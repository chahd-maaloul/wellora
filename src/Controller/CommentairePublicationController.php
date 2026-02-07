<?php

namespace App\Controller;

use App\Entity\CommentairePublication;
use App\Form\CommentairePublicationType;
use App\Repository\CommentairePublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commentaire/publication')]
final class CommentairePublicationController extends AbstractController
{
    #[Route(name: 'app_commentaire_publication_index', methods: ['GET'])]
    public function index(CommentairePublicationRepository $commentairePublicationRepository): Response
    {
        return $this->render('commentaire_publication/index.html.twig', [
            'commentaire_publications' => $commentairePublicationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_commentaire_publication_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentairePublication = new CommentairePublication();
        $form = $this->createForm(CommentairePublicationType::class, $commentairePublication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commentairePublication);
            $entityManager->flush();

            return $this->redirectToRoute('app_commentaire_publication_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commentaire_publication/new.html.twig', [
            'commentaire_publication' => $commentairePublication,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commentaire_publication_show', methods: ['GET'])]
    public function show(CommentairePublication $commentairePublication): Response
    {
        return $this->render('commentaire_publication/show.html.twig', [
            'commentaire_publication' => $commentairePublication,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commentaire_publication_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CommentairePublication $commentairePublication, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentairePublicationType::class, $commentairePublication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commentaire_publication_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commentaire_publication/edit.html.twig', [
            'commentaire_publication' => $commentairePublication,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commentaire_publication_delete', methods: ['POST'])]
    public function delete(Request $request, CommentairePublication $commentairePublication, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commentairePublication->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($commentairePublication);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commentaire_publication_index', [], Response::HTTP_SEE_OTHER);
    }
}
