<?php

namespace App\Controller;

use App\Repository\CommentairePublicationRepository;
use App\Repository\ParcoursDeSanteRepository;
use App\Repository\PublicationParcoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminTrailAnalyticsController extends AbstractController
{
    // ==================== ADMIN TRAIL ANALYTICS ROUTES ====================
    
    #[Route('/admin/trail-analytics', name: 'admin_trail_analytics')]
    public function dashboard(): Response
    {
        return $this->render('admin/trail-analytics/dashboard.html.twig');
    }

    #[Route('/admin/trail-analytics/usage', name: 'admin_trail_usage')]
    public function usage(): Response
    {
        return $this->render('admin/trail-analytics/usage-metrics.html.twig');
    }

    #[Route('/admin/trail-analytics/safety', name: 'admin_trail_safety')]
    public function safety(ParcoursDeSanteRepository $parcoursDeSanteRepository): Response
    {
        $parcoursDeSantes = $parcoursDeSanteRepository->findBy([], [
            'dateCreation' => 'DESC',
            'nomParcours' => 'ASC',
        ]);

        $totalParcours = count($parcoursDeSantes);
        $totalDistance = 0.0;
        $totalPublications = 0;
        $newParcoursLast30Days = 0;
        $longestDistance = 0.0;
        $parcoursRows = [];
        $last30DaysThreshold = new \DateTimeImmutable('-30 days');

        foreach ($parcoursDeSantes as $parcoursDeSante) {
            $distance = (float) ($parcoursDeSante->getDistanceParcours() ?? 0.0);
            $publicationCount = $parcoursDeSante->getPublicationParcours()->count();
            $dateCreation = $parcoursDeSante->getDateCreation();

            $totalDistance += $distance;
            $totalPublications += $publicationCount;
            $longestDistance = max($longestDistance, $distance);

            if ($dateCreation !== null && $dateCreation >= $last30DaysThreshold) {
                ++$newParcoursLast30Days;
            }

            $parcoursRows[] = [
                'parcours' => $parcoursDeSante,
                'publicationCount' => $publicationCount,
            ];
        }

        $averageDistance = $totalParcours > 0 ? round($totalDistance / $totalParcours, 1) : 0.0;

        $topPublishedParcours = $parcoursRows;
        usort($topPublishedParcours, static function (array $left, array $right): int {
            return $right['publicationCount'] <=> $left['publicationCount'];
        });
        $topPublishedParcours = array_slice($topPublishedParcours, 0, 3);

        return $this->render('admin/trail-analytics/safety-reports.html.twig', [
            'parcoursRows' => $parcoursRows,
            'topPublishedParcours' => $topPublishedParcours,
            'stats' => [
                'totalParcours' => $totalParcours,
                'totalPublications' => $totalPublications,
                'newParcoursLast30Days' => $newParcoursLast30Days,
                'averageDistance' => $averageDistance,
                'longestDistance' => $longestDistance,
            ],
        ]);
    }

    #[Route('/admin/trail-analytics/user-behavior', name: 'admin_trail_user_behavior')]
    public function userBehavior(CommentairePublicationRepository $commentairePublicationRepository): Response
    {
        $comments = $commentairePublicationRepository->findBy([], [
            'dateCommentaire' => 'DESC',
            'id' => 'DESC',
        ]);

        $trailIds = [];
        $latestCommentDate = null;

        foreach ($comments as $comment) {
            $trailId = $comment->getPublicationParcours()?->getParcoursDeSante()?->getId();
            if ($trailId !== null) {
                $trailIds[$trailId] = true;
            }

            $commentDate = $comment->getDateCommentaire();
            if ($commentDate !== null && ($latestCommentDate === null || $commentDate > $latestCommentDate)) {
                $latestCommentDate = $commentDate;
            }
        }

        return $this->render('admin/trail-analytics/user-behavior.html.twig', [
            'comments' => $comments,
            'commentStats' => [
                'totalComments' => count($comments),
                'trailsWithComments' => count($trailIds),
                'latestCommentDate' => $latestCommentDate,
            ],
        ]);
    }

    #[Route('/admin/trail-analytics/content', name: 'admin_trail_content')]
    public function content(
        Request $request,
        PublicationParcoursRepository $publicationParcoursRepository,
        ParcoursDeSanteRepository $parcoursDeSanteRepository
    ): Response
    {
        $parcoursId = $request->query->getInt('parcoursId');
        $selectedParcours = $parcoursId > 0 ? $parcoursDeSanteRepository->find($parcoursId) : null;
        $publications = $publicationParcoursRepository->findByFilters($selectedParcours, null, null, 'DESC');

        $totalPublications = count($publications);
        $eventPublications = 0;
        $opinionPublications = 0;
        $totalAmbiance = 0;
        $totalSecurity = 0;
        $totalComments = 0;
        $publicationImages = [];

        foreach ($publications as $publication) {
            $typePublication = strtolower((string) $publication->getTypePublication());
            if ($typePublication === 'event') {
                ++$eventPublications;
            } else {
                ++$opinionPublications;
            }

            $totalAmbiance += (int) ($publication->getAmbiance() ?? 0);
            $totalSecurity += (int) ($publication->getSecurite() ?? 0);
            $totalComments += $publication->getCommentairePublications()->count();

            $imagePath = trim((string) ($publication->getImagePublication() ?? ''));
            if ($imagePath !== '' && count($publicationImages) < 6) {
                $publicationImages[] = $imagePath;
            }
        }

        $averageAmbiance = $totalPublications > 0 ? round($totalAmbiance / $totalPublications, 1) : 0.0;
        $averageSecurity = $totalPublications > 0 ? round($totalSecurity / $totalPublications, 1) : 0.0;

        return $this->render('admin/trail-analytics/content-metrics.html.twig', [
            'selectedParcours' => $selectedParcours,
            'availableParcours' => $parcoursDeSanteRepository->findBy([], ['nomParcours' => 'ASC']),
            'publications' => $publications,
            'publicationImages' => $publicationImages,
            'stats' => [
                'totalPublications' => $totalPublications,
                'eventPublications' => $eventPublications,
                'opinionPublications' => $opinionPublications,
                'averageAmbiance' => $averageAmbiance,
                'averageSecurity' => $averageSecurity,
                'totalComments' => $totalComments,
            ],
        ]);
    }

    #[Route('/admin/trail-analytics/reports', name: 'admin_trail_reports')]
    public function reports(): Response
    {
        return $this->render('admin/trail-analytics/report-generator.html.twig');
    }
}
