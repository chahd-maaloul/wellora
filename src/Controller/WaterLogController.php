<?php

namespace App\Controller;

use App\Entity\WaterLog;
use App\Form\WaterLogType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/water/log')]
final class WaterLogController extends AbstractController
{
    #[Route(name: 'app_water_log_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $waterLogs = $entityManager
            ->getRepository(WaterLog::class)
            ->findAll();

        return $this->render('water_log/index.html.twig', [
            'water_logs' => $waterLogs,
        ]);
    }

    #[Route('/new', name: 'app_water_log_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $waterLog = new WaterLog();
        $form = $this->createForm(WaterLogType::class, $waterLog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($waterLog);
            $entityManager->flush();

            return $this->redirectToRoute('app_water_log_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('water_log/new.html.twig', [
            'water_log' => $waterLog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_water_log_show', methods: ['GET'])]
    public function show(WaterLog $waterLog): Response
    {
        return $this->render('water_log/show.html.twig', [
            'water_log' => $waterLog,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_water_log_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, WaterLog $waterLog, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WaterLogType::class, $waterLog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_water_log_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('water_log/edit.html.twig', [
            'water_log' => $waterLog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_water_log_delete', methods: ['POST'])]
    public function delete(Request $request, WaterLog $waterLog, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$waterLog->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($waterLog);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_water_log_index', [], Response::HTTP_SEE_OTHER);
    }
}
