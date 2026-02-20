<?php

namespace App\Controller;

use App\Entity\ParcoursDeSante;
use App\Form\ParcoursDeSanteType;
use App\Repository\ParcoursDeSanteRepository;
use App\Service\MapService;
use App\Service\ParcoursRecommendationService;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/parcours/de/sante')]
final class ParcoursDeSanteController extends AbstractController
{
    #[Route(name: 'app_parcours_de_sante_index', methods: ['GET'])]
    public function index(
        Request $request,
        ParcoursDeSanteRepository $parcoursDeSanteRepository,
        PaginatorInterface $paginator
    ): Response
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

        $parcours = $parcoursDeSanteRepository->searchByNameAndLocation(
                $nomParcours !== '' ? $nomParcours : null,
                $localisation !== '' ? $localisation : null,
                $minDistance,
                $maxDistance,
                $minPublicationCount,
                $maxPublicationCount,
                $sortBy,
                $sortOrder
            );

        $parcoursPagination = $paginator->paginate(
            $parcours,
            max(1, $request->query->getInt('page', 1)),
            9
        );

        return $this->render('parcours_de_sante/index.html.twig', [
            'parcours_de_santes' => $parcoursPagination,
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
            'ai_weather_options' => $this->aiWeatherOptions(),
            'ai_chat_endpoint' => $this->generateUrl('app_parcours_de_sante_ai_recommend'),
        ]);
    }

    #[Route('/ai/recommend', name: 'app_parcours_de_sante_ai_recommend', methods: ['POST'])]
    public function aiRecommend(
        Request $request,
        ParcoursDeSanteRepository $parcoursDeSanteRepository,
        MapService $mapService,
        ParcoursRecommendationService $parcoursRecommendationService
    ): JsonResponse
    {
        try {
            $payload = $request->toArray();
        } catch (\Throwable) {
            return new JsonResponse([
                'ok' => false,
                'message' => 'Invalid request payload.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $location = trim((string) ($payload['location'] ?? ''));
        $weather = $this->normalizeWeatherPreference((string) ($payload['weather'] ?? 'any'));
        $latitude = isset($payload['latitude']) && is_numeric($payload['latitude']) ? (float) $payload['latitude'] : null;
        $longitude = isset($payload['longitude']) && is_numeric($payload['longitude']) ? (float) $payload['longitude'] : null;

        if ($latitude === null || $longitude === null) {
            if ($location === '') {
                return new JsonResponse([
                    'ok' => false,
                    'message' => 'Please share your city or use your current location first.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $geoData = $mapService->geocodeLocation($location);
            if ($geoData === null) {
                return new JsonResponse([
                    'ok' => false,
                    'message' => 'I could not find that location. Try a more specific city or address.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $latitude = isset($geoData['latitude']) ? (float) $geoData['latitude'] : null;
            $longitude = isset($geoData['longitude']) ? (float) $geoData['longitude'] : null;
            if ($latitude === null || $longitude === null) {
                return new JsonResponse([
                    'ok' => false,
                    'message' => 'I could not resolve coordinates for that location.',
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $recommendation = $parcoursRecommendationService->recommendNearestByWeather(
            $parcoursDeSanteRepository->findAll(),
            $latitude,
            $longitude,
            $weather
        );

        if ($recommendation === null) {
            return new JsonResponse([
                'ok' => false,
                'message' => 'No recommendation is available yet. Add coordinates to at least one parcours.',
            ], Response::HTTP_NOT_FOUND);
        }

        $trail = $recommendation['parcours'];
        if (!$trail instanceof ParcoursDeSante) {
            return new JsonResponse([
                'ok' => false,
                'message' => 'Recommendation failed unexpectedly. Please try again.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $trailId = $trail->getId();
        $distanceKm = (float) ($recommendation['distance_km'] ?? 0.0);
        $weatherCondition = (string) (($recommendation['weather']['condition'] ?? null) ?? 'Unavailable');
        $weatherMatch = (bool) ($recommendation['weather_match'] ?? false);

        $summary = sprintf(
            'I recommend %s in %s. It is %.1f km from you and the current weather there is %s.',
            (string) $trail->getNomParcours(),
            (string) $trail->getLocalisationParcours(),
            $distanceKm,
            $weatherCondition
        );
        $followup = $weatherMatch
            ? ' This matches your preferred weather.'
            : ' This is the closest strong option even if weather is not a perfect match.';

        return new JsonResponse([
            'ok' => true,
            'message' => $summary . $followup,
            'recommendation' => [
                'id' => $trailId,
                'name' => (string) $trail->getNomParcours(),
                'location' => (string) $trail->getLocalisationParcours(),
                'distance_km' => round($distanceKm, 1),
                'weather_condition' => $weatherCondition,
                'weather_match' => $weatherMatch,
                'details_url' => $trailId !== null
                    ? $this->generateUrl('app_parcours_de_sante_show', ['id' => $trailId])
                    : null,
            ],
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
    public function show(ParcoursDeSante $parcoursDeSante, WeatherService $weatherService, MapService $mapService): Response
    {
        $weather = $weatherService->getCurrentWeatherForCoordinates(
            $parcoursDeSante->getLatitudeParcours(),
            $parcoursDeSante->getLongitudeParcours(),
            $parcoursDeSante->getLocalisationParcours()
        );

        if ($weather === null) {
            $weather = $weatherService->getCurrentWeather($parcoursDeSante->getLocalisationParcours());
        }

        $mapData = $mapService->getMapDataForCoordinates(
            $parcoursDeSante->getLatitudeParcours(),
            $parcoursDeSante->getLongitudeParcours(),
            $parcoursDeSante->getLocalisationParcours()
        );

        if ($mapData === null) {
            $geoData = $mapService->geocodeLocation($parcoursDeSante->getLocalisationParcours());
            if ($geoData !== null) {
                $mapData = $mapService->getMapDataForCoordinates(
                    isset($geoData['latitude']) ? (float) $geoData['latitude'] : null,
                    isset($geoData['longitude']) ? (float) $geoData['longitude'] : null,
                    $parcoursDeSante->getLocalisationParcours()
                );
            }
        }

        return $this->render('parcours_de_sante/show.html.twig', [
            'parcours_de_sante' => $parcoursDeSante,
            'weather' => $weather,
            'map_data' => $mapData,
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

    /**
     * @return array<string, string>
     */
    private function aiWeatherOptions(): array
    {
        return [
            'any' => 'Any weather',
            'clear' => 'Clear sky',
            'cloudy' => 'Cloudy',
            'rain' => 'Rainy',
            'snow' => 'Snowy',
            'windy' => 'Windy',
        ];
    }

    private function normalizeWeatherPreference(string $rawPreference): string
    {
        $value = strtolower(trim($rawPreference));
        if ($value === '' || in_array($value, ['any', 'whatever'], true)) {
            return 'any';
        }

        $mappings = [
            'clear' => ['clear', 'sun', 'sunny', 'warm', 'hot', 'soleil', 'ensoleille'],
            'cloudy' => ['cloud', 'cloudy', 'fog', 'nuage', 'nuageux', 'brume'],
            'rain' => ['rain', 'rainy', 'drizzle', 'storm', 'pluie', 'pluvieux', 'orage'],
            'snow' => ['snow', 'snowy', 'neige'],
            'windy' => ['wind', 'windy', 'vent', 'venteux'],
        ];

        foreach ($mappings as $normalized => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($value, $keyword)) {
                    return $normalized;
                }
            }
        }

        return 'any';
    }
}
