<?php

namespace App\Controller;

use App\Entity\ParcoursDeSante;
use App\Form\ParcoursDeSanteType;
use App\Repository\ParcoursDeSanteRepository;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/parcours/de/sante')]
final class ParcoursDeSanteController extends AbstractController
{
    #[Route(name: 'app_parcours_de_sante_index', methods: ['GET'])]
    public function index(Request $request, ParcoursDeSanteRepository $parcoursDeSanteRepository): Response
    {
        $nomParcours = trim((string) $request->query->get('nomParcours', ''));
        $localisation = trim((string) $request->query->get('localisation', ''));
        $minDistanceRaw = trim((string) $request->query->get('minDistance', ''));
        $maxDistanceRaw = trim((string) $request->query->get('maxDistance', ''));
        $minPublicationCountRaw = trim((string) $request->query->get('minPublicationCount', ''));
        $maxPublicationCountRaw = trim((string) $request->query->get('maxPublicationCount', ''));
        $sortByRaw = strtolower(trim((string) $request->query->get('sortBy', 'date')));
        $sortOrderRaw = strtoupper(trim((string) $request->query->get('sortOrder', 'DESC')));

        $distanceFilter = $parcoursDeSanteRepository->distanceRangeFilter(
            $minDistanceRaw !== '' && is_numeric($minDistanceRaw) ? (float) $minDistanceRaw : null,
            $maxDistanceRaw !== '' && is_numeric($maxDistanceRaw) ? (float) $maxDistanceRaw : null
        );
        $publicationFilter = $parcoursDeSanteRepository->publicationRangeFilter(
            $minPublicationCountRaw !== '' && is_numeric($minPublicationCountRaw) ? (int) $minPublicationCountRaw : null,
            $maxPublicationCountRaw !== '' && is_numeric($maxPublicationCountRaw) ? (int) $maxPublicationCountRaw : null
        );
        $sort = $parcoursDeSanteRepository->normalizeSort($sortByRaw, $sortOrderRaw);

        $distanceFilterApplied = $minDistanceRaw !== '' || $maxDistanceRaw !== '';
        $publicationFilterApplied = $minPublicationCountRaw !== '' || $maxPublicationCountRaw !== '';

        $minDistance = $distanceFilterApplied ? $distanceFilter['minValue'] : null;
        $maxDistance = $distanceFilterApplied ? $distanceFilter['maxValue'] : null;
        $minPublicationCount = $publicationFilterApplied ? $publicationFilter['minValue'] : null;
        $maxPublicationCount = $publicationFilterApplied ? $publicationFilter['maxValue'] : null;

        $sortBy = $sort['sortBy'];
        $sortOrder = $sort['sortOrder'];

        return $this->render('parcours_de_sante/index.html.twig', [
            'parcours_de_santes' => $parcoursDeSanteRepository->searchByNameAndLocation(
                $nomParcours !== '' ? $nomParcours : null,
                $localisation !== '' ? $localisation : null,
                $minDistance,
                $maxDistance,
                $minPublicationCount,
                $maxPublicationCount,
                $sortBy,
                $sortOrder
            ),
            'nomParcours' => $nomParcours,
            'localisation' => $localisation,
            'minDistance' => $minDistance,
            'maxDistance' => $maxDistance,
            'minPublicationCount' => $minPublicationCount,
            'maxPublicationCount' => $maxPublicationCount,
            'distanceFilter' => $distanceFilter,
            'publicationFilter' => $publicationFilter,
            'discoveryConfig' => $parcoursDeSanteRepository->parcoursDiscovery(),
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }

    #[Route('/new', name: 'app_parcours_de_sante_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $parcoursDeSante = new ParcoursDeSante();
        $form = $this->createForm(ParcoursDeSanteType::class, $parcoursDeSante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/parcours';
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0755, true);
                }
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-zA-Z0-9-_]/', '_', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move($uploadsDir, $newFilename);
                    $parcoursDeSante->setImageParcours('/uploads/parcours/'.$newFilename);
                } catch (FileException $e) {
                    // keep going without image on failure
                }
            }

            $entityManager->persist($parcoursDeSante);
            $entityManager->flush();

            return $this->redirectToRoute('app_parcours_de_sante_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parcours_de_sante/new.html.twig', [
            'parcours_de_sante' => $parcoursDeSante,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_parcours_de_sante_show', methods: ['GET'])]
    public function show(ParcoursDeSante $parcoursDeSante, WeatherService $weatherService): Response
    {
        $weather = $weatherService->getCurrentWeatherForCoordinates(
            $parcoursDeSante->getLatitudeParcours(),
            $parcoursDeSante->getLongitudeParcours(),
            $parcoursDeSante->getLocalisationParcours()
        );

        if ($weather === null) {
            $weather = $weatherService->getCurrentWeather($parcoursDeSante->getLocalisationParcours());
        }

        return $this->render('parcours_de_sante/show.html.twig', [
            'parcours_de_sante' => $parcoursDeSante,
            'weather' => $weather,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_parcours_de_sante_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ParcoursDeSante $parcoursDeSante, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParcoursDeSanteType::class, $parcoursDeSante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/parcours';
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0755, true);
                }
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-zA-Z0-9-_]/', '_', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move($uploadsDir, $newFilename);
                    // delete old image if present and lives in uploads/parcours
                    $old = $parcoursDeSante->getImageParcours();
                    if ($old && str_starts_with($old, '/uploads/parcours/')) {
                        $oldPath = $this->getParameter('kernel.project_dir') . '/public' . $old;
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                    $parcoursDeSante->setImageParcours('/uploads/parcours/'.$newFilename);
                } catch (FileException $e) {
                    // ignore image save failure
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_parcours_de_sante_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('parcours_de_sante/edit.html.twig', [
            'parcours_de_sante' => $parcoursDeSante,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_parcours_de_sante_delete', methods: ['POST'])]
    public function delete(Request $request, ParcoursDeSante $parcoursDeSante, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$parcoursDeSante->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($parcoursDeSante);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_parcours_de_sante_index', [], Response::HTTP_SEE_OTHER);
    }
}
