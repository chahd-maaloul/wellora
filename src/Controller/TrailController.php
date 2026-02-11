<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrailController extends AbstractController
{
    // ==================== CORE TRAIL ROUTES ====================
    
    #[Route('/trail/dashboard', name: 'trail_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('trail/dashboard.html.twig');
    }

    #[Route('/trail/discover', name: 'trail_discover')]
    public function discover(): Response
    {
        return $this->render('trail/discover.html.twig');
    }

    #[Route('/trail/create', name: 'trail_create')]
    public function create(): Response
    {
        return $this->render('trail/create.html.twig');
    }

    #[Route('/trail/detail/{id}', name: 'trail_detail', requirements: ['id' => '\d+'])]
    public function detail(int $id): Response
    {
        return $this->render('trail/detail.html.twig', [
            'id' => $id,
        ]);
    }

    #[Route('/trail/my-trails', name: 'trail_my_trails')]
    public function myTrails(): Response
    {
        return $this->render('trail/my-trails.html.twig');
    }

    // ==================== PUBLICATION ROUTES ====================

    #[Route('/trail/publications/feed', name: 'trail_publication_feed')]
    public function publicationFeed(): Response
    {
        return $this->render('trail/publication/feed.html.twig');
    }

    #[Route('/trail/publication/create', name: 'trail_publication_create')]
    public function publicationCreate(): Response
    {
        return $this->render('trail/publication/create.html.twig');
    }

    // ==================== COMMUNITY ROUTES ====================

    #[Route('/trail/community/reviews', name: 'trail_community_reviews')]
    public function communityReviews(): Response
    {
        return $this->render('trail/community/reviews.html.twig');
    }

    #[Route('/trail/community/write-review', name: 'trail_community_write_review')]
    public function writeReview(): Response
    {
        return $this->render('trail/community/write-review.html.twig');
    }

    #[Route('/trail/community/discussions', name: 'trail_community_discussions')]
    public function discussions(): Response
    {
        return $this->render('trail/community/discussions.html.twig');
    }

    #[Route('/trail/community/groups', name: 'trail_community_groups')]
    public function groups(): Response
    {
        return $this->render('trail/community/groups.html.twig');
    }

    #[Route('/trail/community/profile', name: 'trail_community_profile')]
    public function userProfile(): Response
    {
        return $this->render('trail/community/user-profile.html.twig');
    }

    #[Route('/trail/community/feed', name: 'trail_community_feed')]
    public function activityFeed(): Response
    {
        return $this->render('trail/community/activity-feed.html.twig');
    }

    #[Route('/trail/community/safety', name: 'trail_community_safety')]
    public function safetyTools(): Response
    {
        return $this->render('trail/community/safety-tools.html.twig');
    }

    // ==================== MAP ROUTES ====================

    #[Route('/trail/map/interactive', name: 'trail_map_interactive')]
    public function interactiveMap(): Response
    {
        return $this->render('trail/map/interactive-map.html.twig');
    }

    #[Route('/trail/map/detail/{id}', name: 'trail_map_detail', requirements: ['id' => '\d+'])]
    public function trailDetailMap(int $id): Response
    {
        return $this->render('trail/map/trail-detail-map.html.twig', [
            'id' => $id,
        ]);
    }

    #[Route('/trail/map/navigation/{id}', name: 'trail_map_navigation', requirements: ['id' => '\d+'])]
    public function navigationMap(int $id): Response
    {
        return $this->render('trail/map/navigation.html.twig', [
            'id' => $id,
        ]);
    }

    #[Route('/trail/map/planning', name: 'trail_map_planning')]
    public function planningTools(): Response
    {
        return $this->render('trail/map/planning-tools.html.twig');
    }

    #[Route('/trail/map/offline', name: 'trail_map_offline')]
    public function offlineMaps(): Response
    {
        return $this->render('trail/map/offline-maps.html.twig');
    }

    // ==================== API/UTILITIES ROUTES ====================

    #[Route('/trail/api/search', name: 'trail_api_search')]
    public function searchTrails(): Response
    {
        // API endpoint for searching trails
        return $this->json([
            'trails' => [],
            'total' => 0,
        ]);
    }

    #[Route('/trail/api/nearby', name: 'trail_api_nearby')]
    public function nearbyTrails(): Response
    {
        // API endpoint for nearby trails based on geolocation
        return $this->json([
            'trails' => [],
            'total' => 0,
        ]);
    }

    #[Route('/trail/api/export/{id}', name: 'trail_api_export', requirements: ['id' => '\d+'])]
    public function exportTrail(int $id): Response
    {
        // API endpoint for exporting trail as GPX
        return $this->json([
            'success' => true,
            'download_url' => '/download/gpx/' . $id,
        ]);
    }
}
