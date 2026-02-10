<?php

namespace App\Controller;

use App\Entity\Healthjournal;
use App\Form\HealthjournalType;
use App\Repository\HealthjournalRepository;
use App\Repository\HealthentryRepository;
use App\Repository\SymptomRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/healthjournal')]
final class HealthjournalController extends AbstractController
{
    #[Route(name: 'app_healthjournal_index', methods: ['GET'])]
    public function index(HealthjournalRepository $healthjournalRepository): Response
    {
        return $this->render('healthjournal/index.html.twig', [
            'healthjournals' => $healthjournalRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_healthjournal_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $healthjournal = new Healthjournal();
        $form = $this->createForm(HealthjournalType::class, $healthjournal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($healthjournal);
            $entityManager->flush();

            return $this->redirectToRoute('app_healthjournal_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('healthjournal/new.html.twig', [
            'healthjournal' => $healthjournal,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_healthjournal_show', methods: ['GET'])]
    public function show(Healthjournal $healthjournal): Response
    {
        // Get entries filtered by the journal's date range
        $filteredEntries = $healthjournal->getEntriesByDateRange();
        
        // Collect all symptoms from filtered entries
        $symptoms = [];
        foreach ($filteredEntries as $entry) {
            foreach ($entry->getSymptoms() as $symptom) {
                $symptoms[] = $symptom;
            }
        }

        return $this->render('healthjournal/show.html.twig', [
            'healthjournal' => $healthjournal,
            'healthEntries' => $filteredEntries,
            'symptoms' => $symptoms,
        ]);
    }
    #[Route('/{id}/dashboard', name: 'app_healthjournal_dashboard', methods: ['GET'])]
    public function dashboard(Healthjournal $healthjournal, HealthentryRepository $entryRepo, SymptomRepository $symptomRepo): Response
    {
        // Get entries filtered by the journal's date range
        $entries = $healthjournal->getEntriesByDateRange();
        
        // Get symptoms from filtered entries
        $symptoms = [];
        foreach ($entries as $entry) {
            foreach ($entry->getSymptoms() as $symptom) {
                $symptoms[] = $symptom;
            }
        }

        return $this->render('health/analytics/dashboard.html.twig', [
            'healthjournal' => $healthjournal,
            'healthEntries' => $entries,
            'symptoms' => $symptoms,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_healthjournal_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Healthjournal $healthjournal, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HealthjournalType::class, $healthjournal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_healthjournal_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('healthjournal/edit.html.twig', [
            'healthjournal' => $healthjournal,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_healthjournal_delete', methods: ['POST'])]
    public function delete(Request $request, Healthjournal $healthjournal, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$healthjournal->getId(), $request->request->get('_token'))) {
            $entityManager->remove($healthjournal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_healthjournal_index', [], Response::HTTP_SEE_OTHER);
    }
}
