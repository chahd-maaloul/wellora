<?php

namespace App\Service;

use App\Entity\ParcoursDeSante;

final class ParcoursRecommendationService
{
    private const WEATHER_ANY = 'any';
    private const MAX_WEATHER_EVALUATIONS = 6;

    public function __construct(
        private readonly WeatherService $weatherService
    ) {}

    /**
     * @param ParcoursDeSante[] $parcours
     * @return array{
     *     parcours: ParcoursDeSante,
     *     distance_km: float,
     *     preferred_weather: string,
     *     weather_match: bool,
     *     weather: array<string, mixed>|null
     * }|null
     */
    public function recommendNearestByWeather(array $parcours, float $userLatitude, float $userLongitude, string $preferredWeather): ?array
    {
        $normalizedPreference = $this->normalizePreferredWeather($preferredWeather);

        $candidates = [];
        foreach ($parcours as $trail) {
            if (!$trail instanceof ParcoursDeSante) {
                continue;
            }

            $trailLatitude = $trail->getLatitudeParcours();
            $trailLongitude = $trail->getLongitudeParcours();
            if ($trailLatitude === null || $trailLongitude === null) {
                continue;
            }

            $candidates[] = [
                'parcours' => $trail,
                'distance_km' => $this->calculateDistanceKm($userLatitude, $userLongitude, $trailLatitude, $trailLongitude),
            ];
        }

        if ($candidates === []) {
            return null;
        }

        usort($candidates, static fn (array $a, array $b): int => $a['distance_km'] <=> $b['distance_km']);

        if ($normalizedPreference === self::WEATHER_ANY) {
            $nearest = $candidates[0];
            $weather = $this->weatherService->getCurrentWeatherForCoordinates(
                $nearest['parcours']->getLatitudeParcours(),
                $nearest['parcours']->getLongitudeParcours(),
                $nearest['parcours']->getLocalisationParcours()
            );

            return [
                'parcours' => $nearest['parcours'],
                'distance_km' => $nearest['distance_km'],
                'preferred_weather' => $normalizedPreference,
                'weather_match' => true,
                'weather' => $weather,
            ];
        }

        $bestCandidate = null;
        $nearestCandidates = array_slice($candidates, 0, self::MAX_WEATHER_EVALUATIONS);

        foreach ($nearestCandidates as $candidate) {
            $weather = $this->weatherService->getCurrentWeatherForCoordinates(
                $candidate['parcours']->getLatitudeParcours(),
                $candidate['parcours']->getLongitudeParcours(),
                $candidate['parcours']->getLocalisationParcours()
            );

            $matchScore = $this->getWeatherMatchScore($normalizedPreference, $weather);
            $rankingScore = $matchScore * 1000 - $candidate['distance_km'];

            if ($bestCandidate === null || $rankingScore > $bestCandidate['ranking_score']) {
                $bestCandidate = [
                    'parcours' => $candidate['parcours'],
                    'distance_km' => $candidate['distance_km'],
                    'weather' => $weather,
                    'match_score' => $matchScore,
                    'ranking_score' => $rankingScore,
                ];
            }
        }

        if ($bestCandidate === null) {
            return null;
        }

        return [
            'parcours' => $bestCandidate['parcours'],
            'distance_km' => $bestCandidate['distance_km'],
            'preferred_weather' => $normalizedPreference,
            'weather_match' => $bestCandidate['match_score'] > 0,
            'weather' => $bestCandidate['weather'],
        ];
    }

    private function normalizePreferredWeather(string $preferredWeather): string
    {
        $normalized = strtolower(trim($preferredWeather));
        $allowed = ['any', 'clear', 'cloudy', 'rain', 'snow', 'windy'];

        return in_array($normalized, $allowed, true) ? $normalized : self::WEATHER_ANY;
    }

    /**
     * Score:
     * 2 => strong match
     * 1 => acceptable match
     * 0 => mismatch or weather unavailable
     */
    private function getWeatherMatchScore(string $preferredWeather, ?array $weather): int
    {
        if ($weather === null) {
            return 0;
        }

        $condition = strtolower((string) ($weather['condition'] ?? ''));
        $windSpeed = isset($weather['wind_speed']) ? (float) $weather['wind_speed'] : null;

        return match ($preferredWeather) {
            'clear' => str_contains($condition, 'clear') ? 2 : 0,
            'cloudy' => (str_contains($condition, 'cloud') || str_contains($condition, 'fog')) ? 2 : 0,
            'rain' => (str_contains($condition, 'rain') || str_contains($condition, 'drizzle') || str_contains($condition, 'thunderstorm')) ? 2 : 0,
            'snow' => str_contains($condition, 'snow') ? 2 : 0,
            'windy' => $windSpeed !== null && $windSpeed >= 25.0 ? 2 : ($windSpeed !== null && $windSpeed >= 18.0 ? 1 : 0),
            default => 0,
        };
    }

    private function calculateDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }
}
