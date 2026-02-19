<?php

namespace App\Controller;

use App\Entity\Healthentry;
use App\Entity\Symptom;
use App\Form\HealthentryType;
use App\Repository\HealthentryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;

#[Route('/healthentry')]
final class HealthentryController extends AbstractController
{
    #[Route('/', name: 'app_healthentry_index', methods: ['GET', 'POST'])]
    public function index(HealthentryRepository $healthentryRepository, Request $request): Response
    {
        // Support both GET query params and POST form data
        $search = $request->request->get('search', $request->query->get('search', ''));
        $filterDate = $request->request->get('filterDate', $request->query->get('filterDate', ''));
        $sortBy = $request->request->get('sortBy', $request->query->get('sortBy', 'date'));
        $sortOrder = $request->request->get('sortOrder', $request->query->get('sortOrder', 'DESC'));
        
        $queryBuilder = $healthentryRepository->createQueryBuilder('e');
        
        // Search by poids (weight)
        if ($search) {
            $queryBuilder->andWhere('e.poids LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        // Filter by date
        if ($filterDate) {
            $queryBuilder->andWhere('e.date >= :filterDate')
                ->setParameter('filterDate', new \DateTime($filterDate));
        }
        
        // Sort results
        $queryBuilder->orderBy('e.' . $sortBy, $sortOrder);
        $healthentries = $queryBuilder->getQuery()->getResult();
        
        return $this->render('healthentry/index.html.twig', [
            'healthentries' => $healthentries,
            'search' => $search,
            'filterDate' => $filterDate,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }

    #[Route('/search', name: 'app_healthentry_search', methods: ['GET'])]
    public function search(HealthentryRepository $healthentryRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $filterDate = $request->query->get('filterDate', '');
        $sortBy = $request->query->get('sortBy', 'date');
        $sortOrder = $request->query->get('sortOrder', 'DESC');
        
        $queryBuilder = $healthentryRepository->createQueryBuilder('e');
        
        // Search by poids (weight)
        if ($search) {
            $queryBuilder->andWhere('e.poids LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        
        // Filter by date
        if ($filterDate) {
            $queryBuilder->andWhere('e.date >= :filterDate')
                ->setParameter('filterDate', new \DateTime($filterDate));
        }
        
        // Sort results
        $queryBuilder->orderBy('e.' . $sortBy, $sortOrder);
        $healthentries = $queryBuilder->getQuery()->getResult();
        
        $html = $this->renderView('healthentry/_entries_list.html.twig', [
            'healthentries' => $healthentries,
            'search' => $search,
            'filterDate' => $filterDate,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
        
        return new JsonResponse([
            'html' => $html,
            'total' => count($healthentries),
        ]);
    }

    #[Route('/new', name: 'app_healthentry_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, HealthentryRepository $healthentryRepository): Response
    {
        $healthentry = new Healthentry();
        
        // Pre-populate with one empty symptom for UX
        $healthentry->addSymptom(new Symptom());
        
        $form = $this->createForm(HealthentryType::class, $healthentry);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Handle "Add Symptom" button
            if ($request->request->has('add_symptom')) {
                $symptom = new Symptom();
                $healthentry->addSymptom($symptom);
                
                // Re-create form with updated data
                $form = $this->createForm(HealthentryType::class, $healthentry);
                
                return $this->render('healthentry/new.html.twig', [
                    'healthentry' => $healthentry,
                    'form' => $form->createView(),
                ]);
            }
            
            // Manual validation for numeric fields
            $poids = $form->get('poids')->getData();
            if ($poids !== null && $poids !== '' && ($poids < 30 || $poids > 200)) {
                $form->get('poids')->addError(new \Symfony\Component\Form\FormError(
                    'Le poids doit être compris entre 30 et 200 kg'
                ));
            }
            
            $glycemie = $form->get('glycemie')->getData();
            if ($glycemie !== null && $glycemie !== '' && ($glycemie < 0.5 || $glycemie > 3)) {
                $form->get('glycemie')->addError(new \Symfony\Component\Form\FormError(
                    'La glycémie doit être comprise entre 0.5 et 3 g/l'
                ));
            }
            
            $tension = $form->get('tension')->getData();
            if ($tension !== null && $tension !== '') {
                $tensionValue = (float)$tension;
                if ($tensionValue < 40 || $tensionValue > 120) {
                    $form->get('tension')->addError(new \Symfony\Component\Form\FormError(
                        'La tension doit être comprise entre 40 et 120 mmHg'
                    ));
                }
            }
            
            $sommeil = $form->get('sommeil')->getData();
            if ($sommeil !== null && $sommeil !== '' && ($sommeil < 0 || $sommeil > 12)) {
                $form->get('sommeil')->addError(new \Symfony\Component\Form\FormError(
                    'Le sommeil doit être compris entre 0 et 12 heures'
                ));
            }
            
            // Check for duplicate entry (same date and journal)
            $date = $healthentry->getDate();
            $journal = $healthentry->getJournal();
            if ($date && $journal) {
                $existingEntry = $healthentryRepository->findOneBy(['date' => $date, 'journal' => $journal]);
                if ($existingEntry) {
                    $form->addError(new \Symfony\Component\Form\FormError(
                        'Une entrée existe déjà pour cette date. Veuillez modifier l\'entrée existante ou choisir une autre date.'
                    ));
                }
            }
            
            if ($form->isValid()) {
                $entityManager->persist($healthentry);
                $entityManager->flush();

                return $this->redirectToRoute('app_healthentry_show', ['id' => $healthentry->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('healthentry/new.html.twig', [
            'healthentry' => $healthentry,
            'form' => $form->createView(),
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
    public function edit(Request $request, Healthentry $healthentry, EntityManagerInterface $entityManager, HealthentryRepository $healthentryRepository): Response
    {
        // Pre-populate with one empty symptom if none exist
        if ($healthentry->getSymptoms()->count() === 0) {
            $healthentry->addSymptom(new Symptom());
        }
        
        $form = $this->createForm(HealthentryType::class, $healthentry);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Handle "Add Symptom" button
            if ($request->request->has('add_symptom')) {
                $symptom = new Symptom();
                $healthentry->addSymptom($symptom);
                
                // Re-create form with updated data
                $form = $this->createForm(HealthentryType::class, $healthentry);
                
                return $this->render('healthentry/edit.html.twig', [
                    'healthentry' => $healthentry,
                    'form' => $form->createView(),
                ]);
            }
            
            // Manual validation for numeric fields
            $poids = $form->get('poids')->getData();
            if ($poids !== null && $poids !== '' && ($poids < 30 || $poids > 200)) {
                $form->get('poids')->addError(new \Symfony\Component\Form\FormError(
                    'Le poids doit être compris entre 30 et 200 kg'
                ));
            }
            
            $glycemie = $form->get('glycemie')->getData();
            if ($glycemie !== null && $glycemie !== '' && ($glycemie < 0.5 || $glycemie > 3)) {
                $form->get('glycemie')->addError(new \Symfony\Component\Form\FormError(
                    'La glycémie doit être comprise entre 0.5 et 3 g/l'
                ));
            }
            
            $tension = $form->get('tension')->getData();
            if ($tension !== null && $tension !== '') {
                $tensionValue = (float)$tension;
                if ($tensionValue < 40 || $tensionValue > 120) {
                    $form->get('tension')->addError(new \Symfony\Component\Form\FormError(
                        'La tension doit être comprise entre 40 et 120 mmHg'
                    ));
                }
            }
            
            $sommeil = $form->get('sommeil')->getData();
            if ($sommeil !== null && $sommeil !== '' && ($sommeil < 0 || $sommeil > 12)) {
                $form->get('sommeil')->addError(new \Symfony\Component\Form\FormError(
                    'Le sommeil doit être compris entre 0 et 12 heures'
                ));
            }
            
            // Check for duplicate entry (same date and journal, but different ID)
            $date = $healthentry->getDate();
            $journal = $healthentry->getJournal();
            if ($date && $journal) {
                $existingEntry = $healthentryRepository->findOneBy(['date' => $date, 'journal' => $journal]);
                if ($existingEntry && $existingEntry->getId() !== $healthentry->getId()) {
                    $form->addError(new \Symfony\Component\Form\FormError(
                        'Une autre entrée existe déjà pour cette date.'
                    ));
                }
            }
            
            if ($form->isValid()) {
                $entityManager->flush();

                return $this->redirectToRoute('app_healthentry_show', ['id' => $healthentry->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('healthentry/edit.html.twig', [
            'healthentry' => $healthentry,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_healthentry_delete', methods: ['POST'])]
    public function delete(Request $request, Healthentry $healthentry, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$healthentry->getId(), $request->request->get('_token'))) {
            $entityManager->remove($healthentry);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_healthentry_index', [], Response::HTTP_SEE_OTHER);
    }
}
