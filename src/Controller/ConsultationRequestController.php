<?php

namespace App\Controller;

use App\Entity\ConsultationRequest;
use App\Form\ConsultationRequestType;
use App\Repository\ConsultationRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/consultation/request')]
final class ConsultationRequestController extends AbstractController
{
    #[Route(name: 'app_consultation_request_index', methods: ['GET'])]
    public function index(ConsultationRequestRepository $consultationRequestRepository): Response
    {
        return $this->render('consultation_request/index.html.twig', [
            'consultation_requests' => $consultationRequestRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_consultation_request_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $consultationRequest = new ConsultationRequest();
        $form = $this->createForm(ConsultationRequestType::class, $consultationRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($consultationRequest);
            $entityManager->flush();

            return $this->redirectToRoute('app_consultation_request_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consultation_request/new.html.twig', [
            'consultation_request' => $consultationRequest,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_consultation_request_show', methods: ['GET'])]
    public function show(ConsultationRequest $consultationRequest): Response
    {
        return $this->render('consultation_request/show.html.twig', [
            'consultation_request' => $consultationRequest,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_consultation_request_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ConsultationRequest $consultationRequest, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConsultationRequestType::class, $consultationRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_consultation_request_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consultation_request/edit.html.twig', [
            'consultation_request' => $consultationRequest,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_consultation_request_delete', methods: ['POST'])]
    public function delete(Request $request, ConsultationRequest $consultationRequest, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$consultationRequest->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($consultationRequest);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_consultation_request_index', [], Response::HTTP_SEE_OTHER);
    }
}
