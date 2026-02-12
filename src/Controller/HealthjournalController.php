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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/healthjournal')]
final class HealthjournalController extends AbstractController
{
    #[Route('/', name: 'app_healthjournal_index', methods: ['GET', 'POST'])]
    public function index(HealthjournalRepository $healthjournalRepository, Request $request): Response
    {
        // Support both POST form data and GET query params
        $search = $request->request->get('search', $request->query->get('search', ''));
        $filterDate = $request->request->get('filterDate', $request->query->get('filterDate', ''));
        $sortBy = $request->request->get('sortBy', $request->query->get('sortBy', 'datedebut'));
        $sortOrder = $request->request->get('sortOrder', $request->query->get('sortOrder', 'ASC'));
        
        $queryBuilder = $healthjournalRepository->createQueryBuilder('h');
        
        // Search by name
        if ($search) {
            $queryBuilder->andWhere('h.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        // Filter by date start
        if ($filterDate) {
            $queryBuilder->andWhere('h.datedebut >= :filterDate')
                ->setParameter('filterDate', new \DateTime($filterDate));
        }
        
        // Sort results
        $queryBuilder->orderBy('h.' . $sortBy, $sortOrder);
        $healthjournals = $queryBuilder->getQuery()->getResult();
        
        return $this->render('healthjournal/index.html.twig', [
            'healthjournals' => $healthjournals,
            'search' => $search,
            'filterDate' => $filterDate,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }
    
    #[Route('/search', name: 'app_healthjournal_search', methods: ['GET'])]
    public function search(HealthjournalRepository $healthjournalRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $filterDate = $request->query->get('filterDate', '');
        $sortBy = $request->query->get('sortBy', 'datedebut');
        $sortOrder = $request->query->get('sortOrder', 'ASC');
        
        $queryBuilder = $healthjournalRepository->createQueryBuilder('h');
        
        // Search by name
        if ($search) {
            $queryBuilder->andWhere('h.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        // Filter by date start
        if ($filterDate) {
            $queryBuilder->andWhere('h.datedebut >= :filterDate')
                ->setParameter('filterDate', new \DateTime($filterDate));
        }
        
        // Sort results
        $queryBuilder->orderBy('h.' . $sortBy, $sortOrder);
        $healthjournals = $queryBuilder->getQuery()->getResult();
        
        return $this->render('healthjournal/_journals_list.html.twig', [
            'healthjournals' => $healthjournals,
            'search' => $search,
            'filterDate' => $filterDate,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }

    #[Route('/new', name: 'app_healthjournal_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, HealthjournalRepository $healthjournalRepository): Response
    {
        $healthjournal = new Healthjournal();
        $form = $this->createForm(HealthjournalType::class, $healthjournal);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Validate date range
            $datedebut = $form->get('datedebut')->getData();
            $datefin = $form->get('datefin')->getData();
            
            if ($datedebut && $datefin && $datedebut > $datefin) {
                $this->addFlash('error', 'La date de début doit être inférieure à la date de fin.');
                $form->addError(new \Symfony\Component\Form\FormError(
                    'La date de début doit être inférieure à la date de fin'
                ));
            }
            
            if ($form->isValid()) {
                $entityManager->persist($healthjournal);
                $entityManager->flush();

                $this->addFlash('success', 'Journal créé avec succès.');
                return $this->redirectToRoute('app_healthjournal_index', [], Response::HTTP_SEE_OTHER);
            }
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
    public function edit(Request $request, Healthjournal $healthjournal, EntityManagerInterface $entityManager, HealthjournalRepository $healthjournalRepository): Response
    {
        $originalName = $healthjournal->getName(); // Keep original name for duplicate check
        $form = $this->createForm(HealthjournalType::class, $healthjournal);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Validate date range
            $datedebut = $form->get('datedebut')->getData();
            $datefin = $form->get('datefin')->getData();
            
            if ($datedebut && $datefin && $datedebut > $datefin) {
                $this->addFlash('error', 'La date de début doit être inférieure à la date de fin.');
                $form->addError(new \Symfony\Component\Form\FormError(
                    'La date de début doit être inférieure à la date de fin'
                ));
            }
            
            // Check for duplicate journal (same name but different ID)
            $name = $form->get('name')->getData();
            if ($name && $name !== $originalName) {
                $existingJournal = $healthjournalRepository->findOneBy(['name' => $name]);
                if ($existingJournal && $existingJournal->getId() !== $healthjournal->getId()) {
                    $this->addFlash('error', 'Un journal avec ce nom existe déjà. Veuillez choisir un autre nom.');
                    $form->addError(new \Symfony\Component\Form\FormError(
                        'Un journal avec ce nom existe déjà. Veuillez choisir un autre nom.'
                    ));
                }
            }
            
            if ($form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'Journal modifié avec succès.');
                return $this->redirectToRoute('app_healthjournal_index', [], Response::HTTP_SEE_OTHER);
            }
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
            $this->addFlash('success', 'Journal supprimé avec succès.');
        }

        return $this->redirectToRoute('app_healthjournal_index', [], Response::HTTP_SEE_OTHER);
    }
}
